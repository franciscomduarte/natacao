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
