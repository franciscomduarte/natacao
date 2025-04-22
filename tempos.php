<?php
include("head.php");
include("utils.php");

$db = new Conexao();
$pdo = $db->conectar();

$resultadoObj = new Resultado($pdo);
$indiceObj = new Indice($pdo);

$atletas = $resultadoObj->listarAtletas();
$categorias = $indiceObj->listarCatergorias();

$atletaSelecionado = $_GET['atleta'] ?? '';
$categoriaSelecionada = $_GET['categoria'] ?? '';
$melhoresTempos = [];
$atletaObj = null;

if ($atletaSelecionado) {
  $melhoresTempos = $resultadoObj->melhoresTemposPorProva($atletaSelecionado);
  $atletaObj = $resultadoObj->listarAtleta($atletaSelecionado);
}
?>

<style>
  .container-resultados {
    padding: 20px;
  }
</style>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?php include("sidebar.php"); ?>
      <div class="layout-page">
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Resultados /</span> Tempos x Indíces</h4>

            <!-- Filtro de Atleta -->
            <div class="card mb-4">
              <div class="card-body">
                <form method="GET">
                  <div class="row">
                    <div class="col-md-6">
                      <label>Atleta</label>
                      <select name="atleta" class="form-control" required>
                        <option value="">Selecione um atleta</option>
                        <?php foreach ($atletas as $atleta) : ?>
                          <option value="<?= $atleta['atleta'] ?>" <?= $atleta['atleta'] == $atletaSelecionado ? 'selected' : '' ?>>
                            <?= $atleta['atleta'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                    <label>Categoria</label>
                      <select name="categoria" class="form-control" required>
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria) : ?>
                          <option value="<?= $categoria['categoria'] ?>" <?= $categoria['categoria'] == $categoriaSelecionada ? 'selected' : '' ?>>
                            <?= $categoria['categoria'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                      <button type="submit" class="btn btn-primary w-100">Pesquisar</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Resultados -->
            <?php if ($melhoresTempos) : 
              $contador = 0;
            ?>
              <div class="card p-4">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead class="table-light">

                    </thead>
                    <tbody>
                      <?php foreach ($melhoresTempos as $tempo) : 
                              $contador++;
                              $indice25m = $indiceObj->listarIndice25m($tempo['prova'], $categoriaSelecionada, $atletaObj['sexo']);  
                              $indice50m = $indiceObj->listarIndice50m($tempo['prova'], $categoriaSelecionada, $atletaObj['sexo']);

                              $indiceInverno25m = isset($indice25m[0]['tempo']) ? $indice25m[0]['tempo'] : '';
                              $indiceInverno50m = isset($indice50m[0]['tempo']) ? $indice50m[0]['tempo'] : '';
                              $indiceVerao25m = isset($indice25m[1]['tempo']) ? $indice25m[1]['tempo'] : '';
                              $indiceVerao50m = isset($indice50m[1]['tempo']) ? $indice50m[1]['tempo'] : '';

                              $diferencaInverno25m = tempoParaSegundos($tempo['tempo']) - tempoParaSegundos($indiceInverno25m);
                              $diferencaVerao25m = tempoParaSegundos($tempo['tempo']) - tempoParaSegundos($indiceVerao25m);
                              $diferencaInverno50m = tempoParaSegundos($tempo['tempo']) - tempoParaSegundos($indiceInverno50m);
                              $diferencaVerao50m = tempoParaSegundos($tempo['tempo']) - tempoParaSegundos($indiceVerao50m);
                      ?>
                      <?php if (isset($indice25m[0]['estacao'])) { ?>
                        <?php if ($indice25m[0]['estacao'] && $indice25m[0]['estacao'] != 'ANUAL') { ?>
                          <?php if ($contador <= 1) {?>
                          <tr>
                            <th>Prova</th>
                            <th>Melhor Tempo</th>
                            <th colspan="2">Indíce Piscina 25M</th>
                            <th colspan="2">Indíce Piscina 50M</th>
                          </tr>
                          <tr>
                            <th colspan="2"></th>
                            <th>Inverno</th>
                            <th>Verão</th>
                            <th>Inverno</th>
                            <th>Verão</th>
                          </tr>
                          <?php } ?>
                          <tr>
                            <td><?= $tempo['prova_descricao'] ?></td>
                            <td><?= $tempo['tempo'] ?></td>
                            <td>
                              <?= $indiceInverno25m == '' ? '-' : $indiceInverno25m . " <span style='font-size: 0.8em; color: " . ($diferencaInverno25m > 0 ? 'red' : 'green') . ";'> (" . segundosParaTempo($diferencaInverno25m) . ")</span>" ?>
                            </td>
                            <td>
                              <?= $indiceVerao25m == '' ? '-' : $indiceVerao25m . " <span style='font-size: 0.8em; color: " . ($diferencaVerao25m > 0 ? 'red' : 'green') . ";'> (" . segundosParaTempo($diferencaVerao25m) . ")</span>" ?>
                            </td>
                            <td>
                              <?= $indiceInverno50m == '' ? '-' : $indiceInverno50m . " <span style='font-size: 0.8em; color: " . ($diferencaInverno50m > 0 ? 'red' : 'green') . ";'> (" . segundosParaTempo($diferencaInverno50m) . ")</span>" ?>
                            </td>
                            <td>
                              <?= $indiceVerao50m == '' ? '-' : $indiceVerao50m . " <span style='font-size: 0.8em; color: " . ($diferencaVerao50m > 0 ? 'red' : 'green') . ";'> (" . segundosParaTempo($diferencaVerao50m) . ")</span>" ?>
                            </td>
                          </tr>
                        <?php } else { ?>
                          <?php if ($contador <= 1) {?>
                            <tr>
                              <th>Prova</th>
                              <th>Melhor Tempo</th>
                              <th>Indíce Piscina 25M</th>
                              <th>Indíce Piscina 50M</th>
                            </tr>
                            <tr>
                              <th colspan="2"></th>
                              <th>Anual</th>
                              <th>Anual</th>
                            </tr>
                          <?php } ?>
                          <tr>
                            <td><?= $tempo['prova_descricao'] ?></td>
                            <td><?= $tempo['tempo'] ?></td>
                            <td>
                              <?= $indiceInverno25m == '' ? '-' : $indiceInverno25m . " <span style='font-size: 0.8em; color: " . ($diferencaInverno25m > 0 ? 'red' : 'green') . ";'> (" . segundosParaTempo($diferencaInverno25m) . ")</span>" ?>
                            </td>
                            <td>
                              <?= $indiceInverno50m == '' ? '-' : $indiceInverno50m . " <span style='font-size: 0.8em; color: " . ($diferencaInverno50m > 0 ? 'red' : 'green') . ";'> (" . segundosParaTempo($diferencaInverno50m) . ")</span>" ?>
                            </td>
                          </tr>
                        <?php } 
                        }?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php elseif ($atletaSelecionado) : ?>
              <div class="alert alert-warning">Nenhum resultado encontrado para este atleta.</div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>
</body>

</html>
