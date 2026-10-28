<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$request = json_decode(file_get_contents('php://input'), true);

	switch ($request["acao"]) {
		case "get_encomendas_ano":
			$stmt = $conn->prepare(
				"SELECT nome_ano_escolar, (encomendas_ano - encomendas_inicial) AS quantidade
                 FROM ano_escolar
                 ORDER BY id_ano_escolar ASC"
			);
			$stmt->execute();
			$result = $stmt->get_result();
			$dados = [];
			while ($row = $result->fetch_assoc()) {
				$dados[] = $row;
			}
			$stmt->close();
			echo json_encode($dados);
			exit();

		case "get_encomendas_estado":
			$stmt = $conn->prepare(
				"SELECT estado_encomenda, COUNT(*) AS quantidade
                 FROM encomenda
                 GROUP BY estado_encomenda
                 ORDER BY FIELD(estado_encomenda, 'registada', 'separada', 'pedida', 'concluida', 'entregue', 'cancelada')"
			);
			$stmt->execute();
			$result = $stmt->get_result();
			$dados = [];
			while ($row = $result->fetch_assoc()) {
				$dados[] = $row;
			}
			$stmt->close();
			echo json_encode($dados);
			exit();

		case "get_reposicao_estado":
			$stmt = $conn->prepare(
				"SELECT
                    SUM(CASE WHEN cancelado = 0 AND pedido = 0 AND concluido = 0 THEN 1 ELSE 0 END) AS por_pedir,
                    SUM(CASE WHEN pedido = 1 AND concluido = 0 AND cancelado = 0 THEN 1 ELSE 0 END) AS pedido,
                    SUM(CASE WHEN concluido = 1 AND cancelado = 0 THEN 1 ELSE 0 END) AS concluido,
                    SUM(CASE WHEN cancelado = 1 THEN 1 ELSE 0 END) AS cancelado
                 FROM reposicao"
			);
			$stmt->execute();
			$result = $stmt->get_result();
			$dados = $result->fetch_assoc();
			$stmt->close();
			echo json_encode($dados);
			exit();
	}
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
	<title>MPP - Início</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
	<link rel="stylesheet" href="assets/css/main.css" />
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
					if (isset($_SESSION['user_id'])) {
						$result = $conn->query(
							"SELECT SUM(encomendas_ano - encomendas_inicial) AS total_encomendas FROM ano_escolar"
						);
						$result = $result->fetch_assoc();
						$total_encomendas = $result["total_encomendas"];

					?>
						<div class="row">
							<div class="col-12" style="text-align:center">
								<h2>Total de encomendas: <?= $total_encomendas ?></h2>
							</div>
						</div>
						<div class="row">
							<div class="col-6 col-12-small" style="text-align:center">
								<h2>Nº de encomendas por ano escolar</h2>

								<div>
									<canvas id="grafico" width="5"></canvas>
								</div>

							</div>
							<div class="col-6 col-12-small" style="text-align:center">
								<h2>Nº de encomendas por estado</h2>
								<div id="estado-cards" class="estado-cards-wrapper"></div>
							</div>
						</div>
						<div class="row" style="margin-top: 2em;">
							<div class="col-12" style="text-align:center">
								<h2>Material a pedir &mdash; por estado</h2>
								<div id="reposicao-cards" class="estado-cards-wrapper"></div>
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
	<script src="assets/js/index.js"></script>
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/browser.min.js"></script>
	<script src="assets/js/breakpoints.min.js"></script>
	<script src="assets/js/util.js"></script>
	<script src="assets/js/main.js"></script>

</body>

</html>