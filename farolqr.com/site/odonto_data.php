<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once 'conexao.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: https://carlitoslocacoes.com/farolqr/site/login_farolqr.php");
    exit;
}

$eq_user = $_SESSION["username_odonto2"];
$erro = '';
$simulacao = '';

$id_consorcio = isset($_GET['id']) ? intval($_GET['id']) : null;

$consulta = '';
$valor_total = 0;
$entrada_minima = 0;
$estoque = 0;
$servico = '';
$cepOrigem = '';
$peso = 0;
$altura = 0;
$largura = 0;
$comprimento = 0;

if ($id_consorcio) {
    $stmt = $cx->prepare("SELECT consulta, valor_total, entrada, estoque, servico, cep_origem, peso, altura, largura, comprimento 
                          FROM consorcio_cadastro WHERE id = ?");
    $stmt->bind_param("i", $id_consorcio);
    $stmt->execute();
    $stmt->bind_result($consulta, $valor_total, $entrada_minima, $estoque, $servico, $cepOrigem, $peso, $altura, $largura, $comprimento);
    $stmt->fetch();
    $stmt->close();
}
if ($estoque <= 0) {
    echo "<p style='color:red; text-align:center; font-weight:bold;'>Este consórcio não possui estoque disponível.</p>";
    $cx->close();
    exit;
}

$tokenJWT = "eyJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NzE1MTU5NjcsImlzcyI6InRva2VuLXNlcnZpY2UiLCJleHAiOjE3NzE2MDIzNjcsImp0aSI6ImU4MzJmNzEyLWVkZmUtNDFhZS04MDQxLTJjM2U3MmM2NmU3NSIsImFtYmllbnRlIjoiUFJPRFVDQU8iLCJwZmwiOiJQRiIsImlwIjoiMTc3LjE1Mi4xNzguMjI5LCAxOTIuMTY4LjEuMTMyIiwiY2F0IjoiSWQwIiwiY3BmIjoiMDQxNTQ2NTIwNjAiLCJpZCI6ImNhcmxpdG9wYXV0eiJ9.qOUSryk27y_-eJLAgXfQi5384B-V0rtCASE-odOsHw4vDgVySPtbxhxonMtFm9KelPjoNBb528Cv-En0XUewwS_k1_AuDWnazLJXGZY5MOtWpmVZdIisU_lw3vwAJyGxhjwmXu6r16Fd0vhZPz0Hn_uhsh8YqVLLHsZPqvblFNy4-7BTK_lqXSRYVU5kZaFEwe96Bj5rm_T-FHUoTEjvm0xcl6H8XfTTBs2UTjFbmGfBKyVXEsiUmKhSkpxqwSgizDGAk4BQwNN04gyl71S1DvXbLLej76F0xot8lWb9Vt7B0TgqyEue5TBan2jN-vPfOd8f9AotH22bXLQa2_xhig";

// Função de cálculo com regra especial
function calcularFreteComToken($token, $cepDestino, $servico, $cepOrigem, $peso, $altura, $largura, $comprimento) {
    // Caso especial: se todas as dimensões e peso forem zero, frete = 0
    if ($peso == 0 && $altura == 0 && $largura == 0 && $comprimento == 0) {
        return 0;
    }

    $url = "https://api.correios.com.br/frete/v1/calculo";

    $dadosFrete = [
        "cepOrigem"   => preg_replace('/[^0-9]/', '', $cepOrigem),
        "cepDestino"  => preg_replace('/[^0-9]/', '', $cepDestino),
        "peso"        => $peso > 0 ? $peso : 1,
        "comprimento" => $comprimento > 0 ? $comprimento : 20,
        "altura"      => $altura > 0 ? $altura : 5,
        "largura"     => $largura > 0 ? $largura : 15,
        "servico"     => $servico
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dadosFrete));

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $json = json_decode($response, true);
        $valor = $json['valor'] ?? 0;

        if ($valor <= 0) {
            return 200.00; // fallback
        }
        return $valor;
    }

    return 200.00; // fallback se API não responder
}

// Simulação
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["acao"] === "simular") {
    $entrada = floatval($_POST["entrada"] ?? 0);
    $parcelas = intval($_POST["parcelas"] ?? 0);
    $cep = trim($_POST["cep"] ?? '');

    $frete = calcularFreteComToken($tokenJWT, $cep, $servico, $cepOrigem, $peso, $altura, $largura, $comprimento);

    $valor_total_com_frete = $valor_total + $frete;
    if ($valor_total <= 0 || $entrada < $entrada_minima || $entrada >= $valor_total_com_frete || $parcelas < 1) {
        $erro = "Entrada inválida. Deve ser no mínimo R$ " . number_format($entrada_minima, 2, ',', '.') . " e menor que o valor total.";
    } else {
        $valor_parcela = ($valor_total_com_frete - $entrada) / $parcelas;
        $simulacao = "Frete: " . ($frete == 0 ? "Grátis" : "R$ " . number_format($frete, 2, ',', '.')) .
                     " | Total com frete: R$ " . number_format($valor_total_com_frete, 2, ',', '.') . 
                     " | Parcelas: " . $parcelas . "x de R$ " . number_format($valor_parcela, 2, ',', '.');
    }
}

