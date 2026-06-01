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

$preco_por_aura = 1;
$reais = round($aura * $preco_por_aura, 2);
$unique_id = uniqid('card_', true);

$access_token = 'ACESS TOKEN'; // substitua pelo seu Access Token privado
$descricao = "FarolQR.Com Compra de $aura aura para caixa postal $caixa";

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
$status = $resultado['status'] ?? 'erro';
$card_id = $resultado['id'] ?? 'sem_id';

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

echo "<h2>Resultado do Pagamento</h2>";
echo "<p>Status: <strong>$status</strong></p>";
echo "<p>Caixa Postal: $caixa</p>";
echo "<p>Aura: $aura</p>";
echo "<p>Valor total: R$ " . number_format($reais, 2, ',', '.') . "</p>";
echo "<p>Parcelas: $installments x de R$ " . number_format($valor_parcela, 2, ',', '.') . "</p>";
