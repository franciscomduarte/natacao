<?php
class Resultado {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    
    public function listarEntidades() {
        $sql = "SELECT distinct entidade FROM resultado";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtletas() {
        $sql = "SELECT distinct atleta FROM resultado Order by atleta";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtleta($atleta) {
        $sql = "SELECT * FROM resultado WHERE atleta = :atleta Order by atleta limit 1";
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
                WHERE atleta = :atleta
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
                r.atleta,
                r.entidade,
                r.tempo,
                r.colocacao,
                r.pontos
            FROM resultado r
            INNER JOIN prova p ON r.prova_id = p.id
            INNER JOIN campeonato c ON p.campeonato_id = c.id
            WHERE 1=1
        ";

        $params = [];

        // Filtros dinâmicos
        if (!empty($filtros['campeonatos'])) {
            $placeholders = implode(',', array_fill(0, count($filtros['campeonatos']), '?'));
            $sql .= " AND c.id IN ($placeholders)";
            $params = array_merge($params, $filtros['campeonatos']);
        }

        if (!empty($filtros['entidades'])) {
            $placeholders = implode(',', array_fill(0, count($filtros['entidades']), '?'));
            $sql .= " AND r.entidade IN ($placeholders)";
            $params = array_merge($params, $filtros['entidades']);
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