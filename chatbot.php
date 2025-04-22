<?php

include_once ('utils.php');
// Substitua pela sua chave da OpenAI

carregarEnv(__DIR__ . '/.env');
$apiKey = $_ENV['OPENAI_API_KEY'];

// Verifica se a pergunta foi enviada via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pergunta'])) {
    $pergunta = trim($_POST['pergunta']);

    // Cria a requisição para o modelo da OpenAI
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Você é Ligeirinho, um especialista em natação. Responda de forma breve e amigável.'],
            ['role' => 'user', 'content' => $pergunta]
        ],
        'max_tokens' => 200,
        'temperature' => 0.7
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro de requisição: ' . curl_error($ch);
    } else {
        $result = json_decode($response, true);
        echo $result['choices'][0]['message']['content'] ?? 'Sem resposta.';
    }

    curl_close($ch);
} else {
    echo 'Pergunta não recebida.';
}
?>
