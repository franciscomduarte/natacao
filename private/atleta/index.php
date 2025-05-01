<?php
include($_SERVER['DOCUMENT_ROOT'] . "/natacao/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . "/natacao/head.php");
$db = new Conexao();
$pdo = $db->conectar();

$atletaObj = new Atleta($pdo);
$atletas = $atletaObj->listar();
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
            <?php include($_SERVER['DOCUMENT_ROOT'] . "/natacao/sidebar.php"); ?>
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4">Cadastro de Atletas</h4>
                        <a href="<?php $_SERVER['DOCUMENT_ROOT']?>/natacao/private/atleta/form.php" class="btn btn-success mb-3">Novo Atleta</a>

                        <table id="tabelaResultados" class="table table-striped table-bordered nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Registro</th>
                                    <th>Nome</th>
                                    <th>Nascimento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($atletas as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['registro']) ?></td>
                                        <td><?= htmlspecialchars($a['nome']) ?></td>
                                        <td><?= htmlspecialchars($a['nascimento']) ?></td>
                                        <td>
                                            <a href="<?php $_SERVER['DOCUMENT_ROOT']?>/natacao/private/atleta/form.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                                            <a href="<?php $_SERVER['DOCUMENT_ROOT']?>/natacao/private/atleta/excluir.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/natacao/footer.php"; ?>
</body>