<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Verifica se o formulário de adição foi submetido
	if (isset($_POST['adicionar'])) {
		$nome_ano_escolar = $_POST['nome_ano_escolar'];
		$encomendas_inicial = intval($_POST['encomendas_inicial']);

		// Verifica se já existe um ano escolar com esse nome
		$sql = "SELECT * FROM ano_escolar
					WHERE nome_ano_escolar = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_ano_escolar);
		$resultado = $stmt->execute();
		$rows = $stmt->get_result();
		$rows = $rows->num_rows;
		$stmt->close();

		if ($rows > 0) {
			echo "<script type='text/javascript'>
						alert('Já existe um ano escolar com esse nome.');
						window.location.replace('gestao_ano_escolar.php');
					</script>";
			exit();
		}

		$sql = "INSERT INTO ano_escolar (nome_ano_escolar, encomendas_inicial, encomendas_ano) VALUES (?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sii", $nome_ano_escolar, $encomendas_inicial, $encomendas_inicial);
		$resultado = $stmt->execute();

		if ($resultado == false) {
			echo "Erro ao adicionar à base de dados: " . $stmt->error;
			exit();
		}

		header('Location: ' . $_SERVER['PHP_SELF']);
		exit();
	} else if (isset($_POST['editar'])) {
		$id_ano_escolar = intval($_POST['edit_id_ano_escolar']);
		$nome_ano_escolar = $_POST['edit_nome_ano_escolar'];
		$encomendas_inicial = intval($_POST['edit_encomendas_inicial']);

		// Verifica se já existe um ano escolar com esse nome
		$sql = "SELECT * FROM ano_escolar
					WHERE nome_ano_escolar = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_ano_escolar);
		$resultado = $stmt->execute();
		$rows = $stmt->get_result();
		$rows = $rows->num_rows;
		$stmt->close();

		if ($rows > 0) {
			echo "<script type='text/javascript'>
						alert('Já existe um ano escolar com esse nome.');
						window.location.replace('gestao_ano_escolar.php');
					</script>";
			exit();
		}

		$sql = "UPDATE ano_escolar
					SET nome_ano_escolar = ?,
                        encomendas_inicial = ?
					WHERE id_ano_escolar = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sii", $nome_ano_escolar, $encomendas_inicial, $id_ano_escolar);
		$resultado = $stmt->execute();

		if ($resultado == false) {
			echo "Erro ao editar na base de dados: " . $stmt->error;
			exit();
		}

		header('Location: ' . $_SERVER['PHP_SELF']);
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
	<title>MPP - Anos Escolares</title>
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
						<h2>Gestão de Ano Escolar</h2>

						<!-- Modal com formulário pra editar -->
						<div id="modal-gestao" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal" class="modal-close">&times;</span>
								<h3>Editar Ano Escolar</h3>

								<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
									<input type="hidden" name="edit_id_ano_escolar" value="" id="edit_id_ano_escolar">
									<div class="row">
										<div class="col-8">
											<label for="edit_nome_ano_escolar">Ano escolar: </label>
											<input type="text" name="edit_nome_ano_escolar" id="edit_nome_ano_escolar">
										</div>
									</div>



									<div class="row">
										<div class="col-8">
											<label for="edit_encomendas_inicial">Encomendas Inicial: </label>
											<input type="text" name="edit_encomendas_inicial" id="edit_encomendas_inicial">
										</div>
									</div>

									<div class="row" style="margin-top: 10px;">
										<div class="col-6">
											<input type="submit" name="editar" value="editar">
										</div>
									</div>
								</form>
							</div>
						</div>


						<!-- Formulário para inserir linha na tabela -->
						<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" id="form_ano_escolar">
							<div class="row">
								<div class="col-3 col-12xsmall">
									<label for="nome_ano_escolar">Adicionar Ano Escolar:</label>
									<input type="text" name="nome_ano_escolar" required id="nome_ano_escolar">
								</div>

								<div class="col-3 col-12xsmall">
									<label for="encomendas_ano">Nº encomenda inicial:</label>
									<input type="text" name="encomendas_inicial" required id="encomendas_inicial">
								</div>

								<div class="col-3 col-12xsmall" style="align-self: flex-end;">
									<input type="submit" name="adicionar" value="adicionar">
								</div>
							</div>
						</form>

						<!-- Tabela mostrando os dados -->
						<div class="table-wrapper">
							<table class="alt">
								<thead>
									<tr>
										<th>Id Ano Escolar</th>
										<th>Nome Ano Escolar</th>
										<th>Encomendas Ano</th>
										<th>Encomendas Inicial</th>
										<th>Ações</th>
									</tr>
								</thead>

								<tbody>
									<?php
									// Busca os dados à base de dados e constrói a tabela
									$sql = "SELECT *, (encomendas_ano - encomendas_inicial) AS 'encomendas' FROM ano_escolar ORDER BY encomendas_inicial";
									$stmt = $conn->prepare($sql);
									$stmt->execute();
									$resultado = $stmt->get_result();

									if ($resultado == false) {
										echo "Erro ao buscar à base de dados: " . $stmt->error;
										exit();
									}

									if ($resultado->num_rows == 0) { ?>
										<td colspan="5">Sem itens na tabela</td>
									<?php
									}

									$linhas = $resultado->fetch_all(MYSQLI_ASSOC);

									foreach ($linhas as $linha) { ?>
										<tr>
											<td id="id_ano_escolar"><?= $linha['id_ano_escolar'] ?></td>
											<td id="nome_ano_escolar_<?= $linha['id_ano_escolar'] ?>"><?= $linha['nome_ano_escolar'] ?></td>
											<td id="encomendas_ano_<?= $linha['id_ano_escolar'] ?>"><?= $linha['encomendas'] ?></td>
											<td id="encomendas_inicial_<?= $linha['id_ano_escolar'] ?>"><?= $linha['encomendas_inicial'] ?></td>
											<td><button class="primary small" onclick="editar(<?= $linha['id_ano_escolar'] ?>)">Editar</button></td>
										</tr>
									<?php }



									?>
								</tbody>
							</table>


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
	<script>
		function editar(id) {
			// Popula o modal
			const nome_ano_escolar = document.getElementById(`nome_ano_escolar_${id}`).innerText;
			const encomendas_ano = document.getElementById(`encomendas_ano_${id}`).innerText;
			const encomendas_inicial = document.getElementById(`encomendas_inicial_${id}`).innerText;

			document.getElementById('edit_id_ano_escolar').value = id;
			document.getElementById('edit_nome_ano_escolar').value = nome_ano_escolar;
			document.getElementById('edit_encomendas_inicial').value = encomendas_inicial;

			const modal = document.getElementById('modal-gestao');
			modal.style.display = 'flex';
		}
	</script>
	<script src="assets/js/gestao.js"></script>
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/browser.min.js"></script>
	<script src="assets/js/breakpoints.min.js"></script>
	<script src="assets/js/util.js"></script>
	<script src="assets/js/main.js"></script>

</body>

</html>