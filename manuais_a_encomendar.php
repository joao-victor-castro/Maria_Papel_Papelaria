<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');
require("vendor/autoload.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents("php://input"), true);

	if($request["acao"] == "pedir_manuais"){
		$stmt = $conn->prepare(
			"SELECT password_hash FROM utilizador
			WHERE id_utilizador = ?"
		);
		$stmt->bind_param("i", $_SESSION["user_id"]);
		$stmt->execute();
		$user = $stmt->get_result()->fetch_assoc();

		$pass = $request["pass"];

		if(password_verify($pass, $user["password_hash"])){
			pedir_manuais($conn);
			exit();
		}
		else{
			echo json_encode(['resultado'=>'erro', 'msg'=>'Palavra-passe incorreta']);
			exit();

		}
	}
}

function pedir_manuais(mysqli $conn){
	$conn->begin_transaction();

	try {
		// Seleciona todos os manuais com quant_manuais_pedir > 0
		$result = $conn->query("SELECT * FROM manual
								JOIN editora ON manual.id_editora = editora.id_editora
								WHERE quant_manuais_pedir > 0");
		$manuais = $result->fetch_all(MYSQLI_ASSOC);
	
		// Gera o excel
		$tabela_manuais = [['ISBN do manual', 'Nome do manual', 'Quantidade', 'Código', 
							'Nome da editora', 'Valor do manual', 'Tipo do manual']];
	
		foreach($manuais as $manual){
			$tabela_manuais [] = [
				$manual["isbn_manual"],
				$manual["nome_manual"],
				$manual["quant_manuais_pedir"],
				$manual["cod_manual"],
				$manual["nome_editora"],
				$manual["preco_manual"],
				$manual["tipo_manual"]
			];
		}

		$data_encomenda = date("Y-m-d");


		$xlsx = Shuchkin\SimpleXLSXGen::fromArray($tabela_manuais);

		$pasta = 'encomendas_a_editora/';
		$arquivo = "encomenda_editora_". $data_encomenda . ".xlsx";
		$caminho = $pasta . $arquivo;
		$xlsx->saveAs(__DIR__ . '/'. $caminho);

		// Volta quant_manuais_pedir a 0
		$conn->query(
			"UPDATE manual
			SET quant_manuais_pedir = 0"
		);

		// Salvar encomenda à editora na base de dados
		$stmt = $conn->prepare(
			"INSERT INTO encomenda_editora (id_utilizador, data_encomenda_editora, doc_encomenda_editora)
			VALUES (?,?,?)"
		);
		$stmt->bind_param("iss", $_SESSION["user_id"], $data_encomenda, $caminho);
		$stmt->execute();

		// Atualiza as encomendas para pedido, e adiciona observação
		att_manuais_pedido($conn);
	
		$stmt->close();
		$conn->commit();
	} catch (Exception $e) {
		$conn->rollback();
		echo json_encode(['resultado'=>'erro', 'msg'=>$e]);
		return;
	}

	echo json_encode(['resultado'=>'sucesso', 'msg'=>'Encomenda à editora feita com sucesso']);
	return;
}

function att_manuais_pedido(mysqli $conn){
	// Seleciona o id de todas as encomendas que estao registadas
	$result = $conn->query(
		"SELECT id_encomenda FROM encomenda
		WHERE estado_encomenda = 'registada'"
	);
	$id_registadas = $result->fetch_all(MYSQLI_ASSOC);

	// Adiciona observação em todas as encomendas
	$stmt_obs = $conn->prepare(
		"INSERT INTO observacao_encomenda (id_encomenda, observacao_encomenda, data_observacao, id_utilizador)
		VALUES (?,?,?,?)"
	);
	$data_obs = date("Y-m-d H:i:s");
	$obs = "MPP3: A encomenda passou ao estado de pedida.";

	foreach($id_registadas as $id){
		$stmt_obs->bind_param("issi", $id["id_encomenda"], $obs, $data_obs, $_SESSION["user_id"]);
		$stmt_obs->execute();
	}

	// Atualiza pra pedida todas as encomendas registadas
	$stmt_pedido = $conn->prepare(
		"UPDATE encomenda
		SET estado_encomenda = 'pedida', data_pedido = ?
		WHERE estado_encomenda = 'registada'"
	);
	$data_encomenda = date("Y-m-d");
	$stmt_pedido->bind_param("s", $data_encomenda);
	$stmt_pedido->execute();
	$stmt_pedido->close();
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
		<title>MPP - Manuais a encomendar</title>
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
						<h2>Manuais a encomendar</h2>

						<!-- Modal de confirmar  -->
						<div id="modal-gestao" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal" class="modal-close">&times;</span>
								<h3>Confirmar encomenda</h3>
								<p>Introduza a sua palavra-passe para confirmar a encomenda:</p>

								<div class="row" style="margin-bottom: 10px;">
									<div class="col-6">
										<input type="password" id="pass_confirmar">
									</div>
								</div>

								<div class="row aln-middle">
									<div class="col-3 col-12-small">
										<button class="primary" id="btnConfirmarSenha">Confirmar</button>
									</div>
									<div class="col-6 col-12-small">
										<p id="msgErro"></p>
									</div>
								</div>
							</div>
						</div>

						<!-- Tabela mostrando os manuais -->
						<div class="table-wrapper">
							<table class="alt">
								<thead>
									<tr>
										<th>ISBN do manual</th>
										<th>Nome do manual</th>
										<th>Código</th>
                                        <th>Nome da editora</th>
                                        <th>Valor do manual</th>
                                        <th>Tipo do manual</th>
                                        <th>Quantidade</th>
                                        <th>Total com desconto</th>
									</tr>
								</thead>

								<tbody>
								<?php 
								// Vai a base de dados buscar todos os manuais com quant_pedir
								$result = $conn->query("SELECT manual.*, editora.nome_editora FROM manual
														JOIN editora ON manual.id_editora = editora.id_editora
														WHERE quant_manuais_pedir > 0
														ORDER BY nome_editora, quant_manuais_pedir DESC");
								$manuais = $result->fetch_all(MYSQLI_ASSOC);

								$valor_total = 0;

								foreach($manuais as $manual){
									?>
									<tr>
										<td><?= $manual["isbn_manual"] ?></td>
										<td><?= $manual["nome_manual"] ?></td>
										<td><?= $manual["cod_manual"] ?></td>
										<td><?= $manual["nome_editora"] ?></td>
										<td><?= $manual["preco_manual"] ?></td>
										<td><?= $manual["tipo_manual"] ?></td>
										<td><?= $manual["quant_manuais_pedir"] ?></td>
										<?php 
										if($manual["tipo_manual"] == "Manual"){
											$preco_manual = floatval($manual["preco_manual"]);
											$quantidade = intval($manual["quant_manuais_pedir"]);
											$preco_desconto = $preco_manual * 0.82 * $quantidade;
											$preco_desconto = round($preco_desconto, 2);
											$valor_total += $preco_desconto;
											?>
											<td><?= $preco_desconto ?></td>
											
											<?php }
										else{
											$preco_manual = floatval($manual["preco_manual"]);
											$quantidade = intval($manual["quant_manuais_pedir"]);
											$preco_desconto = $preco_manual * 0.80 * $quantidade;
											$preco_desconto = round($preco_desconto, 2);
											$valor_total += $preco_desconto;
											?>
											<td><?= $preco_desconto ?></td>

										<?php }
										?>
									</tr>
								<?php 
								}
								?>
								<tr>
									<td colspan="7" style="text-align: center; font-weight:bold">Valor total com desconto</td>
									<td><?= $valor_total ?></td>
								</tr>
								</tbody>
							</table>							
						</div>

						<!-- Botão de confirmar encomenda -->
						<div class="row">
							<div class="col-12">
								<button class="primary" id="btnConfirmarEncomenda">Confirmar encomenda</button>
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

			<script src="assets/js/gestao.js"></script>
			<script src="assets/js/manuais_a_encomendar.js"></script>
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>
