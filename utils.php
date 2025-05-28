<?php
    function resumirProva($descricaoCompleta) {
        if (preg_match('/(\d+)\s+METROS\s+([A-ZÇ]+)/i', $descricaoCompleta, $matches)) {
            $distancia = $matches[1];
            $estilo = strtoupper(substr($matches[2], 0, 1)); // Primeira letra do estilo
            return "{$distancia} {$estilo}";
        }
        return $descricaoCompleta; // Retorna original se padrão não for encontrado
    }
    
    if (!function_exists('str_ends_with')) {
        function str_ends_with($haystack, $needle) {
            $len = strlen($needle);
            return $len === 0 || substr($haystack, -$len) === $needle;
        }
    }

    function tempoParaSegundos($tempo) {

        if($tempo == '') 
            return 0;
        list($minutos, $resto) = explode(':', $tempo);
        return (int)$minutos * 60 + (float)$resto;
    }

    function segundosParaTempo($segundos) {
        $negativo = $segundos < 0;
        $segundos = abs($segundos);  // trabalha com valor absoluto

        $min = floor($segundos / 60);
        $seg = $segundos - ($min * 60);

        $tempoFormatado = sprintf('%02d:%05.2f', $min, $seg);
        return $negativo ? "-$tempoFormatado" : $tempoFormatado;
    }

    function tempoParaCentesimos($tempo) {
        // Divide minutos e o restante
        list($minutos, $resto) = explode(":", $tempo);
    
        // Divide segundos e centésimos
        list($segundos, $centesimos) = explode(".", $resto);
    
        // Converte tudo para centésimos
        $totalCentesimos = ((int)$minutos * 60 + (int)$segundos) * 100 + (int)$centesimos;
    
        return $totalCentesimos;
    }
    function carregar_documentos($pasta) {
        $docs = [];
        foreach (glob($pasta . "*.txt") as $arquivo) {
            $conteudo = file_get_contents($arquivo);
            $docs[] = $conteudo;
        }
        return $docs;
    }

    function limpar_texto($texto) {
        $texto = strtolower($texto);
        $tokens = explode(' ', $texto);
        $tokens = array_filter($tokens, fn($t) => strlen($t) > 2);
        return array_map('stem_palavra', $tokens);
    }

    function stem_palavra($palavra) {
        $sufixos = ['ções', 'sões', 'mente', 'dade', 'rão', 'ção', 'são', 'ndo', 'nte', 'ar', 'er', 'ir', 'es', 'as', 'os', 'is', 'am', 'ou', 'ei', 'ia', 'al', 'el', 'il'];
        foreach ($sufixos as $sufixo) {
            if (str_ends_with($palavra, $sufixo)) {
                return substr($palavra, 0, -strlen($sufixo));
            }
        }
        return $palavra;
    }

    function buscar_trecho_relacionado($pergunta, $documentos, $limite_similaridade = 0.75) {
        $tokens_pergunta = limpar_texto($pergunta);
        $pontuacoes = [];

        foreach ($documentos as $doc) {
            $tokens_doc = limpar_texto($doc);
            $matchCount = 0;

            foreach ($tokens_pergunta as $token_p) {
                foreach ($tokens_doc as $token_d) {
                    $dist = levenshtein($token_p, $token_d);
                    $maxLen = max(strlen($token_p), strlen($token_d));
                    $sim = ($maxLen > 0) ? 1 - ($dist / $maxLen) : 0;

                    if ($sim > 0.8) {
                        $matchCount++;
                        break;
                    }
                }
            }

            $similaridade = $matchCount / count($tokens_pergunta);
            $pontuacoes[] = ['texto' => $doc, 'score' => $similaridade];
        }

        usort($pontuacoes, fn($a, $b) => $b['score'] <=> $a['score']);
        $melhor = $pontuacoes[0] ?? null;

        return ($melhor && $melhor['score'] >= $limite_similaridade) ? $melhor['texto'] : null;
    }
    function buscar_prova_no_json($pergunta, $json, $limite_similaridade = 0.75) {
        $tokens_pergunta = limpar_texto($pergunta);
        $pontuacoes = [];

        foreach ($json['provas'] as $prova) {
            $bloco = $prova['prova'] . ' ' . $prova['descricao'] . ' ' . $prova['categoria'] . ' ' . $prova['data'];
            $tokens_bloco = limpar_texto($bloco);

            $matchCount = 0;

            foreach ($tokens_pergunta as $token_p) {
                foreach ($tokens_bloco as $token_d) {
                    $dist = levenshtein($token_p, $token_d);
                    $maxLen = max(strlen($token_p), strlen($token_d));
                    $sim = ($maxLen > 0) ? 1 - ($dist / $maxLen) : 0;

                    if ($sim > 0.8) {
                        $matchCount++;
                        break;
                    }
                }
            }

            $similaridade = $matchCount / count($tokens_pergunta);
            $pontuacoes[] = ['prova' => $prova, 'score' => $similaridade];
        }

        usort($pontuacoes, fn($a, $b) => $b['score'] <=> $a['score']);
        $melhor = $pontuacoes[0] ?? null;

        if ($melhor && $melhor['score'] >= $limite_similaridade) {
            $prova = $melhor['prova'];
            $resumo = "Resultado da {$prova['prova']} - {$prova['descricao']} (Categoria: {$prova['categoria']}, Data: {$prova['data']}):\n";
            foreach ($prova['resultados'] as $res) {
                $resumo .= "- {$res['colocacao']}: {$res['atleta']} ({$res['tempo']})\n";
            }
            return $resumo;
        }

        return null;
    }
