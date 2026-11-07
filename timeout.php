<?php
session_start();

// Recebe o sinal
if($_SERVER["REQUEST_METHOD"]  == "POST"){
    $request = json_decode(file_get_contents('php://input'),true);
    
    $limite = 60; // 1 minuto
    $sinal = time();
    $sinal_anterior = $_SESSION["sinal_anterior"];
    $_SESSION["sinal_anterior"] = $sinal;

    if(($sinal - $sinal_anterior) > $limite){
        unset($_SESSION["sinal_anterior"]);
        echo json_encode(['expirou'=>true]);
        session_destroy();
        exit();
    }

    echo json_encode(['expirou'=> false]);
    exit();
}