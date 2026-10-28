<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Verifica se o formulário de adição foi submetido
	if (isset($_POST['editar'])) {
		$id_ano_letivo = intval($_POST['edit_id_ano_letivo']);
		$nome_ano_letivo = $_POST['edit_nome_ano_letivo'];

		// Verifica se já existe um ano letivo com esse nome
		$sql = "SELECT * FROM ano_letivo
					WHERE nome_ano_letivo = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_ano_letivo);
		$resultado = $stmt->execute();
		$rows = $stmt->get_result();
		$rows = $rows->num_rows;
		$stmt->close();

		if ($rows > 0) {
			echo "<script type='text/javascript'>
						alert('Já existe um ano letivo com esse nome.');
						window.location.replace('gestao_ano_letivo.php');
					</script>";
			exit();
		}

		$sql = "UPDATE ano_letivo
					SET nome_ano_letivo = ?
					WHERE id_ano_letivo = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $nome_ano_letivo, $id_ano_letivo);
		$resultado = $stmt->execute();

		if ($resultado == false) {
			echo "Erro ao editar na base de dados: " . $stmt->error;
			exit();
		}

		header('Location: ' . $_SERVER['PHP_SELF']);
		exit();
	} else if (isset($_POST["fechar"])) {
		$id_ano_letivo = $_POST["fechar_id_ano_letivo"];
		$nome_ano_letivo = $_POST["novo_nome_ano_letivo"];

		// Verifica se já existe um ano letivo com esse nome
		$sql = "SELECT * FROM ano_letivo
					WHERE nome_ano_letivo = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $nome_ano_letivo);
		$resultado = $stmt->execute();
		$rows = $stmt->get_result();
		$rows = $rows->num_rows;
		$stmt->close();

		if ($rows > 0) {
			echo "<script type='text/javascript'>
						alert('Já existe um ano letivo com esse nome.');
						window.location.replace('gestao_ano_letivo.php');
					</script>";
			exit();
		}

		$conn->begin_transaction();

		try {
			// Colocar todos os outros anos letivos como fechados (ter certeza que nao existem 2 ativos)
			$stmt_fechar_ano = $conn->prepare(
				"UPDATE ano_letivo
                    SET ano_letivo_ativo = 0"
			);
			$stmt_fechar_ano->execute();

			// fazer a soma de encomendas do ano letivo
			$stmt_soma_encomendas = $conn->prepare(
				"SELECT SUM(encomendas_ano - encomendas_inicial) AS encomendas_ano_letivo
                    FROM ano_escolar"
			);
			$stmt_soma_encomendas->execute();
			$result = $stmt_soma_encomendas->get_result()->fetch_assoc();
			$encomendas_ano_letivo = $result["encomendas_ano_letivo"];

			// Atualizar total ano letivo
			$stmt_atualizar_total = $conn->prepare(
				"UPDATE ano_letivo
                    SET enc_ano_letivo = ?
                    WHERE id_ano_letivo = ?"
			);
			$stmt_atualizar_total->bind_param("ii", $encomendas_ano_letivo, $id_ano_letivo);
			$stmt_atualizar_total->execute();

			// zerar contagem dos anos escolares
			$stmt_zerar_anos = $conn->prepare(
				"UPDATE ano_escolar
                    SET encomendas_ano = encomendas_inicial"
			);
			$stmt_zerar_anos->execute();

			// limpar tabelas encomenda, manual_encomenda, observacao_encomenda
			$conn->query("DELETE FROM observacao_encomenda");
			$conn->query("DELETE FROM encomenda_manual");
			$conn->query("DELETE FROM encomenda");
			$conn->query("DELETE FROM encomenda_editora");
			$conn->query("DELETE FROM aviso_encomendas");
			$conn->query("UPDATE manual SET quant_manuais_pedir = 0, quant_manuais_enc = 0");

			// Volta os IDs pra 1
			$conn->query("ALTER TABLE encomenda_editora AUTO_INCREMENT = 1");
			$conn->query("ALTER TABLE observacao_encomenda AUTO_INCREMENT = 1");
			$conn->query("ALTER TABLE encomenda_manual AUTO_INCREMENT = 1");
			$conn->query("ALTER TABLE encomenda AUTO_INCREMENT = 1");


			// apagar ficheiros de /encomenda e /encomendas_a_editora
			$pastaPDF = __DIR__ . '/encomendas/';
			$pastaExcel = __DIR__ . '/encomendas_a_editora/';
			$pastaAvisos = __DIR__ . '/avisos/';

			// PDF encomendas
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($pastaPDF, RecursiveDirectoryIterator::SKIP_DOTS)
			);

			foreach ($iterator as $item) {
				if ($item->isFile() && $item->getExtension() == 'pdf') {
					unlink($item->getPathName());
				}
			}

			// pdf avisos
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($pastaAvisos, RecursiveDirectoryIterator::SKIP_DOTS)
			);

			foreach ($iterator as $item) {
				if ($item->isFile() && $item->getExtension() == 'pdf') {
					unlink($item->getPathName());
				}
			}

			// Excel
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($pastaExcel, RecursiveDirectoryIterator::SKIP_DOTS)
			);

			foreach ($iterator as $item) {
				if ($item->isFile() && $item->getExtension() == 'xlsx') {
					unlink($item->getPathName());
				}
			}

			// criar novo ano letivo
			$stmt_novo_ano = $conn->prepare(
				"INSERT INTO ano_letivo (nome_ano_letivo, ano_letivo_ativo)
                    VALUES (?,?)"
			);
			$ativo = 1;
			$stmt_novo_ano->bind_param("si", $nome_ano_letivo, $ativo);
			$stmt_novo_ano->execute();



			$stmt_fechar_ano->close();
			$stmt_soma_encomendas->close();
			$stmt_atualizar_total->close();
			$stmt_zerar_anos->close();
			$stmt_novo_ano->close();
			$conn->commit();

			// Alert de sucesso
			echo "<script>
                        alert('Novo ano letivo iniciado com sucesso');
                        location.href = 'gestao_ano_letivo.php';
                    </script>";
			exit();
		} catch (Exception $e) {
			$conn->rollback();
			echo "<script>
                    alert('Não foi possível fechar o ano letivo');
                    console.log($e);
                    location.href = 'gestao_ano_letivo.php';
                </script>";
			exit();
		}
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
	<title>MPP - Ano letivo</title>
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
						<h2>Gestão de anos letivos</h2>

						<!-- Modal com formulário pra editar -->
						<div id="modal-gestao" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal" class="modal-close">&times;</span>
								<h3>Editar ano letivo</h3>

								<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
									<input type="hidden" name="edit_id_ano_letivo" value="" id="edit_id_ano_letivo">
									<div class="row">
										<div class="col-8">
											<input type="text" name="edit_nome_ano_letivo" id="edit_nome_ano_letivo" required>
										</div>
										<div class="col-4">
											<input type="submit" name="editar" value="editar">
										</div>
									</div>
								</form>
							</div>
						</div>

						<!-- Modal de fechar ano letivo -->
						<div class="modal-overlay modal-fechar" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal" class="modal-close">&times;</span>
								<h3>Fechar ano letivo</h3>

								<div id="confirmar-fechar">
									<p>Tem a certeza de que quer fechar o ano letivo? Terá que criar outro ano em seguida</p>
									<button name="btnFechar" onclick="confirmarFecharAno()">Sim</button>
									<button name="btnFechar" onclick="fecharModal()">Não</button>
								</div>

								<div id="fechar-ano" style="display: none;">
									<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
										<input type="hidden" name="fechar_id_ano_letivo" value="" id="fechar_id_ano_letivo">
										<div class="row">
											<div class="col-12" style="margin-bottom: 10px;">
												<label for="novo_nome_ano_letivo">Introduza o nome do novo ano letivo:</label>
												<input type="text" name="novo_nome_ano_letivo" id="novo_nome_ano_letivo" required>
											</div>
											<div class="col-6 col-12-small">
												<input type="submit" name="fechar" value="Fechar e criar novo ano letivo">
											</div>
											<div class="col-4">
												<button onclick="fecharModal()" class="primary">Voltar atrás</button>
											</div>
										</div>
									</form>
								</div>

							</div>
						</div>




						<!-- Tabela mostrando os dados -->
						<div class="table-wrapper">
							<table class="alt">
								<thead>
									<tr>
										<th>Id ano letivo</th>
										<th>Nome ano letivo</th>
										<th>Num. encomendas</th>
										<th> </th>
									</tr>
								</thead>

								<tbody>
									<?php
									// Busca os dados à base de dados e constrói a tabela
									$sql = "SELECT * FROM ano_letivo 
                                                                ORDER BY id_ano_letivo DESC";
									$stmt = $conn->prepare($sql);
									$stmt->execute();
									$resultado = $stmt->get_result();

									if ($resultado == false) {
										echo "Erro ao buscar à base de dados: " . $stmt->error;
										exit();
									}

									if ($resultado->num_rows == 0) { ?>
										<td colspan="4">Sem itens na tabela</td>
									<?php

									}

									$linhas = $resultado->fetch_all(MYSQLI_ASSOC);

									foreach ($linhas as $linha) { ?>
										<tr>
											<td id="id_ano_letivo"><?= $linha['id_ano_letivo'] ?></td>
											<td id="nome_ano_letivo_<?= $linha['id_ano_letivo'] ?>"><?= $linha['nome_ano_letivo'] ?></td>
											<td id="enc_ano_letivo"><?= $linha["enc_ano_letivo"] ?></td>
											<td>
												<?php
												if ($linha["ano_letivo_ativo"] == 1) { ?>
													<button class="primary small" onclick="editar(<?= $linha['id_ano_letivo'] ?>)">Editar</button>
													<button class="secondary small" onclick="fecharAno(<?= $linha['id_ano_letivo'] ?>)">Fechar ano letivo</button>
													<strong style="color: green;">Ativo</strong>
												<?php }
												?>
											</td>
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
		const modal = document.querySelector('.modal-fechar');

		function confirmarFecharAno() {
			document.getElementById('confirmar-fechar').style.display = "none";
			document.getElementById('fechar-ano').style.display = "flex";
		}

		function fecharModal() {
			modal.style.display = "none";
		}

		function fecharAno(id) {
			modal.style.display = "flex";

			document.getElementById('fechar_id_ano_letivo').value = id;
		}

		function editar(id) {
			// Popula o modal
			const nome_editora = document.getElementById(`nome_ano_letivo_${id}`).innerText;

			document.getElementById('edit_id_ano_letivo').value = id;
			document.getElementById('edit_nome_ano_letivo').value = nome_editora;

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