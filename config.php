<?php

include_once('utils.php');

// Caminho absoluto do diretório raiz da aplicação
define('APP_ROOT', dirname(__DIR__));

// Caminho base para links (ajuste se a app estiver em uma subpasta)
if ($_SERVER['HTTP_HOST'] === 'localhost:83' || $_SERVER['HTTP_HOST'] === 'localhost') {
    define('BASE_URL', '/natacao');
} else {
    define('BASE_URL', '');
} // ex: '/admin', ou '' se estiver na raiz

//Carrega o arquivo de configurações
carregar_env(APP_ROOT . BASE_URL . '/.env');

// Inicializa a sessão com segurança
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura o timezone padrão
date_default_timezone_set('America/Sao_Paulo');

// Autoload opcional (se estiver usando classes com PSR-4 ou similares)
spl_autoload_register(function ($classe) {
    $caminho = __DIR__ . '/classes/' . $classe . '.php';
    if (file_exists($caminho)) {
        require_once $caminho;
    }
    $caminho = __DIR__ . '/classes/' . $classe . '.php';
});

// Função auxiliar para redirecionar
function redirecionar($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

function carregar_env($caminho) {
    if (!file_exists($caminho)) return;
    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        if (strpos(trim($linha), '#') === 0) continue;
        list($chave, $valor) = explode('=', $linha, 2);
        $_ENV[trim($chave)] = trim($valor);
    }
}
