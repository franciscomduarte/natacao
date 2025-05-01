<?php
include_once("../config.php");
include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/head.php");

$db = new Conexao();
$pdo = $db->conectar();

$indiceObj = new Indice($pdo);
$provas = $indiceObj->listarProvasFINA(); // Método que retorna as provas com tempo de referência

$resultado = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prova = $_POST["prova"] ?? '';
    $tempo = $_POST["tempo"] ?? '';
    $sexo = $_POST["sexo"] ?? '';
    $piscina = $_POST["piscina"] ?? '';

    if ($prova && $tempo) {
        $tempoReferencia = $indiceObj->obterTempoReferenciaFINA($prova, $sexo, $piscina);
        $pontos = calcularPontuacaoFINA($tempo, $tempoReferencia);
        $resultado = [
            'tempo' => $tempo,
            'referencia' => $tempoReferencia['basetime'],
            'pontos' => $pontos
        ];
    }
}

function calcularPontuacaoFINA($tempoAtleta, $tempoReferencia) {
    $segundosAtleta = tempoParaSegundos($tempoAtleta);
    $segundosReferencia = tempoParaSegundos($tempoReferencia['basetime']);
    if ($segundosAtleta <= 0 || $segundosReferencia <= 0) return 0;
    return round(1000 * pow($segundosReferencia / $segundosAtleta, 3));
}
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include($_SERVER['DOCUMENT_ROOT'] . BASE_URL . "/sidebar.php"); ?>
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">FINA /</span> Cálculo de Índice Técnico</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Informações sobre a Pontuação FINA</h5>
                                        <p class="card-text">
                                            A Tabela de Pontuação da World Aquatics permite comparações de resultados entre diferentes provas.
                                            A pontuação da World Aquatics atribui valores de pontos às performances na natação — mais pontos para
                                            desempenhos de nível mundial (normalmente 1000 ou mais) e menos pontos para tempos mais lentos.
                                        </p>
                                        <p class="card-text">
                                            Os valores de pontos são definidos anualmente. As tabelas apresentam uma pontuação para piscina curta (25m)
                                            e outra para piscina longa (50m).
                                        </p>
                                        <h6 class="card-subtitle mt-3 text-muted">Fórmula</h6>
                                        <p class="card-text">
                                            Os pontos (P) são calculados usando uma curva cúbica, com o tempo nadado (T) e o tempo base (B), ambos em segundos:
                                            <pre><code>P = 1000 × (B / T)³</code></pre>
                                            A fórmula exata é usada para calcular os pontos a partir dos tempos. Depois disso, os valores de pontos são
                                            truncados para um número inteiro. Nas tabelas de pontos de 2009 e anos anteriores, os valores eram arredondados.
                                        </p>
                                        <p class="card-text">
                                            Se for necessário calcular o tempo (T) para atingir um número específico de pontos (P), a fórmula exata é usada
                                            para uma estimativa inicial. Em seguida, o tempo é ajustado em centésimos de segundo até que o cálculo inverso
                                            retorne o número exato de pontos.
                                        </p>
                                        <h6 class="card-subtitle mt-3 text-muted">Tempos Base para 1000 Pontos</h6>
                                        <p class="card-text">
                                            Os tempos base são definidos para todas as provas individuais e revezamentos comuns, separados por masculino/feminino
                                            e piscina longa/piscina curta. Esses tempos são definidos todos os anos com base no recorde mundial mais recente
                                            aprovado pela World Aquatics. Para piscina curta (SCM), o corte é em 31 de agosto. Para piscina longa (LCM), o corte é em
                                            31 de dezembro.
                                        </p>
                                        <p class="card-text">
                                            Por exemplo, para a pontuação de 2023: os tempos em piscina curta são válidos até 31 de agosto de 2024, e os tempos em
                                            piscina longa até 31 de dezembro de 2023.
                                        </p>
                                        <p class="card-text">
                                            Os tempos base são publicados no site da World Aquatics até um mês após o fim do respectivo período.
                                            Os arquivos publicados contêm a fórmula, os tempos base usados no cálculo e a pontuação que seria atribuída aos
                                            atletas com base em seus tempos em LCM ou SCM.
                                            <span>Veja os tempos base da FINA de 2025 <a href="https://resources.fina.org/fina/document/2025/01/08/baaf68c9-0118-42c3-ac3f-e11ce013fd8a/Points-Base-times-SCM-and-LCM-2025.pdf" target="_blank">aqui</a></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Calcular Pontuação FINA</h5>
                                        <form method="POST">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="prova" class="form-label">Prova</label>
                                                    <select name="prova" class="form-control" required>
                                                        <option value="">Selecione uma prova</option>
                                                        <?php foreach ($provas as $p) : ?>
                                                            <option value="<?= $p['prova'] ?>" <?= ($p['prova'] == ($_POST['prova'] ?? '')) ? 'selected' : '' ?>>
                                                                <?= $p['prova'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="sexo" class="form-label">Sexo</label>
                                                    <select name="sexo" class="form-control" required>
                                                        <option value="">Selecione o sexo</option>
                                                        <option value="M" <?= (($_POST['sexo'] ?? '') == 'M') ? 'selected' : '' ?>>Masculino</option>
                                                        <option value="F" <?= (($_POST['sexo'] ?? '') == 'F') ? 'selected' : '' ?>>Feminino</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="piscina" class="form-label">Piscina</label>
                                                    <select name="piscina" class="form-control" required>
                                                        <option value="">Selecione a piscina</option>
                                                        <option value="25" <?= (($_POST['piscina'] ?? '') == '25') ? 'selected' : '' ?>>25 Metros</option>
                                                        <option value="50" <?= (($_POST['piscina'] ?? '') == '50') ? 'selected' : '' ?>>50 Metros</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tempo" class="form-label">Tempo (ex: 01:02.34)</label>
                                                    <input type="text" id="tempo" placeholder="00:00:00" class="form-control" name="tempo" value="<?= $_POST['tempo'] ?? '' ?>" required>
                                                </div>
                                                <div class="col-md-12 d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-primary">Calcular</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <?php if ($resultado): ?>
                                    <div class="card p-4">
                                        <h5 class="card-title">Resultado</h5>
                                        <p class="card-text"><strong>Prova:</strong> <?= htmlspecialchars($_POST['prova']) ?></p>
                                        <p class="card-text"><strong>Sexo:</strong> <?= htmlspecialchars($_POST['sexo'] == 'M' ? 'Masculino' : 'Feminino') ?></p>
                                        <p class="card-text"><strong>Tempo do atleta:</strong> <?= $resultado['tempo'] ?></p>
                                        <p class="card-text"><strong>Tempo de referência:</strong> <?= $resultado['referencia'] ?></p>
                                        <p class="card-text"><strong>Pontuação FINA:</strong> <?= $resultado['pontos'] ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/footer.php'; ?>

    <script>
        $(document).ready(function(){
            $('#tempo').mask('00:00:00', {placeholder: "00:00:00"});
        });
    </script>

</body>
</html>