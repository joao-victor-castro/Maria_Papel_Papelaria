<?php
// login.php - Sistema de autenticação MPP com regras de negócio
session_start();

// Incluir configuração da base de dados
include('db_connect.php');

// Processar o formulário de login
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Por favor, preencha todos os campos.';
    } else {
        try {
            // Verificar as credenciais
            $sql = "SELECT id_utilizador, username, nome_utilizador, password_hash, 
                           e_administrador, utilizador_ativo, tentativas_falhas, password_alterada
                    FROM utilizador 
                    WHERE username = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user) {
                // REGRA: Se tentativas_falhas >= 3, bloquear conta automaticamente
                if ($user['tentativas_falhas'] >= 3) {
                    // Atualizar para inativo
                    $bloqueio_sql = "UPDATE utilizador 
                                     SET utilizador_ativo = 0 
                                     WHERE username = ?";
                    $bloqueio_stmt = $conn->prepare($bloqueio_sql);
                    $bloqueio_stmt->bind_param("s", $username);
                    $bloqueio_stmt->execute();
                    $bloqueio_stmt->close();
                    
                    $user['utilizador_ativo'] = 0;
                    $error_message = 'Conta bloqueada devido a múltiplas tentativas falhadas. Contacte o administrador.';
                }
                // REGRA: Verificar se o utilizador está ativo
                elseif (!$user['utilizador_ativo']) {
                    $error_message = 'Esta conta está desativada. Contacte o administrador.';
                }
                // Verificar a password
                elseif (password_verify($password, $user['password_hash'])) {
                    // LOGIN BEM-SUCEDIDO
                    
                    // REGRA: Resetar tentativas_falhas para 0
                    $update_sql = "UPDATE utilizador 
                                   SET ultimo_login = NOW(), 
                                       tentativas_falhas = 0 
                                   WHERE id_utilizador = ?";
                    
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $user['id_utilizador']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Guardar informações na sessão
                    $_SESSION['user_id'] = $user['id_utilizador'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nome_utilizador'] = $user['nome_utilizador'];
                    $_SESSION['e_administrador'] = $user['e_administrador'];
                    $_SESSION['password_alterada'] = $user['password_alterada'];
                    $_SESSION["sinal_anterior"] = time();
                    
                    // REGRA: Se password_alterada = 0, redirecionar para alteração de password
                    if ($user['password_alterada'] == 0) {
                        $_SESSION['require_password_change'] = true;
                        header('Location: alterar_password.php?first=1');
                        exit();
                    }
                    
                    // Redirecionar para a página principal
                    header('Location: index.php');
                    exit();
                    
                } else {
                    // Password incorreta - REGRA: Incrementar tentativas_falhas
                    $tentativas_falhas = $user['tentativas_falhas'] + 1;
                    
                    $update_sql = "UPDATE utilizador 
                                   SET tentativas_falhas = ? 
                                   WHERE username = ?";
                    
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("is", $tentativas_falhas, $username);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Verificar se agora chegou a 3 tentativas para bloquear
                    if ($tentativas_falhas >= 3) {
                        $bloqueio_sql = "UPDATE utilizador 
                                         SET utilizador_ativo = 0 
                                         WHERE username = ?";
                        $bloqueio_stmt = $conn->prepare($bloqueio_sql);
                        $bloqueio_stmt->bind_param("s", $username);
                        $bloqueio_stmt->execute();
                        $bloqueio_stmt->close();
                        
                        $error_message = 'Conta bloqueada devido a 3 tentativas falhadas. Contacte o administrador.';
                    } else {
                        $tentativas_restantes = 3 - $tentativas_falhas;
                        $error_message = "Credenciais inválidas. Tentativa $tentativas_falhas de 3. Restam $tentativas_restantes tentativas.";
                    }
                }
            } else {
                // Username não existe - não incrementamos tentativas para evitar enumeração de utilizadores
                $error_message = 'Credenciais inválidas. Por favor, tente novamente.';
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $error_message = 'Erro no sistema. Por favor, tente mais tarde.';
        }
    }
}

// Verificar se já está autenticado
if (isset($_SESSION['user_id'])) {
    // REGRA: Se password_alterada = 0, redirecionar para alteração
    if (isset($_SESSION['password_alterada']) && $_SESSION['password_alterada'] == 0) {
        header('Location: alterar_password.php?first=1');
        exit();
    }
    
    // Se já estiver autenticado, redirecionar para index.php
    header('Location: index.php');
    exit();
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
		<title>MPP - Login</title>
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
                <section>
                    <header class="main">
                        <h2>Autenticação</h2>
                    </header>
								
								<!-- Mensagens de Erro -->
								<?php if (!empty($error_message)): ?>
									<div class="mensagem <?php echo strpos($error_message, 'bloqueada') !== false ? 'error' : 'warning'; ?>">
										<strong><?php echo strpos($error_message, 'bloqueada') !== false ? '❌ Conta Bloqueada' : '⚠️ Atenção'; ?>:</strong> 
										<?php echo htmlspecialchars($error_message); ?>
									</div>
								<?php endif; ?>
								
								<!-- Formulário de Login -->
								<div class="row">
									<div class="col-6 col-12-small">
										<div class="box">
											<h3>Login</h3>
											<form method="post" action="">
												<div class="row gtr-uniform">
													<div class="col-12">
														<label for="username">Username</label>
														<input type="text" name="username" id="username" 
															   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
															   placeholder="Digite o seu username" 
															   required 
															   autocomplete="username" />
													</div>
													<div class="col-12">
														<label for="password">Password</label>
														<input type="password" name="password" id="password" 
															   placeholder="Digite a sua password" 
															   required 
															   autocomplete="current-password" />
													</div>
													<div class="col-12">
														<ul class="actions">
															<li><input type="submit" value="Entrar" class="primary" /></li>
															<li><input type="reset" value="Limpar" class="primary" /></li>
														</ul>
													</div>
												</div>
											</form>
											
											<!-- Informações de Segurança -->
											<div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
												<h4>Política de Segurança</h4>
												<ul style="font-size: 0.9em;">
													<li>3 tentativas falhadas = conta bloqueada</li>
													<li>Primeiro acesso = alteração obrigatória de password</li>
													<li>Contas inativas não conseguem autenticar</li>
                                                    <li>Conta bloqueada? Contacte o administrador para reativar a sua conta.</li>
												</ul>
											</div>
										</div>
									</div>
									
								</div>
								
							</section>
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
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
			
			<!-- Script para melhorar UX do formulário -->
			<script>
			$(document).ready(function() {
				// Focar no campo username ao carregar a página
				$('#username').focus();
				
				// Prevenir reenvio do formulário ao atualizar a página
				if (window.history.replaceState) {
					window.history.replaceState(null, null, window.location.href);
				}
				
				// Mostrar/ocultar password
				$('#password').after('<span style="cursor:pointer; margin-left:10px;" class="toggle-password">👁️</span>');
				$('.toggle-password').click(function() {
					var passwordField = $('#password');
					var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
					passwordField.attr('type', type);
					$(this).text(type === 'password' ? '👁️' : '🙈');
				});
			});
			</script>

	</body>
</html>
<?php
// Fechar conexão
if (isset($conn)) {
    $conn->close();
}
?>