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

?>