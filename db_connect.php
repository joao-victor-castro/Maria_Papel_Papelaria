<?php
// db_connect.php 



ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

// Configurações da base de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mpp3_bd');
define('DB_CHARSET', 'utf8');

// Definir timezone para Portugal - ESSENCIAL para datas corretas
date_default_timezone_set('Europe/Lisbon');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    // Definir charset
    if (!$conn->set_charset(DB_CHARSET)) {
        throw new Exception("Erro ao definir o charset: " . $conn->error);
    }
    
} catch (Exception $e) {
    // Em produção, registe o erro em um log em vez de exibir
    error_log($e->getMessage());
    die("Erro na conexão à base de dados. Por favor, tente novamente mais tarde.");
}

// Agora a conexão está pronta para usar
?>