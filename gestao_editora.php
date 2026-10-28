<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Verifica se o formulário de adição foi submetido
	if (isset($_POST['adicionar'])) {
		$nome_editora = $_POST['nome_editora'];

		// Verifica se já existe uma editora com esse nome
		$sql = "SELECT * FROM editora
					WHERE nome_editora = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_editora);
		$resultado = $stmt->execute();
		$rows = $stmt->get_result();
		$rows = $rows->num_rows;
		$stmt->close();

		if ($rows > 0) {
			echo "<script type='text/javascript'>
						alert('Já existe uma editora com esse nome.');
						window.location.replace('gestao_editora.php');
					</script>";
			exit();
		}

		$sql = "INSERT INTO editora (nome_editora) VALUES (?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_editora);
		$resultado = $stmt->execute();

		if ($resultado == false) {
			echo "Erro ao adicionar à base de dados: " . $stmt->error;
			exit();
		}

		header('Location: ' . $_SERVER['PHP_SELF']);
		exit();
	} 
	else if (isset($_POST['editar'])) {
		$id_editora = intval($_POST['edit_id_editora']);
		$nome_editora = $_POST['edit_nome_editora'];

		// Verifica se já existe uma editora com esse nome
		$sql = "SELECT * FROM editora
					WHERE nome_editora = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_editora);
		$resultado = $stmt->execute();
		$rows = $stmt->get_result();
		$rows = $rows->num_rows;
		$stmt->close();

		if ($rows > 0) {
			echo "<script type='text/javascript'>
						alert('Já existe uma editora com esse nome na base de dados.');
						window.location.replace('gestao_editora.php');
					</script>";
			exit();
		}

		$sql = "UPDATE editora
					SET nome_editora = ?
					WHERE id_editora = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $nome_editora, $id_editora);
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
	<title>MPP - Editoras</title>
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
						<h2>Gestão de Editoras</h2>

						<!-- Modal com formulário pra editar -->
						<div id="modal-gestao" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal" class="modal-close">&times;</span>
								<h3>Editar Editora</h3>

								<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
									<input type="hidden" name="edit_id_editora" value="" id="edit_id_editora">
									<div class="row">
										<div class="col-8">
											<input type="text" name="edit_nome_editora" id="edit_nome_editora">
										</div>
										<div class="col-4">
											<input type="submit" name="editar" value="editar">
										</div>
									</div>
								</form>
							</div>
						</div>


						<!-- Formulário para inserir linha na tabela -->
						<label for="form-editora">Adicionar Editora:</label>
						<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" id="form_editora">
							<div class="row">
								<div class="col-3 col-12xsmall">
									<input type="text" name="nome_editora" required>
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
										<th>Id Editora</th>
										<th>Nome Editora</th>
										<th>Ações</th>
									</tr>
								</thead>

								<tbody>
									<?php
									// Busca os dados à base de dados e constrói a tabela
									$sql = "SELECT * FROM editora";
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
											<td id="id_editora"><?= $linha['id_editora'] ?></td>
											<td id="nome_editora_<?= $linha['id_editora'] ?>"><?= $linha['nome_editora'] ?></td>
											<td><button class="primary small" onclick="editar(<?= $linha['id_editora'] ?>)">Editar</button></td>
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
			const nome_editora = document.getElementById(`nome_editora_${id}`).innerText;

			document.getElementById('edit_id_editora').value = id;
			document.getElementById('edit_nome_editora').value = nome_editora;

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