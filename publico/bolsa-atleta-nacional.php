<?php
include_once("../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");

$db = new Conexao();
$pdo = $db->conectar();
$obj = new BolsaAtletaCalculator($pdo);

$ano = $_GET['ano'] ?? date('Y');
$dadosBraInverno = $obj->calcularBolsaAtleta($ano, 'brasileiro-inverno', 'nacional', []);
$dadosBraVerao = $obj->calcularBolsaAtleta($ano, 'brasileiro-verao', 'nacional', []);
$dadosTrofeuBrasil = $obj->calcularBolsaAtleta($ano, 'trofeu-brasil', 'nacional', []);
$dadosJoseFinkel = $obj->calcularBolsaAtleta($ano, 'jose-finkel', 'nacional', []);


?>
<style>
    .container-resultados {
        padding: 20px;
    }

    .dt-buttons {
        margin-bottom: 15px;
    }

    #tabela_resultados {
        margin-top: 10px;
    }
</style>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sidebar.php"); ?>
        <div class="layout-page">
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="fw-bold py-3 mb-4">Ranking Bolsa Atleta Estadual - <?= $ano ?></h4><h6 style="color: red">(obs: Caso encontre alguma incosistência, <a mailto="francisco.m.duarte@gmail.com">avise-nos</a>)</h6>

                    <form method="GET" class="mb-4">
                        <label>Selecione o ano:</label>
                        <select name="ano" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
                            <?php for ($a = date('Y'); $a >= 2025; $a--): ?>
                                <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>

                    <table id="tabelaBolsa" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Registro</th>
                                <th>Ano Nasc.</th>
                                <th>Troféu Brasil</th>
                                <th>Bra - Inverno</th>
                                <th>Bra - Verão</th>
                                <th>José Finkel</th>
                                <th>Total Pontos</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            // Função auxiliar para consolidar os dados
                            function mergeDadosPorAtleta($dados, &$ranking, $coluna) {
                                foreach ($dados as $atleta) {
                                    $registro = $atleta['registro'];
                                    if (!isset($ranking[$registro])) {
                                        $ranking[$registro] = [
                                            'nome' => $atleta['nome'],
                                            'registro' => $registro,
                                            'nascimento' => $atleta['nascimento'],
                                            'provas' => [
                                                'trofeu_brasil' => '-',
                                                'bra_inverno' => '-',
                                                'bra_verao' => '-',
                                                'jose_finkel' => '-'
                                            ],
                                            'pontos_por_competicao' => [
                                                'trofeu_brasil' => 0,
                                                'bra_inverno' => 0,
                                                'bra_verao' => 0,
                                                'jose_finkel' => 0
                                            ],
                                            'pontos' => 0
                                        ];
                                    }
                            
                                    $provas = implode("<br>", $atleta['provas'] ?? []);
                                    $ranking[$registro]['provas'][$coluna] = $provas;
                                    $ranking[$registro]['pontos_por_competicao'][$coluna] += $atleta['pontos'];
                                }
                            }

                            // Consolidando todos os dados
                            $ranking = [];

                            mergeDadosPorAtleta($dadosTrofeuBrasil, $ranking, 'trofeu_brasil');
                            mergeDadosPorAtleta($dadosBraInverno, $ranking, 'bra_inverno');
                            mergeDadosPorAtleta($dadosBraVerao, $ranking, 'bra_verao');
                            mergeDadosPorAtleta($dadosJoseFinkel, $ranking, 'jose_finkel');
                            

                            foreach ($ranking as &$atleta) {
                                $atleta['pontos'] = array_sum($atleta['pontos_por_competicao']);
                            }
                            unset($atleta);

                            foreach ($ranking as $atleta):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($atleta['nome']) ?></td>
                                <td><?= htmlspecialchars($atleta['registro']) ?></td>
                                <td><?= htmlspecialchars($atleta['nascimento']) ?></td>
                                <td><?= $atleta['provas']['trofeu_brasil'] ?></td>
                                <td><?= $atleta['provas']['bra_inverno'] ?></td>
                                <td><?= $atleta['provas']['bra_verao'] ?></td>
                                <td><?= $atleta['provas']['jose_finkel'] ?></td>
                                <td><strong><?= number_format($atleta['pontos'], 2) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>

                    <?php if (empty($dados)): ?>
                        <div class="alert alert-warning mt-4">Nenhum dado encontrado para o ano selecionado.</div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>



<?php include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/footer.php"); ?>

<script>

    $(document).ready(function() {
        $('#tabelaBolsa').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            dom: 'Bfrtip',
            order: [[10, 'desc']], // <- ordena pela coluna Total Pontos (11ª coluna), decrescente
            buttons: [
                { extend: 'copy', className: 'btn btn-outline-secondary' },
                { extend: 'csv', className: 'btn btn-outline-secondary' },
                { extend: 'excel', className: 'btn btn-success' },
                { extend: 'print', className: 'btn btn-outline-secondary' }
            ],
            language: {
                search: "Pesquisar:",
                lengthMenu: "Mostrar _MENU_ registros por página",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                paginate: {
                    first: "Primeiro",
                    last: "Último",
                    next: "Próximo",
                    previous: "Anterior"
                },
                buttons: {
                    copy: "Copiar",
                    csv: "CSV",
                    excel: "Excel",
                    print: "Imprimir"
                }
            },
            columnDefs: [
                { targets: [2, 3], className: 'text-wrap' },
                { targets: '_all', className: 'align-middle' }
            ]
        });
    });


</script>

</body>
