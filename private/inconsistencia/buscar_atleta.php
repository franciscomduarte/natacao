<?php
include_once("../../config.php");

header('Content-Type: application/json');

$registro = $_GET['registro'] ?? '';
$pdo = (new Conexao())->conectar();
$atletaObj = new Atleta($pdo);

$atleta = $atletaObj->buscarPorRegistro($registro);

echo json_encode($atleta ?: []);
