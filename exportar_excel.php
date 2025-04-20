<?php
require 'autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Configura conexão
$db = new Conexao();
$pdo = $db->conectar();
$resultadoObj = new Resultado($pdo);

// Filtros recebidos via POST (AJAX)
$filtros = [
    'campeonatos' => $_POST['campeonatos'] ?? [],
    'entidades' => $_POST['entidades'] ?? [],
    'ano' => $_POST['ano'] ?? '',
    'piscina' => $_POST['piscina'] ?? ''
];

// Consulta os resultados
$resultados = $resultadoObj->filtrarResultados($filtros);

// Criação do Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Cabeçalhos
$sheet->fromArray([
    'Campeonato', 'Data', 'Prova', 'Atleta', 'Entidade', 'Tempo', 'Colocacao', 'Pontos'
], NULL, 'A1');

// Dados
$row = 2;
foreach ($resultados as $res) {
    $sheet->fromArray([
        $res['campeonato'],
        $res['data'],
        $res['descricao'],
        $res['atleta'],
        $res['entidade'],
        $res['tempo'],
        $res['colocacao'],
        $res['pontos']
    ], NULL, "A{$row}");
    $row++;
}

// Retorno como download
$filename = "resultados_exportados.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
