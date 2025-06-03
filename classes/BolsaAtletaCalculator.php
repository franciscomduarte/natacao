<?php

// Assuming Resultado class and resumirProva method exists as used in your original code.
// If not, you might need to implement or adjust this part.
// Example placeholder:
/*
static class Resultado {
    public static function resumirProva($descricaoProva) {
        // Placeholder: implement your logic to summarize event description
        // e.g., remove unnecessary details, standardize format
        return $descricaoProva; 
    }
}
*/

class BolsaAtletaCalculator {
    private $conn; // Assuming $this->conn is a PDO connection or similar

    // Constructor to inject database connection or other dependencies
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public static $pontuacaoBrasiliense = [
        1 => 3, 2 => 2, 3 => 1
    ];

    public static $pontuacaoBrasilienseAbsoluto = [
        1 => 6, 2 => 4, 3 => 2
    ];

    public static $pontuacaoCentroOeste = [
        1 => 10, 2 => 8, 3 => 6, 4 => 4, 5 => 2
    ];

    public static $pontuacaoBrasileiro = [
        1 => 30, 2 => 24, 3 => 20, 4 => 18, 5 => 16, 6 => 14, 7 => 12, 8 => 10
    ];

    public static $pontuacaoTrofeusFinalA = [
        1 => 90, 2 => 72, 3 => 60, 4 => 54, 5 => 48, 6 => 42, 7 => 36, 8 => 30
    ];

    public static $pontuacaoTrofeusFinalB = [
        1 => 24, 2 => 20, 3 => 18, 4 => 16, 5 => 14, 6 => 12, 7 => 10, 8 => 8
    ];

