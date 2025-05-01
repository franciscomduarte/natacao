<?php
include($_SERVER['DOCUMENT_ROOT'] . "/natacao/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . "/natacao/head.php");

$registro = $_GET['registro'] ?? '';
$pdo = (new Conexao())->conectar();
$atletaObj = new Atleta($pdo);

$atleta = $atletaObj->buscarPorRegistro($registro);

echo json_encode($atleta ?: []);
