<?php
include_once("../../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");
$db = new Conexao();
$pdo = $db->conectar();

$obj = new Campeonato($pdo);
$editando = false;
$nome = $nascimento = $cidade = $piscina = $realizacao = $ano = $chave = "";
$id = $_GET['id'] ?? null;

if ($id) {
    $editando = true;
    $campeonato= $obj->buscarPorId($id);
    if ($campeonato) {
        $nome = $campeonato['nome'];
        $cidade = $campeonato['cidade'];
        $piscina = $campeonato['piscina'];
        $realizacao = $campeonato['realizacao'];
        $ano = $campeonato['ano'];
        $chave = $campeonato['chave'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cidade = $_POST['cidade'];
    $piscina = $_POST['piscina'];
    $realizacao = $_POST['realizacao'];
    $ano = $_POST['ano'];
    $chave = $_POST['chave'];

    if ($editando) {
        $obj->atualizar($id, $nome, $cidade, $piscina, $realizacao, $ano, $chave);
    } else {
        $obj->inserir($nome, $cidade, $piscina, $realizacao, $ano, $chave);
    }
    header("Location: index.php");
    exit;
}
?>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sidebar.php"); ?>
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><?= $editando ? 'Editar' : 'Novo' ?> Atleta</h4>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Nome</label>
                                <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($nome) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Cidade</label>
                                <input type="text" name="cidade" class="form-control" required value="<?= htmlspecialchars($cidade) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Piscina</label>
                                <input type="text" name="piscina" class="form-control" required value="<?= htmlspecialchars($piscina) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Realização</label>
                                <input type="text" name="realizacao" class="form-control" required value="<?= htmlspecialchars($realizacao) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Ano</label>
                                <input type="text" name="ano" class="form-control" required value="<?= htmlspecialchars($ano) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Chave</label>
                                <input type="text" name="chave" class="form-control" value="<?= htmlspecialchars($chave) ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="<?php echo BASE_URL ?>/private/campeonato" class="btn btn-secondary">Voltar</a>
                        </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/footer.php"; ?>
</body>