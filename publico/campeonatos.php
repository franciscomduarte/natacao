<?php
  include_once("../config.php");
  include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");
  
  $db = new Conexao();
  $pdo = $db->conectar();

  $campeonato = new Campeonato($pdo);
  $campeonatos = $campeonato->listarCampeonatos();

  ?>

<style>
  .container-resultados {
    padding: 20px;
  }
</style>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <?php include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sidebar.php"); ?>

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Lista de Campeonatos</h4>

              <!-- Basic Bootstrap Table -->
              <div class="card p-4">
                <h5 class="card-header">Campeonatos Importados</h5>
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <thead class="table-light">
                      <tr>
                        <th>Campeonato</th>
                        <th>Realizado em</th>
                        <th>Piscina</th>
                        <th>Data</th>
                        <th>Situação</th>
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($campeonatos as $campeonato) { ?>
                        <tr>
                            <td><?php echo $campeonato['nome']; ?></td>
                            <td><?php echo $campeonato['cidade']; ?></td>
                            <td><?php echo $campeonato['piscina']; ?></td>
                            <td><?php echo $campeonato['realizacao']; ?></td>
                            <td><span class="badge bg-label-primary me-1">Realizado</span></td>
                            <td>
                            </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <!--/ Basic Bootstrap Table -->

              <!-- / Content -->
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>
    </div>
    <!-- / Layout wrapper -->

    <?php include $_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/footer.php"; ?>
  </body>

</html>
