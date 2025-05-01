<?php
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");

$db = new Conexao();
$pdo = $db->conectar();

$obj = new Atleta($pdo);
$id = $_GET['id'] ?? null;

if ($id) {
    $obj->excluir($id);
}

header("Location: index.php");
exit;