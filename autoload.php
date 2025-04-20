<?php
spl_autoload_register(function ($classe) {
    $caminho = __DIR__ . '/classes/' . $classe . '.php';
    if (file_exists($caminho)) {
        require_once $caminho;
    }
});
