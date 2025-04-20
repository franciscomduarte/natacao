<?php
include("head.php");

$db = new Conexao();
$pdo = $db->conectar();

$campeonatoObj = new Campeonato($pdo);
$resultadoObj = new Resultado($pdo);

$campeonatos = $campeonatoObj->listarCampeonatos();
$entidades = $resultadoObj->listarEntidades();
$anos = $resultadoObj->listarAnos();

// Filtros
$filtros = [
    'campeonato' => $_GET['campeonato'] ?? '',
    'entidade' => $_GET['entidade'] ?? '',
    'ano' => $_GET['ano'] ?? '',
    'piscina' => $_GET['piscina'] ?? ''
];

$resultados = $resultadoObj->filtrarResultados($filtros);
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
      <?php include("sidebar.php"); ?>
      <div class="layout-page">
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Resultados /</span> Campeonatos</h4>

            <!-- Filtros -->
            <div class="card mb-4">
              <div class="card-body">
                <form method="GET">
                  <div class="row">
                    <div class="col-md-3">
                      <label>Campeonato</label>
                      <select name="campeonato" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($campeonatos as $c) : ?>
                          <option value="<?= $c['id'] ?>" <?= $c['id'] == $filtros['campeonato'] ? 'selected' : '' ?>>
                            <?= $c['nome'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Entidade</label>
                      <select name="entidade" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($entidades as $e) : ?>
                          <option value="<?= $e['entidade'] ?>" <?= $e['entidade'] == $filtros['entidade'] ? 'selected' : '' ?>>
                            <?= $e['entidade'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Ano</label>
                      <select name="ano" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($anos as $a) : ?>
                          <option value="<?= $a['ano'] ?>" <?= $a['ano'] == $filtros['ano'] ? 'selected' : '' ?>>
                            <?= $a['ano'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                      <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                      <a href="resultados.php" class="btn btn-primary w-100">Limpar</a>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Resultados -->
            <div class="card p-4">
              <div class="table-responsive">
                <table id="tabelaResultados" class="table table-striped table-bordered nowrap" style="width:100%">
                    <thead class="table-light">
                    <tr>
                      <th>Campeonato</th>
                      <th>Ano</th>
                      <th>Prova</th>
                      <th>Atleta</th>
                      <th>Entidade</th>
                      <th>Tempo</th>
                      <th>Colocação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($resultados as $res) : ?>
                      <tr>
                        <td><?= $res['campeonato'] ?></td>
                        <td><?= $res['ano'] ?></td>
                        <td><?= $res['descricao'] ?></td>
                        <td><?= $res['atleta'] ?></td>
                        <td><?= $res['entidade'] ?></td>
                        <td><?= $res['tempo'] ?></td>
                        <td><?= $res['colocacao'] ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>
</body>

</html>
