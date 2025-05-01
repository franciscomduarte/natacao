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
         $sql = "SELECT nome FROM atleta Order by nome";
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
 
     function calcularBolsaAtletaEstudantil($ano): array {
        $pontuacao = [
            1 => 10,
            2 => 8,
            3 => 6,
            4 => 4,
            5 => 2
        ];
    
        // Campeonatos válidos para estudantil
        $chavesValidas = [
            'centro-oeste-1-sem',
            'centro-oeste-2-sem',
            'brasiliense-verao',
            'brasiliense-inverno',
            'brasileiro-verao',
            'brasileiro-inverno'
        ];
    
        // Consulta resultados válidos
        $sql = "
            SELECT 
                r.atleta_id,
                a.nome,
                a.registro,
                a.nascimento,
                r.colocacao,
                p.descricao AS prova_descricao,
                c.ano
            FROM resultado r
            JOIN atleta a ON r.atleta_id = a.id
            JOIN prova p ON r.prova_id = p.id
            JOIN campeonato c ON p.campeonato_id = c.id
            WHERE 1 = 1
            AND c.id = 1
            
            -- AND c.ano = ?
            AND r.colocacao BETWEEN 1 AND 5
        ";
        #AND c.chave IN (" . implode(',', array_fill(0, count($chavesValidas), '?')) . ")
        #$params = array_merge($chavesValidas, [$ano]);
        $stmt = $this->conn->prepare($sql);
        #$stmt->execute($params);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $pontuacoes = [];
        foreach ($resultados as $row) {
            $id = $row['atleta_id'];
            $pontos = $pontuacao[(int)$row['colocacao']] ?? 0;
    
            if (!isset($pontuacoes[$id])) {
                $pontuacoes[$id] = [
                    'nome' => $row['nome'],
                    'registro' => $row['registro'],
                    'nascimento' => $row['nascimento'],
                    'pontos' => []
                ];
            }
    
            // Adiciona a pontuação com descrição da prova
            $pontuacoes[$id]['pontos'][] = [
                'pontos' => $pontos,
                'prova' => resumirProva($row['prova_descricao']) . " (" . $row['colocacao'] . ")"
            ];
        }
    
        // Seleciona os 4 melhores resultados com descrição
        $resultadoFinal = [];
        foreach ($pontuacoes as $id => $dados) {
            usort($dados['pontos'], fn($a, $b) => $b['pontos'] <=> $a['pontos']);
            $melhores = array_slice($dados['pontos'], 0, 4);
            $total = array_sum(array_column($melhores, 'pontos'));
            $provas = array_column($melhores, 'prova');
    
            $resultadoFinal[] = [
                'atleta_id' => $id,
                'nome' => $dados['nome'],
                'registro' => $dados['registro'],
                'nascimento' => $dados['nascimento'],
                'pontos' => $total,
                'provas' => $provas
            ];
        }
    
        // Ordena por maior pontuação
        usort($resultadoFinal, fn($a, $b) => $b['pontos'] <=> $a['pontos']);
        return $resultadoFinal;
    }    
    

 }