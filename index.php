<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="vertical-menu-template-free"
>
  <?php 
    include_once('head.php'); 
    
    $db = new Conexao();
    $pdo = $db->conectar();

    $campeonato = new Campeonato($pdo);
    $totalCampeonatos = $campeonato->contarCampeonatos();

  ?>

<style>

.imagem-centralizada {
  display: block;
  margin: 45px auto;
  border-radius: 12px;
  max-width: 300px;
}

.chatbox {
  height: 200px;
  overflow-y: auto;
  background: #f9f9f9;
}

.message {
  margin-bottom: 12px;
  display: flex;
  align-items: flex-start;
}

.message.user {
  justify-content: flex-end;
  text-align: right;
}

.message.bot {
  justify-content: flex-start;
}

.message .text {
  max-width: 70%;
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 15px;
  line-height: 1.4;
}

.message.user .text {
  background-color: #007bff;
  color: white;
  border-bottom-right-radius: 0;
}

.message.bot .text {
  background-color: #e9ecef;
  color: #333;
  border-bottom-left-radius: 0;
  display: flex;
  align-items: flex-start;
}

.avatar {
  width: 40px;
  height: 40px;
  margin-right: 10px;
  border-radius: 50%;
  object-fit: cover;
}

</style>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        
        <?php include('sidebar.php'); ?>

        <!-- Layout container -->
        <div class="layout-page">

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                  <div class="card">
                    <div class="d-flex align-items-end row">
                      <div class="col-sm-7">
                        <div class="card-body">
                          <h5 class="card-title text-primary">L3 Swim chegou! 🎉</h5>
                          <p class="mb-4">
                              O <span class="fw-bold">L3Swim</span> é um site especializado em natação competitiva, desenvolvido com o objetivo de auxiliar técnicos e atletas na busca por alto desempenho e excelência esportiva. Com uma abordagem focada em dados, análise e acompanhamento de resultados, o L3Swim oferece ferramentas que ajudam a transformar dados em performance.
                          </p>
                        </div>
                      </div>
                      <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                          <img class="imagem-centralizada"
                            src="assets/img/illustrations/image.png"
                            height="140"
                            alt="View Badge User"
                            data-app-dark-img="illustrations/man-with-laptop-dark.png"
                            data-app-light-img="illustrations/man-with-laptop-light.png"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 col-md-12 order-1">
                  <div class="row">
                    
                  <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img
                                            src="assets/img/icons/unicons/chart-success.png"
                                            alt="chart success"
                                            class="rounded"
                                        />
                                    </div>
                                </div>
                                <h3 class="card-title mb-2">Tempos x Indices</h3>
                                <span class="fw-semibold d-block mb-1">Os brasileiros estão ai! Veja a tabela de indices e veja quanto falta pra você!</span>
                                <a href="<?= BASE_URL ?>/publico/tempos.php" class="btn btn-primary">Ver mais</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img
                                            src="assets/img/icons/unicons/wallet-info.png"
                                            alt="Credit Card"
                                            class="rounded"
                                        />
                                    </div>
                                </div>
                                <h3 class="card-title text-nowrap mb-1">Resultados</h3>
                                <span class="fw-semibold d-block mb-1">Encontre aqui os resultados das competições regionais e nacionais!</span>
                                <a href="<?= BASE_URL ?>/publico/resultados.php" class="btn btn-primary">Ver mais</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img
                                            src="assets/img/icons/unicons/cc-primary.png"
                                            alt="Credit Card"
                                            class="rounded"
                                        />
                                    </div>
                                </div>
                                <h3 class="card-title text-nowrap mb-1">Calculadora de I.T</h3>
                                <span class="fw-semibold d-block mb-1">Veja o seu indice Técnico de acordo com as regras da World Aquatics!</span>
                                <a href="<?= BASE_URL ?>/publico/calculadora.php" class="btn btn-primary">Ver mais</a>
                            </div>
                        </div>
                    </div>


                    <!-- Widget do Chat com Avatar e Texto -->
                    <div id="chat-widget" onclick="toggleChat()">
                      <span class="chat-label">
                        Conheça o <strong>Ligeirinho</strong><br>
                        nosso especialista em natação
                      </span>
                      <div id="chat-button">
                          <img src="assets/img/avatars/avatar.png" alt="Personagem do chat" class="chat-avatar">
                      </div>
                    </div>


                      <!-- Janela do Chat -->
                      <div id="chat-container" class="d-none">
                        <div class="chat-card">
                          <div class="chat-header">
                            <span>Ligeirinho - Especialista em Natação</span>
                            <span class="chat-close" onclick="toggleChat()">✖</span>
                          </div>
                          <div class="chat-body" id="chatbox">
                            <!-- Mensagens vão aqui -->
                          </div>
                          <div class="chat-footer">
                            <form id="formChat" class="d-flex gap-2">
                              <input type="text" id="mensagem" class="form-control" placeholder="Digite sua pergunta..." required>
                              <button class="btn btn-primary" type="submit">Enviar</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    
                  </div>
                </div>
              </div>
            </div>



            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                  ©
                  <script>
                    document.write(new Date().getFullYear());
                  </script>, made by <a mailto="francisco.m.duarte@gmail.com"><b>Francisco Molina</b></a>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <?php include 'footer.php'; ?>

  </body>
</html>
