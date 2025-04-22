<?php

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
    
    function vetorizar($texto) {
        $texto = strtolower(preg_replace("/[^a-z0-9 ]/", "", $texto));
        $palavras = explode(" ", $texto);
        $vetor = [];
        foreach ($palavras as $p) {
            if (!empty($p)) {
                $vetor[$p] = ($vetor[$p] ?? 0) + 1;
            }
        }
        return $vetor;
    }
    
    function cosseno($v1, $v2) {
        $dot = 0;
        $norma1 = 0;
        $norma2 = 0;
    
        $todasChaves = array_unique(array_merge(array_keys($v1), array_keys($v2)));
    
        foreach ($todasChaves as $chave) {
            $a = $v1[$chave] ?? 0;
            $b = $v2[$chave] ?? 0;
            $dot += $a * $b;
            $norma1 += $a * $a;
            $norma2 += $b * $b;
        }
    
        return $norma1 && $norma2 ? $dot / (sqrt($norma1) * sqrt($norma2)) : 0;
    }
    
    function buscarTrechoMaisProximo($pergunta, $documentos) {
        $vetorPergunta = vetorizar($pergunta);
        $melhorTrecho = "";
        $melhorScore = -1;
    
        foreach ($documentos as $trecho) {
            $vetorTrecho = vetorizar($trecho);
            $score = cosseno($vetorPergunta, $vetorTrecho);
            if ($score > $melhorScore) {
                $melhorScore = $score;
                $melhorTrecho = $trecho;
            }
        }
    
        return $melhorTrecho;
    }

    function carregarEnv($caminho) {
        if (!file_exists($caminho)) return;
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhas as $linha) {
            if (strpos(trim($linha), '#') === 0) continue;
            list($chave, $valor) = explode('=', $linha, 2);
            $_ENV[trim($chave)] = trim($valor);
        }
    }
?>
    