// Confirmação
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["acao"] === "confirmar") {
    $entrada = floatval($_POST["entrada"] ?? 0);
    $parcelas = intval($_POST["parcelas"] ?? 0);
    $nickname = trim($_POST["nickname"] ?? '');
    $senha = $_POST["senha"] ?? '';

    $rua    = trim($_POST["rua"] ?? '');
    $numero = trim($_POST["numero"] ?? '');
    $bairro = trim($_POST["bairro"] ?? '');
    $cidade = trim($_POST["cidade"] ?? '');
    $estado = trim($_POST["estado"] ?? '');
    $cep    = trim($_POST["cep"] ?? '');

    $frete = calcularFreteComToken($tokenJWT, $cep, $servico, $cepOrigem, $peso, $altura, $largura, $comprimento);

    $valor_total_com_frete = $valor_total + $frete;
    if ($valor_total <= 0 || $entrada < $entrada_minima || $entrada >= $valor_total_com_frete || $parcelas < 1) {
        $erro = "Valor inválido. A entrada deve ser de no mínimo R$ " . number_format($entrada_minima, 2, ',', '.') . " e menor que o valor total.";
    } else {
        $_SESSION['simulacao'] = [
            'id_consorcio' => $id_consorcio,
            'consulta' => $consulta,
            'valor_total' => $valor_total,
            'entrada_minima' => $entrada_minima,
            'entrada' => $entrada,
            'parcelas' => $parcelas,
            'nickname' => $nickname,
            'senha' => $senha,
            'rua' => $rua,
            'numero' => $numero,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'estado' => $estado,
            'cep' => $cep,
            'frete' => $frete,
            'valor_total_com_frete' => $valor_total_com_frete
        ];
        header("Location: pagamento_entrada.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Definir Parcelas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <h3 class="text-center mb-4">Usuário: <?= htmlspecialchars($eq_user) ?></h3>
  <p class="text-center text-muted">ID do Cadastro: <?= $id_consorcio ?></p>

  <div class="container mt-5" style="max-width: 600px;">
    <h2 class="text-center mb-4">Definir Parcelas do Contrato</h2>

    <?php if ($erro): ?>
      <div class="alert alert-danger text-center"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="id_consorcio" value="<?= $id_consorcio ?>">

      <div class="mb-3">
        <label class="form-label">Plano Odonto:</label>
        <p class="form-control-plaintext"><?= htmlspecialchars($consulta) ?></p>
      </div>
<div class="mb-3">
  <label class="form-label">Entrada mínima exigida:</label>
  <p class="form-control-plaintext">R$ <?= number_format($entrada_minima, 2, ',', '.') ?></p>
</div>

            <div class="mb-3">
        <label for="entrada" class="form-label">Entrada (R$):</label>
        <input type="number" name="entrada" id="entrada" step="0.01"
               min="<?= $entrada_minima ?>" max="<?= $valor_total - 0.01 ?>"
               class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="parcelas" class="form-label">Quantidade de Parcelas:</label>
        <input type="number" name="parcelas" id="parcelas" min="1" class="form-control" required>
      </div>

      <!-- Endereço completo -->
      <div class="mb-3">
        <label for="rua" class="form-label">Rua:</label>
        <input type="text" name="rua" id="rua" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="numero" class="form-label">Número:</label>
        <input type="text" name="numero" id="numero" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="bairro" class="form-label">Bairro:</label>
        <input type="text" name="bairro" id="bairro" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="cidade" class="form-label">Cidade:</label>
        <input type="text" name="cidade" id="cidade" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="estado" class="form-label">Estado:</label>
        <input type="text" name="estado" id="estado" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="cep" class="form-label">CEP:</label>
        <input type="text" name="cep" id="cep" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="nickname" class="form-label">Nickname/Apelido:</label>
        <input type="text" name="nickname" id="nickname" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="senha" class="form-label">Confirme sua senha:</label>
        <input type="password" name="senha" id="senha" class="form-control" required>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" name="acao" value="confirmar" class="btn btn-success">Confirmar e Pagar Entrada</button>
        <button type="submit" name="acao" value="simular" class="btn btn-secondary">Simular Parcelas</button>
      </div>
    </form>

    <?php if ($simulacao): ?>
      <div class="alert alert-info text-center"><?= $simulacao ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
