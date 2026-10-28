	<?php
	// Verificação de sessão em todas as páginas protegidas
	session_start();
	include('db_connect.php');

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		// Verifica se o formulário de adição foi submetido
		if (isset($_POST['adicionar'])) {
			$nome_disciplina = $_POST['nome_disciplina'];

			// Verifica se já existe uma disciplina com esse nome
			$sql = "SELECT * FROM disciplina
					WHERE nome_disciplina = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("s", $nome_disciplina);
			$resultado = $stmt->execute();
			$rows = $stmt->get_result();
			$rows = $rows->num_rows;
			$stmt->close();

			if ($rows > 0) {
				echo "<script type='text/javascript'>
						alert('Já existe uma disciplina com esse nome na base de dados.');
						window.location.replace('gestao_disciplina.php');
					</script>";
				exit();
			}

			$sql = "INSERT INTO disciplina (nome_disciplina) VALUES (?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("s", $nome_disciplina);
			$resultado = $stmt->execute();

			if ($resultado == false) {
				echo "Erro ao adicionar à base de dados: " . $stmt->error;
				exit();
			}

			header('Location: ' . $_SERVER['PHP_SELF']);
			exit();
		} else if (isset($_POST['editar'])) {
			$id_disciplina = intval($_POST['edit_id_disciplina']);
			$nome_disciplina = $_POST['edit_nome_disciplina'];

			// Verifica se já existe uma disciplina com esse nome
			$sql = "SELECT * FROM disciplina
					WHERE nome_disciplina = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("s", $nome_disciplina);
			$resultado = $stmt->execute();
			$rows = $stmt->get_result();
			$rows = $rows->num_rows;
			$stmt->close();

			if ($rows > 0) {
				echo "<script type='text/javascript'>
						alert('Já existe uma disciplina com esse nome.');
						window.location.replace('gestao_disciplina.php');
					</script>";
				exit();
			}

			$sql = "UPDATE disciplina
					SET nome_disciplina = ?
					WHERE id_disciplina = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("si", $nome_disciplina, $id_disciplina);
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
		<title>MPP - Disciplinas</title>
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
							<h2> Gestão de Disciplinas</h2>

							<!-- Modal com formulário pra editar -->
							<div id="modal-gestao" class="modal-overlay" style="display: none;">
								<div class="box modal-content">
									<span id="close-modal" class="modal-close">&times;</span>
									<h3>Editar Disciplina</h3>

									<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
										<input type="hidden" name="edit_id_disciplina" value="" id="edit_id_disciplina">
										<div class="row">
											<div class="col-8">
												<input type="text" name="edit_nome_disciplina" id="edit_nome_disciplina">
											</div>
											<div class="col-4">
												<input type="submit" name="editar" value="editar">
											</div>
										</div>
									</form>
								</div>
							</div>


							<!-- Formulário para inserir linha na tabela -->
							<label for="form-disciplina">Adicionar disciplina:</label>
							<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" id="form_disciplina">
								<div class="row">
									<div class="col-3 col-12xsmall">
										<input type="text" name="nome_disciplina" required>
									</div>
									<div class="col-3 col-12xsmall">
										<input type="submit" name="adicionar" value="adicionar">
									</div>
								</div>
							</form>

							<!-- Tabela mostrando os dados -->
							<div class="table-wrapper">
								<table class="alt">
									<thead>
										<tr>
											<th>Id Disciplina</th>
											<th>Nome Disciplina</th>
											<th>Ações</th>
										</tr>
									</thead>

									<tbody>
										<?php
										// Busca os dados à base de dados e constrói a tabela
										$sql = "SELECT * FROM disciplina";
										$stmt = $conn->prepare($sql);
										$stmt->execute();
										$resultado = $stmt->get_result();

										if ($resultado == false) {
											echo "Erro ao buscar à base de dados: " . $stmt->error;
											exit();
										}

										if ($resultado->num_rows == 0) { ?>
											<td colspan="3">Sem itens na tabela</td>
										<?php

										}

										$linhas = $resultado->fetch_all(MYSQLI_ASSOC);

										foreach ($linhas as $linha) { ?>
											<tr>
												<td id="id_disciplina"><?= $linha['id_disciplina'] ?></td>
												<td id="nome_disciplina_<?= $linha['id_disciplina'] ?>"><?= $linha['nome_disciplina'] ?></td>
												<td><button class="primary small" onclick="editar(<?= $linha['id_disciplina'] ?>)">Editar</button></td>
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
				const nome_disciplina = document.getElementById(`nome_disciplina_${id}`).innerText;

				document.getElementById('edit_id_disciplina').value = id;
				document.getElementById('edit_nome_disciplina').value = nome_disciplina;

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