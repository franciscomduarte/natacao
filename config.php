<?php

include_once ("utils.php");


// Caminho absoluto do diretório raiz da aplicação
define('APP_ROOT', dirname(__DIR__));

// Caminho base para links (ajuste se a app estiver em uma subpasta)
define('BASE_URL', '/natacao'); // ex: '/admin', ou '' se estiver na raiz

//Carrega o arquivo de configurações
carregarEnv(APP_ROOT . '/natacao/.env');

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
});


// Função auxiliar para redirecionar
function redirecionar($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}
