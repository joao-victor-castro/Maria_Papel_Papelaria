<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $request = json_decode(file_get_contents('php://input'),true);

    // Faz a query com base no filtro recebido do cliente
    switch($request["tipo_filtro"]){
        case "num_encomenda":
            filtrar_num_encomenda($conn, $request);
            exit();
        
        case "nome_aluno":
            filtrar_nome_aluno($conn, $request);
            exit();

        case "nome_ee":
            filtrar_nome_ee($conn, $request);
            exit();

        case "num_telefone":
            filtrar_num_telefone($conn, $request);
            exit();
        
    }
}

function filtrar_num_encomenda(mysqli $conn, array $request){
    $num_encomenda = $request["filtro"];

    $stmt = $conn->prepare(
        "SELECT encomenda.*, ano_escolar.nome_ano_escolar FROM encomenda
        JOIN ano_escolar ON encomenda.id_ano_encomenda = ano_escolar.id_ano_escolar
        WHERE num_encomenda = ?
        ORDER BY nome_ano_escolar ASC"
    );
    $stmt->bind_param("i", $num_encomenda);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['resultado'=>$rows]);
    return;
}


function filtrar_nome_aluno(mysqli $conn, array $request){
    $nome_aluno = '%' . $request["filtro"] . '%';

    $stmt = $conn->prepare(
        "SELECT encomenda.* , ano_escolar.nome_ano_escolar FROM encomenda
        JOIN ano_escolar ON encomenda.id_ano_encomenda = ano_escolar.id_ano_escolar
        WHERE nome_aluno_encomenda LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')
        ORDER BY nome_ano_escolar ASC"
    );
    $stmt->bind_param("s", $nome_aluno);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['resultado'=>$rows]);
    return;
}


function filtrar_nome_ee(mysqli $conn, array $request){
    $nome_ee = '%' . $request["filtro"] . '%';

    $stmt = $conn->prepare(
        "SELECT encomenda.* , ano_escolar.nome_ano_escolar FROM encomenda
        JOIN ano_escolar ON encomenda.id_ano_encomenda = ano_escolar.id_ano_escolar
        WHERE ee_encomenda LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')
        ORDER BY nome_ano_escolar ASC"
    );
    $stmt->bind_param("s", $nome_ee);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['resultado'=>$rows]);
    return;
}


function filtrar_num_telefone(mysqli $conn, array $request){
    $num_telefone = trim($request["filtro"]);

    $stmt = $conn->prepare(
        "SELECT encomenda.* , ano_escolar.nome_ano_escolar FROM encomenda
        JOIN ano_escolar ON encomenda.id_ano_encomenda = ano_escolar.id_ano_escolar
        WHERE telefone_encomenda = ?
        ORDER BY nome_ano_escolar ASC"
    );
    $stmt->bind_param("s", $num_telefone);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['resultado'=>$rows]);
    return;
}

?>

<!DOCTYPE HTML>
<!--
	Editorial by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>MPP - Pesquisar Encomendas</title>
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
									if (isset($_SESSION['user_id'])) {?>
										<h2>Pesquisar encomendas</h2>
                                        
                                        <h4>Pesquisar por:</h4>
                                        <div class="row">
                                            <div class="col-12">
                                                <input type="radio" name="filtro" value="num_encomenda" id="filtro_num" checked>
                                                <label for="filtro_num">Num. encomenda</label>

                                                <input type="radio" name="filtro" value="nome_aluno" id="nome_aluno">
                                                <label for="nome_aluno">Nome aluno</label>

                                                <input type="radio" name="filtro" value="nome_ee" id="nome_ee">
                                                <label for="nome_ee">Nome E.E.</label>

                                                <input type="radio" name="filtro" value="num_telefone" id="num_telefone">
                                                <label for="num_telefone">Nº telefone</label>
                                            </div>
                                            <div class="col-3">
                                                <input type="text" id="filtro_input">
                                            </div>
                                            <div class="col-2">
                                                <button id="btnFiltro" class="primary">Filtrar</button>
                                            </div>
                                            <div class="col-12">
                                                <p id="msgErro"></p>
                                            </div>
                                        </div>

                                        <!-- tabela -->
                                        <div class="box">
                                            <h4>Manuais encontrados</h4>
                                            <div class="table-wrapper">
                                                <table class="alt">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Num. Encomenda</th>
                                                            <th>Ano escolar</th>
                                                            <th>Nome aluno</th>
                                                            <th>Estado da encomenda</th>
                                                            <th> </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>



									<?php }
										else{
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
		 <script src="assets/js/pesquisar_encomendas.js"></script>
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/browser.min.js"></script>
		<script src="assets/js/breakpoints.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>
	</body>
</html>
