<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $request = json_decode(file_get_contents('php://input'),true);

    // Começa o processo de alteração dos dados
    $conn->begin_transaction();
    try {
        $id_utilizador = $_SESSION["user_id"];
        $data = date("Y-m-d");
        $id_encomenda = $request["id_encomenda"];

        $stmt = $conn->prepare(
            "UPDATE encomenda_manual
            SET manual_separado = ?, data_separacao = ?, id_separado = ?
            WHERE id_encomenda = ? AND id_manual = ?"
        );

        foreach($request["dados"] as $manual){
            $id_manual = $manual["id_manual"];
            $manual_separado = 1;
            $stmt->bind_param("isiii", $manual_separado, $data, $id_utilizador, $id_encomenda, $id_manual);
            $stmt->execute();
        }

        // Verifica se todos os manuais estão separados
        $stmtCheck = $conn->prepare(
            "SELECT id_manual FROM encomenda_manual
            WHERE id_encomenda = ? AND manual_separado = 0"
        );
        $stmtCheck->bind_param("i", $id_encomenda);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        
        if($result->num_rows == 0){
            $stmtSeparado = $conn->prepare(
                "UPDATE encomenda
                SET estado_encomenda = 'separada', data_separado = ?, id_separado = ?
                WHERE id_encomenda = ?"
            );

            $stmtSeparado->bind_param("sii", $data, $id_utilizador, $id_encomenda);
            $stmtSeparado->execute();
            $stmtSeparado->close();

            $obs_separada = "MPP3: A encomenda passou ao estado de separada.";
            $data_separada = date("Y-m-d H:i:s");
            $stmt_separada = $conn->prepare(
                "INSERT INTO observacao_encomenda (id_encomenda, observacao_encomenda, data_observacao, id_utilizador)
                VALUES (?,?,?,?)"
            );
            $stmt_separada->bind_param("issi", $id_encomenda, $obs_separada, $data_separada, $id_utilizador);
            $stmt_separada->execute();
            $stmt_separada->close();
        }

        $stmt->close();
        $stmtCheck->close();
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['resultado'=>'erro', 'msg'=>"Não foi possível fazer as alterações - $e"]);
        exit();
    }

    echo json_encode(['resultado'=>'sucesso', 'msg'=>'Encomenda atualizada com sucesso!']);
    exit();
}

?>

<!DOCTYPE HTML>
<html>

<head>
    <title>MPP - Editar Encomenda</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
</head>

