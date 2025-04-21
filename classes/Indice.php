<?php
 
     class Indice {
 
         private $conn;
 
         public function __construct($pdo) {
             $this->conn = $pdo;
         }
 
         public function listarCatergorias() {
             $sql = "SELECT distinct categoria FROM indice_masculino_25m order by categoria";
             $stmt = $this->conn->query($sql);
             return $stmt->fetchAll(PDO::FETCH_ASSOC);
         }

         public function listarProvasFINA() {
            $sql = "SELECT * FROM basetime";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
 
         public function listarIndice25m($prova, $categoria, $sexo) {
             if($sexo == 'MASCULINO') {
                 $sql = "SELECT * FROM indice_masculino_25m WHERE prova = :prova AND categoria = :categoria";
             } else {
                 $sql = "SELECT * FROM indice_feminino_25m WHERE prova = :prova AND categoria = :categoria";
             }
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':prova', $prova);
             $stmt->bindParam(':categoria', $categoria);
             $stmt->execute();
             return $stmt->fetchAll(PDO::FETCH_ASSOC);
         }

         public function obterTempoReferenciaFINA($prova, $sexo) {
            $sql = "SELECT * FROM basetime WHERE prova = :prova AND sexo = :sexo";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':prova', $prova);
            $stmt->bindParam(':sexo', $sexo);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
 
         public function listarIndice50m($prova, $categoria, $sexo) {
             if($sexo == 'MASCULINO') {
                 $sql = "SELECT * FROM indice_masculino_50m WHERE prova = :prova AND categoria = :categoria";
             } else {
                 $sql = "SELECT * FROM indice_feminino_50m WHERE prova = :prova AND categoria = :categoria";
             }
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':prova', $prova);
             $stmt->bindParam(':categoria', $categoria);
             $stmt->execute();
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