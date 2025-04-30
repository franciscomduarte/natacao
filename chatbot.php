<?php

include_once('utils.php');
carregarEnv(__DIR__ . '/.env');
$apiKey = $_ENV['OPENAI_API_KEY'];

$pergunta = $_POST['mensagem'] ?? 'quem ganhou a prova 200 metros borboleta no torneio centro-oeste?';
$historico = json_decode($_POST['historico'] ?? '[]', true);
$resposta = "Desculpe, não consegui entender sua pergunta.";

if ($pergunta) {
    $documentos = carregar_documentos('regras-natacao/');
    $json = json_decode(file_get_contents('resultado_formatado.json'), true);

    // Tenta encontrar primeiro nos arquivos locais
    $contexto_txt = buscar_trecho_relacionado($pergunta, $documentos, 0.75);
    $contexto_json = buscar_prova_no_json($pergunta, $json, 0.75);

    $mensagem = [];

    if ($contexto_json) {
        // Achou resposta no JSON
        $resposta = $contexto_json;
    } else {
        if ($contexto_txt) {
            $mensagem[] = ['role' => 'system', 'content' =>
                "Você é Ligeirinho, um especialista em natação. Use as regras abaixo para responder de forma clara e objetiva:\n\n$contexto_txt"
            ];
        } else {
            $mensagem[] = ['role' => 'system', 'content' =>
                "Você é Ligeirinho, um especialista em natação. Responda apenas perguntas relacionadas à natação."
            ];
        }

        foreach ($historico as $interacao) {
            $mensagem[] = ['role' => 'user', 'content' => $interacao['pergunta']];
            $mensagem[] = ['role' => 'assistant', 'content' => $interacao['resposta']];
        }

        $mensagem[] = ['role' => 'user', 'content' => $pergunta];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => $mensagem,
            'max_tokens' => 500,
            'temperature' => 0.7
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $resposta = trim($data['choices'][0]['message']['content']);
        }
    }
}

echo json_encode(['resposta' => $resposta]);