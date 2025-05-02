<?php
include_once("../../config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sessao.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");
$db = new Conexao();
$pdo = $db->conectar();

$obj = new Campeonato($pdo);
$campeonatos = $obj->listar();
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
                        <a href="<?php $_SERVER['DOCUMENT_ROOT'] . BASE_URL ?>/private/acampeonato/form.php" class="btn btn-success mb-3">Novo Atleta</a>

                        <table id="tabelaResultados" class="table table-striped table-bordered nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cidade</th>
                                    <th>Piscina</th>
                                    <th>Realização</th>
                                    <th>Ano</th>
                                    <th>Chave</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campeonatos as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['nome']) ?></td>
                                        <td><?= htmlspecialchars($c['cidade']) ?></td>
                                        <td><?= htmlspecialchars($c['piscina']) ?></td>
                                        <td><?= htmlspecialchars($c['realizacao']) ?></td>
                                        <td><?= htmlspecialchars($c['ano']) ?></td>
                                        <td><?= htmlspecialchars($c['chave']) ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL ?>/private/campeonato/form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                                            <a href="<?php echo BASE_URL ?>/private/campeonato/excluir.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
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