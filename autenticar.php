<?php

require_once 'config.php';

$db = new Conexao();
$pdo = $db->conectar();
$usuarioObj = new Usuario($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuario = $usuarioObj->autenticar($email, $senha);
    if ($usuario) {
        session_start();
        $_SESSION['usuario_logado'] = $usuario;
        header("Location: private/index.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
} else {
    $erro = "E-mail ou senha inválidos.";
    exit;
}
?>
