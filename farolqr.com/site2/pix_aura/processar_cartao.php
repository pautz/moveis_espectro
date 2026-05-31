<?php
require 'aura_db.php';

$caixa = $_POST['caixa_postal'] ?? '';
$aura  = intval($_POST['aura'] ?? 0);
$token = $_POST['token'] ?? '';
$email = $_POST['email'] ?? '';
$payment_method_id = $_POST['payment_method_id'] ?? '';
$installments = intval($_POST['installments'] ?? 1);

if ($caixa === '' || $aura < 1 || $token === '' || $payment_method_id === '') {
    die("⚠️ Dados inválidos.");
}

// preço fixo por aura
$preco_por_aura = 1.30;
$reais = round($aura * $preco_por_aura, 2);
$unique_id = uniqid('card_', true);

$access_token = 'acess token'; // substitua pelo seu Access Token privado
$descricao = "FarolQR.coms - compra de $aura aura para caixa postal $caixa";

// calcula valor da parcela pela sua regra própria
$valor_parcela = round($reais / $installments, 2);

$dados = [
    'transaction_amount' => $reais,
    'token' => $token,
    'description' => $descricao,
    'installments' => $installments,
    'payment_method_id' => $payment_method_id,
    'payer' => [
        'email' => $email,
        'first_name' => $caixa,
        'last_name' => 'aura: ' . (string)$aura
    ]
];

// chamada à API do Mercado Pago
$ch = curl_init('https://api.mercadopago.com/v1/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
    'X-Idempotency-Key: ' . $unique_id
]);
$resposta = curl_exec($ch);

if ($resposta === false) {
    die("❌ Erro de conexão com Mercado Pago: " . curl_error($ch));
}
curl_close($ch);

$resultado = json_decode($resposta, true);
if (!isset($resultado['id'])) {
    die("❌ Erro no pagamento: " . $resposta);
}

$status = $resultado['status'] ?? 'erro';
$card_id = $resultado['id'];

// gravação no banco
$stmt = $cx->prepare("INSERT INTO pedidos_aura 
  (caixa_postal, quantidade, valor_reais, pix_id, unique_id, copia_cola, link_pix, status, ip_cliente, payment_method_id, installments) 
  VALUES (?, ?, ?, ?, ?, '', '', ?, ?, ?, ?)");

$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';

$stmt->bind_param(
  "sidssssssi", 
  $caixa, $aura, $reais, $card_id, $unique_id, $status, $ip_cliente, $payment_method_id, $installments
);
$stmt->execute();
$stmt->close();

// feedback ao usuário
echo "<h2>Resultado do Pagamento</h2>";
echo "<p>Status: <strong>$status</strong></p>";
echo "<p>Caixa Postal: $caixa</p>";
echo "<p>Aura: $aura</p>";
echo "<p>Valor pago: R$ " . number_format($reais, 2, ',', '.') . "</p>";
echo "<p>Parcelas: $installments x de R$ " . number_format($valor_parcela, 2, ',', '.') . "</p>";

if ($status === 'approved') {
    echo "<p style='color:green'>✅ Pagamento aprovado!</p>";
} elseif ($status === 'pending') {
    echo "<p style='color:orange'>⏳ Pagamento pendente. Aguarde a confirmação.</p>";
} elseif ($status === 'rejected') {
    echo "<p style='color:red'>❌ Pagamento rejeitado. Verifique os dados do cartão ou tente outro método.</p>";
} else {
    echo "<p>⚠️ Status desconhecido. Detalhes: " . htmlspecialchars($resposta) . "</p>";
}
?>
