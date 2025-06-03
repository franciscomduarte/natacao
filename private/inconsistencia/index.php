<?php
include_once("../../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");
$db = new Conexao();
$pdo = $db->conectar();

$atletaObj = new Inconsistencia($pdo);
$atletas = $atletaObj->listarNaoReconhecidas();
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
                        <h4 class="fw-bold py-3 mb-4">Cadastro de Atletas</h4>
                        <a href="<?php $_SERVER['DOCUMENT_ROOT'] . BASE_URL ?>/private/atleta/form.php" class="btn btn-success mb-3">Novo Atleta</a>

                        <table id="tabelaResultados" class="table table-striped table-bordered nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Campeonato</th>
                                    <th>Prova</th>
                                    <th>Texto</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($atletas as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['id']) ?></td>
                                        <td><?= htmlspecialchars($a['campeonato_id']) ?></td>
                                        <td><?= htmlspecialchars($a['prova_id']) ?></td>
                                        <td><?= htmlspecialchars($a['texto']) ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL ?>/private/inconsistencia/form.php?id=<?= $a['id'] ?>&campeonato_id=<?= $a['campeonato_id'] ?>&prova_id=<?= $a['prova_id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                                            <a href="<?php echo BASE_URL ?>/private/inconsistencia/excluir.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/footer.php"; ?>
</body>