<?php
include_once("../../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");
$db = new Conexao();
$pdo = $db->conectar();

$atletaObj = new Atleta($pdo);
$editando = false;
$registro = $nome = $nascimento = "";
$id = $_GET['id'] ?? null;

if ($id) {
    $editando = true;
    $atleta = $atletaObj->buscarPorId($id);
    if ($atleta) {
        $registro = $atleta['registro'];
        $nome = $atleta['nome'];
        $nascimento = $atleta['nascimento'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registro = $_POST['registro'];
    $nome = $_POST['nome'];
    $nascimento = $_POST['nascimento'];

    if ($editando) {
        $atletaObj->atualizar($id, $registro, $nome, $nascimento);
    } else {
        $atletaObj->inserir($registro, $nome, $nascimento);
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
                                <label>Registro</label>
                                <input type="text" name="registro" class="form-control" required value="<?= htmlspecialchars($registro) ?>">
                            </div>
                            <div class="mb-3">
                                <label>Nome</label>
                                <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($nome) ?>">
                            </div>
                            <div class="mb-3">
                                <label>Nascimento (ano)</label>
                                <input type="number" name="nascimento" class="form-control" min="1900" max="<?= date('Y') ?>" required value="<?= htmlspecialchars($nascimento) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="<?php $_SERVER['DOCUMENT_ROOT'] . BASE_URL ?>/private/atleta" class="btn btn-secondary">Voltar</a>
                        </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/footer.php"; ?>
</body>