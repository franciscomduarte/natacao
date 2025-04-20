<?php
 class Conexao {
     private $host;
     private $dbname;
     private $username;
     private $password;
     private $conn;
 
     public function __construct() {
        Conexao::carregarEnv(__DIR__ . '/.env');
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $dbname = getenv('DB_NAME');

        $this->host = $host;
        $this->username = $user;
        $this->password = "";
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
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhas as $linha) {
            if (strpos(trim($linha), '#') === 0) continue; // ignora comentários
            list($chave, $valor) = explode('=', $linha, 2);
            putenv(trim($chave) . '=' . trim($valor));
        }
    }
 }
 
 ?>