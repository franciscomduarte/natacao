<?php
 
     class Campeonato {
 
         private $conn;
 
         public function __construct($pdo) {
             $this->conn = $pdo;
         }
 
         public function listarCampeonatos() {
             $sql = "SELECT * FROM campeonato";
             $stmt = $this->conn->query($sql);
             return $stmt->fetchAll(PDO::FETCH_ASSOC);
         }
 
         public function listar() {
            $sql = "SELECT * FROM campeonato";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function buscarPorId($id) {
            $stmt = $this->conn->prepare("SELECT * FROM campeonato WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    
        public function inserir($nome, $cidade, $piscina, $realizacao, $ano, $chave) {
            $stmt = $this->conn->prepare("INSERT INTO campeonato (nome, cidade, piscina, realizacao, ano, chave) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$nome, $cidade, $piscina, $realizacao, $ano, $chave]);
        }
    
        public function atualizar($id, $nome, $cidade, $piscina, $realizacao, $ano, $chave) {
            $stmt = $this->conn->prepare("UPDATE campeonato SET nome = ?, cidade = ?, piscina = ?, realizacao = ?, ano = ?, chave = ? WHERE id = ?");
            return $stmt->execute([$nome, $cidade, $piscina, $realizacao, $ano, $chave, $id]);
        }
             // Método para contar total de provas
         public function contarCampeonatos() {
             $sql = "SELECT COUNT(*) AS total FROM campeonato";
             $stmt = $this->conn->prepare($sql);
             $stmt->execute();
 
             $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
             return $resultado ? $resultado['total'] : 0;
         }
 
     }
 ?>