<body class="is-preload">

    <!-- Wrapper -->
    <div id="wrapper">

        <!-- Main -->
        <div id="main">
            <div class="inner">

                <!-- Header -->
                <?php include('header.php'); ?>
                <!-- Banner -->
                <section id="search" class="alt">
                    <?php
                    // Verificar se já está autenticado
                    if (isset($_SESSION['user_id'])) { ?>
                        <h2>Editar encomenda ID: <?= $_GET["id"] ?></h2>

                        <!-- Detalhes da encomenda -->
                        <?php 
                            $stmt = $conn->prepare(
                                "SELECT encomenda.*, utilizador.username, agrupamento.nome_agrupamento FROM encomenda
                                JOIN utilizador ON utilizador.id_utilizador = encomenda.id_utilizador
                                LEFT JOIN agrupamento ON agrupamento.id_agrupamento = encomenda.id_agrupamento
                                WHERE id_encomenda = ?"
                            );

                            $stmt->bind_param("i", $_GET["id"]);

                            $stmt->execute();

                            $result = $stmt->get_result();

                            if($result->num_rows != 1){
                                $stmt->close();
                                header("Location: tratar_encomendas.php");
                                exit();
                            }

                            $encomenda = $result->fetch_assoc();
                            $stmt->close();
                        ?>
                        <div class="box">
                            <h3>Detalhes da encomenda</h3>

                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>Data: </strong><?= date('d/m/Y', strtotime($encomenda["data_encomenda"])) ?></li>
                                        <li><strong>Número da encomenda: </strong><?= $encomenda["num_encomenda"] ?></li>
                                        <li><strong>Utilizador: </strong><?= $encomenda["username"] ?></li>
                                        <li><strong>Código MEGA: </strong><?= $encomenda["codigo_mega"] ?></li>
                                    </ul>
                                </div>

                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>Aluno: </strong><?= $encomenda["nome_aluno_encomenda"] ?></li>
                                        <li><strong>NIF Aluno: </strong><?= $encomenda["nif_encomenda"] ?></li>
                                        <li><strong>E.E. Aluno: </strong><?= $encomenda["ee_encomenda"] ?></li>
                                        <li><strong>Telefone: </strong><?= $encomenda["telefone_encomenda"] ?></li>
                                    </ul>
                                </div>

                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li>
                                            <strong>Plastificar Manuais: </strong>
                                            <?php 
                                            if($encomenda["plast_manuais"] == 1){
                                                echo "Sim";
                                            }
                                            else{
                                                echo "Não";
                                            }
                                            ?>
                                        </li>
                                        <li>
                                            <strong>Plastificas livro de fichas: </strong>
                                            <?php 
                                            if($encomenda["plast_livro_fichas"] == 1){
                                                echo "Sim";
                                            }
                                            else{
                                                echo "Não";
                                            }
                                            ?>
                                        </li>
                                        <li>
                                            <strong>Etiquetas: </strong> 
                                            <?php 
                                            if($encomenda["etiquetas"] == 1){
                                                echo "Sim - " . $encomenda["obs_etiquetas"];
                                            }
                                            else{
                                                echo "Não";
                                            }
                                            ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <p><strong>Agrupamento: </strong><?= $encomenda["nome_agrupamento"] ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <p><strong>Total encomenda: </strong><?= $encomenda["total_encomenda"] ?>€</p>
                                </div>
                                <div class="col-4 col-12-small">
                                    <p><strong>Caução paga: </strong><?= $encomenda["valor_caucao"] ?>€</p>
                                </div>
                                <div class="col-4 col-12-small">
                                    <p><strong>Doc. Encomenda: </strong><a href="<?= $encomenda["doc_encomenda"] ?>" target="_blank">Ver documento</a></p>
                                </div>
                            </div>

                            <div class="row aln-middle" style="margin-top: 10px;">
                                <div class="col-4 col-12-small">
                                    <?php 
                                    $cores_estados = [
                                        'registada'=>'darkred',
                                        'pedida'=>'orange',
                                        'separada'=>'teal',
                                        'concluida'=>'goldenrod',
                                        'entregue'=>'green',
                                        'cancelada'=>'red'
                                    ]
                                    ?>
                                    <h4><strong>Estado da encomenda: <span style="color:<?= $cores_estados[$encomenda['estado_encomenda']] ?>"><?= $encomenda["estado_encomenda"] ?></span></strong></h4>
                                </div>
                            </div>
                        </div>

                        <!-- Observações da encomenda -->
                        <div class="box">
                            <h3>Observações da encomenda</h3>

                            <?php 
                            $stmt = $conn->prepare(
                                "SELECT * FROM observacao_encomenda
                                JOIN utilizador ON observacao_encomenda.id_utilizador = utilizador.id_utilizador
                                WHERE id_encomenda = ?
                                ORDER BY data_observacao DESC"
                            );
                            $stmt->bind_param("i", $_GET["id"]);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $rows = $result->fetch_all(MYSQLI_ASSOC);

                            foreach($rows as $obs){
                                // Verifica se é uma observação automatica
                                if(str_starts_with($obs["observacao_encomenda"],"MPP3:")){
                                    $obs_automatica = true;
                                }
                                else{
                                    $obs_automatica = false;
                                }
                                ?>
                                <dl>
                                    <dt><?= $obs["username"] ?> : <?= $obs["data_observacao"] ?></dt>
                                    
                                    <?php
                                    if($obs_automatica){?>
                                        <dd><strong style="color: green;"><?= $obs["observacao_encomenda"] ?></strong></dd>
                                        <?php 
                                    }
                                    else{?>
                                    <dd><strong style="color: red;"><?= $obs["observacao_encomenda"] ?></strong></dd>
                                    <?php } ?>
                                </dl>
                            <?php
                            }
							?>
                        </div>

                        <!-- Manuais da encomenda -->
                        <?php 
                            $stmt = $conn->prepare(
                                "SELECT encomenda_manual.*, manual.*, disciplina.nome_disciplina, utilizador.username FROM encomenda_manual
                                JOIN manual ON manual.id_manual = encomenda_manual.id_manual
                                JOIN disciplina ON disciplina.id_disciplina = manual.id_disciplina
                                LEFT JOIN utilizador ON utilizador.id_utilizador = encomenda_manual.id_separado
                                WHERE id_encomenda = ?"
                            );

                            $stmt->bind_param("i", $_GET["id"]);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $manuais = $result->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <div class="box">
                            <h3>Manuais da encomenda</h3>

                            <div class="table-wrapper">
                                <table class="alt">
                                    <thead>
                                        <tr>
                                            <th>ISBN</th>
                                            <th>Nome do manual</th>
                                            <th>Disciplina</th>
                                            <th>Tipo de manual</th>
                                            <th>Manual separado</th>
                                            <th>Data separação</th>
                                            <th>Utilizador</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    <?php 
                                        foreach($manuais as $manual){?>
                                            <tr>
                                                <td><?= $manual["isbn_manual"] ?></td>
                                                <td><?= $manual["nome_manual"] ?></td>
                                                <td><?= $manual["nome_disciplina"] ?></td>
                                                <td><?= $manual["tipo_manual"] ?></td>
                                                <td>
                                                    <?php 
                                                    if($manual["manual_separado"] == 1){?>
                                                        <input type="checkbox" id="<?= $manual["id_manual"] ?>" checked disabled>
                                                        <label for="<?= $manual["id_manual"] ?>"></label>
                                                        <?php 
                                                    }
                                                    else{?>
                                                    <input type="checkbox" id="<?= $manual["id_manual"] ?>" class="manualSeparado">
                                                    <label for="<?= $manual["id_manual"] ?>"></label>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        if(!isset($manual["data_separacao"])){
                                                            echo "-";
                                                        }
                                                        else{
                                                            echo date('d/m/Y', strtotime($manual["data_separacao"])); 
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        if(!isset($manual["id_separado"])){
                                                            echo "-";
                                                        }
                                                        else{
                                                            echo $manual["username"]; 
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php }
                                    ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-2">
                                    <button id="salvar_alteracoes" class="primary" data-id_encomenda="<?= $encomenda["id_encomenda"] ?>">Salvar alterações</button>
                                </div>
                                <div class="col-3">
                                    <span id="msgErro"></span>
                                </div>
                            </div>
                        </div>

                        


                    <?php } else {
                        echo <<<HTML
												<section id="banner">
													<div class="col-6 col-12-small">
														<div class="box">
															<h2>Informações do Sistema</h2>
															<p>Este é o sistema de gestão da Maria Papel Papelaria. Para aceder às funcionalidades administrativas, é necessário autenticar-se com as suas credenciais.</p>
															<p>Se ainda não possui uma conta, contacte o administrador do sistema para obter acesso.</p>
														</div>
													</div>
												</section>
												HTML;
                    }

                    ?>


            </div>
        </div>

        <!-- Sidebar -->
        <div id="sidebar">
            <div class="inner">

                <!-- Menu -->
                <?php include('menu.php'); ?>

                <!-- Footer -->
                <?php include('footer.php'); ?>

            </div>
        </div>

    </div>

    <!-- Scripts -->
     <script src="assets/js/editar_encomenda.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>