    public static $chavesValidas = [
        'centro-oeste-1-sem', 'centro-oeste-2-sem', 'brasiliense-verao','brasiliense-inverno',
        'brasileiro-verao','brasileiro-inverno','trofeu-brasil','jose-finkel'
    ];
    /**
     * Defines the rules (PESO, multipliers) for National and International events.
     * Keys should match the output of getCampeonatoRegraKey.
     */
    private function getRegrasNacionalInternacional(): array {
        return [
            // NACIONAL - Based on image 2
            'TROFÉU MARIA LENK E TROFÉU JOSÉ FINKEL' => [
                'peso' => 3,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1] // 1st, 2nd, 3rd, 4th place
            ],
            'BRASILEIROS DE CATEGORIAS' => [ // (INFANTIL – JUVENIL – JÚNIOR E SÊNIOR)
                'peso' => 1,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],

            // INTERNACIONAL - Based on image 1
            'CAMPEONATO MUNDIAL ABSOLUTO (FINA)' => [
                'peso' => 256,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],
            'JOGOS PAN-AMERICANOS (ODEPA)' => [
                'peso' => 128,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],
            'CAMPEONATO MUNDIAL JÚNIOR (FINA)' => [
                'peso' => 64,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],
            // Combined from image: "SUL-AMERICANO ABSOLUTO + JOGOS DESPORTIVOS SUL-AMERICANOS (CONSANAT)"
            'SUL-AMERICANO ABSOLUTO E JOGOS DESPORTIVOS SUL-AMERICANOS (CONSANAT)' => [
                'peso' => 32,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],
            'CAMPEONATO SUL-AMERICANO JUVENIL (CONSANAT)' => [
                'peso' => 16,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],
            'COPA DO MUNDO DE NATAÇÃO (FINA)' => [
                'peso' => 8,
                'multiplicadores' => [1 => 6, 2 => 4, 3 => 2, 4 => 1]
            ],
        ];
    }

    /**
     * Maps the input $campeonato parameter to the corresponding key in getRegrasNacionalInternacional.
     * You'll need to expand this map based on the exact $campeonato strings you use.
     */
    private function getCampeonatoRegraKey(string $campeonatoParam, string $categoriaBolsa): ?string {
        $lowerCampeonatoParam = strtolower($campeonatoParam);
        
        // Define mappings from your $campeonato parameter values to the rule keys
        $map = [];
        if ($categoriaBolsa == 'nacional') {
            $map = [
                // Keys are expected $campeonato inputs (lowercase)
                'trofeu-maria-lenk' => 'TROFÉU MARIA LENK E TROFÉU JOSÉ FINKEL',
                'jose-finkel' => 'TROFÉU MARIA LENK E TROFÉU JOSÉ FINKEL',
                'trofeu-brasil' => 'TROFÉU MARIA LENK E TROFÉU JOSÉ FINKEL', // Assuming Trofeu Brasil is Maria Lenk
                'brasileiros-de-categorias' => 'BRASILEIROS DE CATEGORIAS',
                'brasileiro-verao' => 'BRASILEIROS DE CATEGORIAS', // Example, adjust as needed
                'brasileiro-inverno' => 'BRASILEIROS DE CATEGORIAS', // Example, adjust as needed
                // Add all relevant $campeonato strings that map to 'BRASILEIROS DE CATEGORIAS'
            ];
        } elseif ($categoriaBolsa == 'internacional') {
            $map = [
                // Keys are expected $campeonato inputs for international events (lowercase)
                'mundial-absoluto-fina' => 'CAMPEONATO MUNDIAL ABSOLUTO (FINA)',
                'jogos-panamericanos' => 'JOGOS PAN-AMERICANOS (ODEPA)', // Example mapping
                'mundial-junior-fina' => 'CAMPEONATO MUNDIAL JÚNIOR (FINA)',
                'sulamericano-absoluto-jogos-sulamericanos' => 'SUL-AMERICANO ABSOLUTO E JOGOS DESPORTIVOS SUL-AMERICANOS (CONSANAT)',
                'sulamericano-juvenil' => 'CAMPEONATO SUL-AMERICANO JUVENIL (CONSANAT)',
                'copa-do-mundo-natacao' => 'COPA DO MUNDO DE NATAÇÃO (FINA)',
                // Add more mappings as required for your $campeonato parameter values
            ];
        }
        return $map[$lowerCampeonatoParam] ?? null;
    }

    static function  resumirProva($descricaoCompleta) {
        if (preg_match('/(\d+)\s+METROS\s+([A-ZÇ]+)/i', $descricaoCompleta, $matches)) {
            $distancia = $matches[1];
            $estilo = strtoupper(substr($matches[2], 0, 1)); // Primeira letra do estilo
            return "{$distancia} {$estilo}";
        }
        return $descricaoCompleta; // Retorna original se padrão não for encontrado
    }

    public function calcularBolsaAtleta($ano, $campeonato, $categoriaBolsa = null, $pontuacaoCampeonato = []): array {
        // Consulta resultados válidos (Your existing SQL query logic)
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
            AND a.situacao = 'FEDERADO'
            AND c.ano = ?
            AND c.chave = ? "; // Assuming $campeonato maps to c.chave

        // Placement filters based on championship type (from your original code)
        if ($campeonato == 'brasileiro-verao' || $campeonato == 'brasileiro-inverno' || $campeonato == 'trofeu-brasil' || $campeonato == 'jose-finkel'){
            $sql .= " AND r.colocacao BETWEEN 1 AND 8 ";
        } else {
            $sql .= " AND r.colocacao BETWEEN 1 AND 5 ";
        }

        // Age filters (from your original code)
        if ($categoriaBolsa == 'estudantil') {
            $sql .= " AND a.nascimento >= YEAR(CURDATE()) - 16 ";
        } else {
            // For Nacional, Internacional, Estadual (non-estudantil)
            // This implies athletes are generally 14 or older.
            // Specific age categories for events like "Mundial Júnior" are usually handled by the event's own rules.
            $sql .= " AND a.nascimento <= YEAR(CURDATE()) - 14 ";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $ano, PDO::PARAM_INT);
        $stmt->bindParam(2, $campeonato, PDO::PARAM_STR);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $resultadoFinal = [];

        if ($categoriaBolsa == 'nacional' || $categoriaBolsa == 'internacional') {
            $regrasMaster = $this->getRegrasNacionalInternacional();
            $regraKey = $this->getCampeonatoRegraKey($campeonato, $categoriaBolsa);

            if (!$regraKey || !isset($regrasMaster[$regraKey])) {
                // Log error or handle: Rules not defined for this championship/category combination
                error_log("BolsaAtletaCalculator: Regras não encontradas para campeonato '$campeonato', categoria '$categoriaBolsa'.");
                return []; 
            }

            $regrasCampeonatoEspecifico = $regrasMaster[$regraKey];
            $peso = $regrasCampeonatoEspecifico['peso'];
            $multiplicadores = $regrasCampeonatoEspecifico['multiplicadores'];

            $atletasDadosCampeonato = [];
            foreach ($resultados as $row) {
                $id = $row['atleta_id'];
                if (!isset($atletasDadosCampeonato[$id])) {
                    $atletasDadosCampeonato[$id] = [
                        'nome' => $row['nome'],
                        'registro' => $row['registro'],
                        'nascimento' => $row['nascimento'],
                        'colocacoes_count' => array_fill_keys(array_keys($multiplicadores), 0), // e.g., [1=>0, 2=>0, 3=>0, 4=>0]
                        'provas_detalhes' => []
                    ];
                }
                
                $colocacao = (int)$row['colocacao'];
                // Only count placements that have a defined multiplier (typically 1st-4th)
                if (isset($multiplicadores[$colocacao])) {
                    $atletasDadosCampeonato[$id]['colocacoes_count'][$colocacao]++;
                    
                    $descricaoProva = BolsaAtletaCalculator::resumirProva($row['prova_descricao']) 
                            . " (" . $row['colocacao'] . " lugar)";
;
                    
                    // Avoid duplicate event descriptions if the same result appears multiple times (though unlikely for final placements)
                    if (!in_array($descricaoProva, $atletasDadosCampeonato[$id]['provas_detalhes'])) {
                        $atletasDadosCampeonato[$id]['provas_detalhes'][] = $descricaoProva;
                    }
                }
            }

            foreach ($atletasDadosCampeonato as $id => $dadosAtleta) {
                $somaPontosBase = 0;
                foreach ($dadosAtleta['colocacoes_count'] as $coloc => $nConquistas) {
                    if ($nConquistas > 0 && isset($multiplicadores[$coloc])) {
                        $somaPontosBase += ($multiplicadores[$coloc] * $nConquistas);
                    }
                }
                
                if ($somaPontosBase > 0) { // Only proceed if there are base points
                    $totalPontosAtleta = $somaPontosBase * $peso;

                    $resultadoFinal[] = [
                        'atleta_id' => $id,
                        'nome' => $dadosAtleta['nome'],
                        'registro' => $dadosAtleta['registro'],
                        'nascimento' => $dadosAtleta['nascimento'],
                        'pontos' => $totalPontosAtleta,
                        'provas' => $dadosAtleta['provas_detalhes']
                    ];
                }
            }

        } else { // Logic for Estudantil, Estadual (your existing logic)
            $pontuacoes = [];
            foreach ($resultados as $row) {
                $id = $row['atleta_id'];
                // Points based on $pontuacaoCampeonato array for Estudantil/Estadual
                $pontos = $pontuacaoCampeonato[(int)$row['colocacao']] ?? 0;
            
                if (!isset($pontuacoes[$id])) {
                    $pontuacoes[$id] = [
                        'nome' => $row['nome'],
                        'registro' => $row['registro'],
                        'nascimento' => $row['nascimento'],
                        'pontos' => [] // Stores individual achievements {pontos: X, prova: "desc"}
                    ];
                }
            
                $descricao = BolsaAtletaCalculator::resumirProva($row['prova_descricao']) 
                              . " (" . $row['colocacao'] . " lugar)";

                // Check if this specific achievement (event + placement) already exists
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
        
            // Select the top 4 results for Estudantil/Estadual and sum points
            foreach ($pontuacoes as $id => $dados) {
                usort($dados['pontos'], fn($a, $b) => $b['pontos'] <=> $a['pontos']); // Sort achievements by points
                $melhores = array_slice($dados['pontos'], 0, 4); // Take top 4
                $total = array_sum(array_column($melhores, 'pontos'));
                $provas = array_column($melhores, 'prova');
        
                if ($total > 0) { // Only include athletes with points
                    $resultadoFinal[] = [
                        'atleta_id' => $id,
                        'nome' => $dados['nome'],
                        'registro' => $dados['registro'],
                        'nascimento' => $dados['nascimento'],
                        'pontos' => $total,
                        'provas' => $provas
                    ];
                }
            }
        }
    
        // Sort all athletes by final score
        usort($resultadoFinal, fn($a, $b) => $b['pontos'] <=> $a['pontos']);
        return $resultadoFinal;
    }
}

?>