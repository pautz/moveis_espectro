<?php
session_start();
$token = $_GET['token'] ?? '';
$id    = intval($_GET['id'] ?? 0);

$servername = "127.0.0.1";
$username   = "u839226731_farol";
$password   = "Meta6595869!";
$dbname     = "u839226731_farol";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Conexão falhou: " . $conn->connect_error); }
$conn->set_charset("utf8");

// Busca carrinho
$sql = "SELECT dados FROM carrinhos WHERE token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

$dadosItem = [];
if ($row = $res->fetch_assoc()) {
    $carrinho = json_decode($row['dados'], true);
    if (isset($carrinho[$id])) {
        $dadosItem = $carrinho[$id];
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Alterar Endereço do Item</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; }
    .container { max-width: 600px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #007BFF; }
    input { width: 100%; padding: 8px; margin: 5px 0; }
    .btn { display: inline-block; padding: 10px 15px; margin-top: 10px; background: #007BFF; color: #fff; text-decoration: none; border-radius: 5px; }
</style>
</head>
<body>
<div class="container">
<h2>Alterar Endereço do Produto <?php echo $id; ?></h2>
<form method="post" action="salvar_endereco_item.php?token=<?php echo $token; ?>&id=<?php echo $id; ?>">
    <input type="text" name="rua" placeholder="Rua" value="<?php echo htmlspecialchars($dadosItem['rua'] ?? ''); ?>">
    <input type="text" name="numero" placeholder="Número" value="<?php echo htmlspecialchars($dadosItem['numero'] ?? ''); ?>">
    <input type="text" name="bairro" placeholder="Bairro" value="<?php echo htmlspecialchars($dadosItem['bairro'] ?? ''); ?>">
    <input type="text" name="cidade" placeholder="Cidade" value="<?php echo htmlspecialchars($dadosItem['cidade'] ?? ''); ?>">
    <input type="text" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($dadosItem['estado'] ?? ''); ?>">
    <input type="text" name="cep" placeholder="CEP" value="<?php echo htmlspecialchars($dadosItem['cep'] ?? ''); ?>">
    <input type="text" name="telefone" placeholder="Telefone" value="<?php echo htmlspecialchars($dadosItem['telefone'] ?? ''); ?>">
    <input type="text" name="complemento" placeholder="Complemento" value="<?php echo htmlspecialchars($dadosItem['complemento'] ?? ''); ?>">
    <button type="submit" class="btn">Salvar Endereço do Item</button>
</form>
<a href="carrinho_view.php?token=<?php echo $token; ?>" class="btn" style="background:#6c757d;">Voltar ao Carrinho</a>
</div>
</body>
</html>
