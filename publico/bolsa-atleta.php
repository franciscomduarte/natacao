<?php
include_once("../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");

$db = new Conexao();
$pdo = $db->conectar();
$obj = new Resultado($pdo);

$ano = $_GET['ano'] ?? date('Y');
$dados = $obj->calcularBolsaAtletaEstudantil($ano);
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
                    <h4 class="fw-bold py-3 mb-4">Ranking Bolsa Atleta Estudantil - <?= $ano ?></h4>

                    <form method="GET" class="mb-4">
                        <label>Selecione o ano:</label>
                        <select name="ano" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
                            <?php for ($a = date('Y'); $a >= 2025; $a--): ?>
                                <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>

                    <table id="tabelaResultados" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Registro</th>
                                <th>Ano Nasc.</th>
                                <th>CO - 1º Semestre</th>
                                <th>Brasiliense - Inverno</th>
                                <th>Bra - Inverno</th>
                                <th>CO - 2º Semestre</th>
                                <th>Brasiliense - Verão</th>
                                <th>Bra - Verão</th>
                                <th>Total Pontos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            foreach ($dados as $atleta):
                                $provas = "";
                                foreach ($atleta['provas'] as $prova):
                                    if($provas == ""){
                                        $provas = $prova;
                                    } else {
                                        $provas .= " <br> " . $prova;
                                    }
                                endforeach;
                            ?>
                            <tr>
                                <td><?= $rank++ ?></td>
                                <td><?= htmlspecialchars($atleta['nome']) ?></td>
                                <td><?= htmlspecialchars($atleta['registro']) ?></td>
                                <td><?= htmlspecialchars($atleta['nascimento']) ?></td>
                                <td><?= $provas ?></td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
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
</body>
