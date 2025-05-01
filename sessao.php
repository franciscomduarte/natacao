<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_logado'])) {
    header("Location:" . BASE_URL . "/index.php");
    exit;
}