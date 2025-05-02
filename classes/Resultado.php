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
            AND c.chave IN (" . implode(',', array_fill(0, count($chavesValidas), '?')) . ")
            AND c.ano = ?
            AND a.situacao = 'FEDERADO'
            AND a.nascimento >= YEAR(CURDATE()) - 16
            AND r.colocacao BETWEEN 1 AND 5
        ";
        $params = array_merge($chavesValidas, [$ano]);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
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
            $descricao = Resultado::resumirProva($row['prova_descricao']) . " (" . $row['colocacao'] . ")";

            // Verifica se já existe no array
            $jaExiste = false;
            if (isset($pontuacoes[$id]['pontos'])) {
                foreach ($pontuacoes[$id]['pontos'] as $p) {
                    if ($p['prova'] === $descricao) {
                        $jaExiste = true;
                        break;
                    }
                }
            }
            
            if (!$jaExiste) {
                $pontuacoes[$id]['pontos'][] = [
                    'pontos' => $pontos,
                    'prova' => $descricao
                ];
            }
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



    function calcularBolsaAtletaEstadual($ano): array {
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
            'brasileiro-inverno',
            'trofeu-brasil'
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
            AND c.chave IN (" . implode(',', array_fill(0, count($chavesValidas), '?')) . ")
            AND c.ano = ?
            AND a.situacao = 'FEDERADO'
            AND a.nascimento <= YEAR(CURDATE()) - 14
            AND r.colocacao BETWEEN 1 AND 5
        ";
        $params = array_merge($chavesValidas, [$ano]);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
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
            $descricao = Resultado::resumirProva($row['prova_descricao']) . " (" . $row['colocacao'] . ")";

            // Verifica se já existe no array
            $jaExiste = false;
            if (isset($pontuacoes[$id]['pontos'])) {
                foreach ($pontuacoes[$id]['pontos'] as $p) {
                    if ($p['prova'] === $descricao) {
                        $jaExiste = true;
                        break;
                    }
                }
            }
            
            if (!$jaExiste) {
                $pontuacoes[$id]['pontos'][] = [
                    'pontos' => $pontos,
                    'prova' => $descricao
                ];
            }
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

    function calcularBolsaAtletaNacional($ano): array {
        $pontuacao = [
            1 => 10,
            2 => 8,
            3 => 6,
            4 => 4,
            5 => 2
        ];
    
        // Campeonatos válidos para estudantil
        $chavesValidas = [
            'brasileiro-verao',
            'brasileiro-inverno',
            'trofeu-brasil',
            'jose-finkel'
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
            AND (c.chave IN (" . implode(',', array_fill(0, count($chavesValidas), '?')) . ") or 1 = 1)
            AND c.ano = ?
            AND a.situacao = 'FEDERADO'
            AND a.nascimento <= YEAR(CURDATE()) - 14
            AND r.colocacao BETWEEN 1 AND 5
        ";
        $params = array_merge($chavesValidas, [$ano]);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
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
            $descricao = Resultado::resumirProva($row['prova_descricao']) . " (" . $row['colocacao'] . ")";

            // Verifica se já existe no array
            $jaExiste = false;
            if (isset($pontuacoes[$id]['pontos'])) {
                foreach ($pontuacoes[$id]['pontos'] as $p) {
                    if ($p['prova'] === $descricao) {
                        $jaExiste = true;
                        break;
                    }
                }
            }
            
            if (!$jaExiste) {
                $pontuacoes[$id]['pontos'][] = [
                    'pontos' => $pontos,
                    'prova' => $descricao
                ];
            }
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

    static function  resumirProva($descricaoCompleta) {
        if (preg_match('/(\d+)\s+METROS\s+([A-ZÇ]+)/i', $descricaoCompleta, $matches)) {
            $distancia = $matches[1];
            $estilo = strtoupper(substr($matches[2], 0, 1)); // Primeira letra do estilo
            return "{$distancia} {$estilo}";
        }
        return $descricaoCompleta; // Retorna original se padrão não for encontrado
    }
    
    
 }