<?php
 class Conexao {
     private $host;
     private $dbname;
     private $username;
     private $password;
     private $conn;
 
     public function __construct() {
        Conexao::carregarEnv(__DIR__ . '/.env');

        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        $this->host = $host;
        $this->username = $user;
        $this->password = $pass;
        $this->dbname = $dbname;
    }
     public function conectar() {
         if ($this->conn == null) {
             try {
                 $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
                 $this->conn = new PDO($dsn, $this->username, $this->password);
                 $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             } catch (PDOException $e) {
                 echo "Erro de conexão: " . $e->getMessage();
                 exit;
             }
         }
         return $this->conn;
     }

     public static function carregarEnv($caminho) {
        if (!file_exists($caminho)) return;
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhas as $linha) {
            if (strpos(trim($linha), '#') === 0) continue;
            list($chave, $valor) = explode('=', $linha, 2);
            $_ENV[trim($chave)] = trim($valor);
        }
    }
 }
 
 ?>