<?php
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'), true);

	switch($request["acao"]){
		case "get_encomendas":
			echo json_encode(['encomendas'=>getEncomendas($conn)]);
			exit();
	}
}

function getEncomendas(mysqli $conn){
	$stmt = $conn->prepare(
		"SELECT encomenda.*, ano_escolar.nome_ano_escolar FROM encomenda
		JOIN ano_escolar ON ano_escolar.id_ano_escolar = encomenda.id_ano_encomenda
		ORDER BY num_encomenda ASC"
	);

	$stmt->execute();
	$result = $stmt->get_result();
	$encomendas = $result->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
	return $encomendas;
}

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>MPP - Estado Encomendas</title>
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
								if (isset($_SESSION['user_id'])) {?>

									<h2>Estado das encomendas</h2>

									<div class="box">

										<!-- Filtro de estado -->
										<h4>Filtrar por estado</h4>
										<div class="row" style="margin-bottom: 10px;">
											<div class="col-12">
												<?php
												$estados = ['registada', 'pedida', 'parcialmente_separada', 'separada', 'concluida', 'entregue', 'cancelada'];
												foreach($estados as $estado){?>
													<input type="checkbox" class="filtroEstado" id="estado_<?= $estado ?>" value="<?= $estado ?>">
													<label for="estado_<?= $estado ?>"><?= ucfirst($estado) ?></label>
												<?php } ?>
											</div>
										</div>

										<!-- Filtro de ano escolar -->
										<h4>Filtrar por ano escolar</h4>
										<div class="row">
											<div class="col-12">
											<?php
												$stmt = $conn->prepare("SELECT * FROM ano_escolar");
												$stmt->execute();
												$result = $stmt->get_result();
												$anos = $result->fetch_all(MYSQLI_ASSOC);

												foreach($anos as $ano){?>
													<input type="checkbox" class="filtroAno" id="<?= $ano["id_ano_escolar"] ?>" value="<?= $ano["id_ano_escolar"] ?>">
													<label for="<?= $ano["id_ano_escolar"] ?>"><?= $ano["nome_ano_escolar"] ?></label>
												<?php } ?>
											</div>
										</div>

										<h4>Encomendas: <span id="num_encomendas"></span></h4>

										<!-- Tabela -->
										<div class="table-wrapper">
											<table class="alt">
												<thead>
													<tr>
														<th>Número da encomenda</th>
														<th>Data da encomenda</th>
														<th>Ano escolar</th>
														<th>Estado</th>
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
		 <script src="assets/js/estado_encomendas.js"></script>
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/browser.min.js"></script>
		<script src="assets/js/breakpoints.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>
	</body>
</html>
