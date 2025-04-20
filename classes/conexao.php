<?php
class Conexao {
    private $host = "localhost";
    private $dbname = "natacao";
    private $username = "root";
    private $password = "";
    private $conn;

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
}

?>
