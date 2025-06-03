<?php
 class Resultado {
     private $conn;
 
     public function __construct($pdo) {
         $this->conn = $pdo;
     }

     //TODO ainda falta finalizar.
     public function inserir() {

        try {
            $stmt = $this->conn->prepare("INSERT INTO resultado (prova_id,atleta_id,colocacao,serie,raia,atleta,registro,nascimento,entidade,tempo,pontos,indice) VALUES (:prova_id, :atleta_id, :colocacao, :serie, :raia, :atleta, :registro, :nascimento, :entidade, :tempo, :pontos, :indice)");       
            $stmt->bindParam(':prova_id', $this->prova_id);
            $stmt->bindParam(':atleta_id', $this->atleta_id);
            $stmt->bindParam(':colocacao', $this->colocacao);
            $stmt->bindParam(':serie', $this->serie);
            $stmt->bindParam(':raia', $this->raia);
            $stmt->bindParam(':atleta', $this->atleta);
            $stmt->bindParam(':registro', $this->registro);
            $stmt->bindParam(':nascimento', $this->nascimento);
            $stmt->bindParam(':entidade', $this->entidade);
            $stmt->bindParam(':tempo', $this->tempo);
            $stmt->bindParam(':pontos', $this->pontos);
            $stmt->bindParam(':indice', $this->indice);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return true;

    }
 
     
     public function listarEntidades() {
         $sql = "SELECT distinct entidade FROM resultado";
         $stmt = $this->conn->query($sql);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }
 
     public function listarAtletas() {
         $sql = "SELECT nome FROM atleta Order by nome";
         $stmt = $this->conn->query($sql);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }
 
     public function listarAtleta($atleta) {
         $sql = "SELECT * FROM resultado WHERE atleta_id = :atleta Order by atleta limit 1";
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(':atleta', $atleta);
         $stmt->execute();
         return $stmt->fetch(PDO::FETCH_ASSOC);
     }
 
     public function listarAnos() {
         $sql = "SELECT distinct ano FROM campeonato";
         $stmt = $this->conn->query($sql);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }
 
     public function melhoresTemposPorProva($atleta) {
         $sql = "SELECT distinct r.atleta as atleta, min(tempo) as tempo, descricao as prova_descricao, r.prova as prova
                 FROM resultado as r
                     JOIN prova as p ON r.prova_id = p.id
                 WHERE atleta_id = :atleta
                 GROUP BY r.atleta, descricao";
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(':atleta', $atleta);
         $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }
 
     public function filtrarResultados($filtros) {
         $sql = "
             SELECT 
                 c.nome AS campeonato,
                 c.ano AS ano,
                 p.data AS data,
                 p.descricao AS descricao,
                 a.nome,
                 r.entidade,
                 r.tempo,
                 r.colocacao,
                 r.pontos
             FROM resultado r
             INNER JOIN prova p ON r.prova_id = p.id
             INNER JOIN campeonato c ON p.campeonato_id = c.id
             INNER JOIN atleta a ON r.atleta_id = a.id
             WHERE 1=1
         ";
 
         $params = [];
 
         // Filtros dinâmicos
         if (!empty($filtros['campeonato'])) {
             $sql .= " AND c.id = ?";
             $params[] = $filtros['campeonato'];
         }
 
         if (!empty($filtros['entidade'])) {
             $sql .= " AND r.entidade = ? ";
             $params[] = $filtros['entidade'];
         }
 
         if (!empty($filtros['ano'])) {
             $sql .= " AND YEAR(p.data) = ?";
             $params[] = $filtros['ano'];
         }
 
         if (!empty($filtros['piscina'])) {
             $sql .= " AND c.piscina = ?";
             $params[] = $filtros['piscina'];
         }
 
         $sql .= " ORDER BY p.data, p.descricao, r.colocacao";

         $stmt = $this->conn->prepare($sql);
         $stmt->execute($params);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }  
    
 }