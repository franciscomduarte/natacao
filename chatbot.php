<?php

include_once ('utils.php');
// Substitua pela sua chave da OpenAI

carregarEnv(__DIR__ . '/.env');
$apiKey = $_ENV['OPENAI_API_KEY'];

$pergunta = $_POST['mensagem'] ?? '';
$historico = json_decode($_POST['historico'] ?? '[]', true);
$resposta = "Desculpe, não consegui entender sua pergunta.";

if ($pergunta) {
  // Carrega documentos de regras
  $documentos = carregar_documentos('chat/documentos/');

  // Busca o trecho mais relevante com limiar
  $contexto = buscar_trecho_relacionado($pergunta, $documentos, 0.75);

  // Monta mensagens para a API da OpenAI
  $mensagem = [];

  if ($contexto) {
    $mensagem[] = ['role' => 'system', 'content' =>
      "Você é Ligeirinho, um especialista em natação. Responda com base nas regras abaixo, de forma clara e objetiva:\n\n$contexto"
    ];
  } else {
    $mensagem[] = ['role' => 'system', 'content' =>
      "Você é Ligeirinho, um especialista em natação. Só responda perguntas relacionadas à natação. Caso a pergunta não tenha relação com natação, diga que só responde sobre esse tema."
    ];
  }

  // Adiciona o histórico anterior ao prompt
  foreach ($historico as $interacao) {
    $mensagem[] = ['role' => 'user', 'content' => $interacao['pergunta']];
    $mensagem[] = ['role' => 'assistant', 'content' => $interacao['resposta']];
  }

  // Adiciona a nova pergunta
  $mensagem[] = ['role' => 'user', 'content' => $pergunta];

  // Chamada à API da OpenAI
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

echo json_encode(['resposta' => $resposta]);