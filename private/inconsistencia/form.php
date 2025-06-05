<?php
include_once("../../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");

$db = new Conexao();
$pdo = $db->conectar();
$resultadoObj = new Inconsistencia($pdo);

$id = $_GET['id'] ?? null;
$resultado = $resultadoObj->buscarPorId($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atleta_id = $_REQUEST['atleta_id'] ?? null;
    if($atleta_id) {
        $r = new Resultado($pdo);

        $r->atleta_id = $_REQUEST['atleta_id'] ?? null;
        $r->prova_id = $_REQUEST['prova_id'] ?? null;
        $r->colocacao = $_REQUEST['colocacao'] ?? null;
        $r->serie = $_REQUEST['serie'] ?? null;
        $r->raia = $_REQUEST['raia'] ?? null;
        $r->atleta = $_REQUEST['atleta'] ?? null;
        $r->registro = $_REQUEST['registro'] ?? null;
        $r->nascimento = $_REQUEST['nascimento'] ?? null;
        $r->entidade = $_REQUEST['entidade'] ?? null;
        $r->tempo = $_REQUEST['tempo'] ?? null;
        $r->tempo_centesimos = tempoParaCentesimos($r->tempo) ?? null;
        $r->pontos = $_REQUEST['pontos'] ?? null;
        $r->indice = $_REQUEST['indice'] ?? null;

        if($r->inserir()) {
            $resultado = $resultadoObj->atualizarSituacao($id);
        }
    } else {
        $a = new Atleta($pdo);

        $a->registro = $_REQUEST['registro'] ?? null;
        $a->nome = $_REQUEST['atleta_nome'] ?? null;
        $a->nascimento = $_REQUEST['nascimento'] ?? null;
        $a->inserir();
    }
    header("Location: index.php");
}
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sidebar.php"); ?>
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"></h4>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Inconsistencia</label>
                                <input type="text" name="inconsistencia" id="inconsistencia" class="form-control" value="<?= $resultado['texto'] ?>">
                            </div>

                            <div class="row">
                                <div class="col-xxl">
                                    <div class="card mb-4">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0">Cadastre o ajuste</h5>
                                            <small class="text-muted float-end"></small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label>Entidade</label>
                                                    <input type="text" name="entidade" id="entidade" class="form-control" >
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Registro do Atleta</label>
                                                    <input type="text" name="registro" id="registro" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Atleta Id</label>
                                                    <input type="number" name="atleta_id" id="atleta_id" value="<?= $resultado['atleta_id'] ?? null ?>" class="form-control">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label>Campeonato Id</label>
                                                    <input type="number" name="campeonato_id" id="campeonato_id" value="<?= $resultado['campeonato_id'] ?>" class="form-control" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label>Prova Id</label>
                                                    <input type="number" name="prova_id" id="prova_id" value="<?= $resultado['prova_id'] ?>" class="form-control" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label>Colocação</label>
                                                    <input type="text" name="colocacao" id="colocacao" class="form-control" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label>Série</label>
                                                    <input type="text" name="serie" id="serie" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Raia</label>
                                                    <input type="text" name="raia" id="raia" class="form-control" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label>Nome do Atleta</label>
                                                    <input type="text" name="atleta_nome" id="atleta_nome" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Ano de Nascimento</label>
                                                    <input type="number" name="nascimento" id="nascimento" class="form-control" min="1900" max="<?= date('Y') ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Tempo</label>
                                                    <input type="text" name="tempo" id="tempo" class="form-control" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label>Pontos</label>
                                                    <input type="number" name="pontos" id="pontos" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Indíce</label>
                                                    <input type="number" name="indice" id="indice" class="form-control" min="0" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="<?php echo BASE_URL ?>/private/inconsistencia" class="btn btn-secondary">Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/footer.php"; ?>

    <!-- Script de busca do atleta -->
    <script>
    $(document).ready(function () {
    var inconsistencia = $("#inconsistencia").val(); 
    console.log("Inconsistência:", inconsistencia);

    // Inicializa variáveis
    var colocacao = '';
    var serie = '';
    var raia = '';
    var registro = '';
    var tempo = '';
    var pontos = '';
    var indice = '';

    // Divide os primeiros dados (colocacao, serie, raia)
    var partes = inconsistencia.split(' ');
    if (partes.length > 0) {
        colocacao = partes[0] || '';
        serie = partes[1] || '';
        raia = partes[2] || '';
    }

    // Regex para capturar registro (ex: número de 6 dígitos)
    var regexRegistro = /\bV?(\d{6})\b/;
    var matchRegistro = inconsistencia.match(regexRegistro);
    if (matchRegistro && matchRegistro[1]) {
        registro = matchRegistro[1];
    }

    // Regex para tempo: ex: "01:50 34" -> "01:50.34"
    const match = inconsistencia.match(/(\d{2}:\d{2}) (\d{2})/);
    if (match) {
        tempo = `${match[1]}.${match[2]}`;
    }

    // Regex para pontos e índice: ex: "5,62 123"
    var regexPontosIndice = /(\d{1,2},\d{2})\s(\d{3})$/;
    var matchPontosIndice = inconsistencia.match(regexPontosIndice);
    if (matchPontosIndice && matchPontosIndice[1] && matchPontosIndice[2]) {
        pontos = Math.floor(matchPontosIndice[1].replace(",", "."));
        indice = matchPontosIndice[2];
    }

    // Preenche os campos do formulário
    $("#colocacao").val(colocacao);
    $("#serie").val(serie);
    $("#raia").val(raia);
    $("#registro").val(registro);
    $("#tempo").val(tempo);
    $("#pontos").val(pontos);
    $("#indice").val(indice);

    // Verifica se tem registro antes de buscar
    if (registro.length === 0) {
        console.warn("Registro não encontrado na inconsistência.");
        return;
    }

    console.log("Buscando atleta para registro:", registro);

    // Faz a chamada AJAX
    $.get("<?php echo BASE_URL ?>/private/inconsistencia/buscar_atleta.php", { registro: registro }, function (data) {
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
    });
});

    </script>
</body>