<?php
include($_SERVER['DOCUMENT_ROOT'] . "/natacao/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . "/natacao/head.php");

$db = new Conexao();
$pdo = $db->conectar();
$resultadoObj = new Inconsistencia($pdo);

$editando = false;
$id = $_GET['id'] ?? null;
$dadosResultado = [
    'prova_id' => '',
    'atleta_id' => '',
    'colocacao' => '',
    'serie' => '',
    'raia' => '',
    'atleta' => '',
    'registro' => '',
    'nascimento' => '',
    'entidade' => '',
    'tempo' => '',
    'tempo_centesimos' => '',
    'pontos' => '',
    'indice' => '',
    'linha_bruta' => '',
    'sexo' => '',
    'prova' => ''
];

$dadosAtleta = [
    'registro' => '',
    'nome' => '',
    'nascimento' => ''
];

$resultado = $resultadoObj->buscarPorId($id);
if ($resultado) {
    $dadosAtleta = array_merge($dadosAtleta, $resultado);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($dadosAtleta as $campo => $valor) {
        $dadosAtleta[$campo] = $_POST[$campo] ?? '';
    }
    $resultadoObj->inserir($dadosResultado);
    header("Location: index.php");
    exit;
}
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include($_SERVER['DOCUMENT_ROOT'] . "/natacao/sidebar.php"); ?>
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><?= $editando ? 'Editar' : 'Novo' ?> Resultado</h4>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Registro do Atleta</label>
                                <input type="text" name="registro" id="registro" class="form-control" required>
                            </div>
                            <input type="hidden" name="atleta_id" id="atleta_id">

                            <div class="mb-3">
                                <label>Nome do Atleta</label>
                                <input type="text" name="atleta" id="atleta_nome" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Ano de Nascimento</label>
                                <input type="number" name="nascimento" id="nascimento" class="form-control" min="1900" max="<?= date('Y') ?>" required>
                            </div>

                            <?php foreach ($dadosResultado as $campo => $valor): ?>
                                <div class="mb-3">
                                    <label><?= ucfirst(str_replace('_', ' ', $campo)) ?></label>
                                    <input
                                        type="<?= is_numeric($valor) ? 'number' : 'text' ?>"
                                        name="<?= $campo ?>"
                                        class="form-control"
                                        value="<?= htmlspecialchars($valor) ?>"
                                        <?= ($campo === 'tempo') ? 'pattern="\d{1,2}:\d{2}\.\d{2}" placeholder="mm:ss.cc"' : '' ?>
                                    >
                                </div>
                            <?php endforeach; ?>

                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="/natacao/private/resultado" class="btn btn-secondary">Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/natacao/footer.php"; ?>

    <!-- Script de busca do atleta -->
    <script>
    $(document).ready(function () {
        $("#registro").on("blur", function () {
            const registro = $(this).val().trim();
            if (registro.length === 0) return;

            $.get("/natacao/private/inconsistencia/buscar_atleta.php", { registro: registro }, function (data) {
                console.log("Resposta recebida:", data);
                if (data && data.id) {
                    $("#atleta_id").val(data.id);
                    $("#atleta_nome").val(data.nome);
                    $("#nascimento").val(data.nascimento);
                } else {
                    $("#atleta_id").val("");
                    $("#atleta_nome").val("");
                    $("#nascimento").val("");
                }
            }, "json");
        });
    });
    </script>
</body>