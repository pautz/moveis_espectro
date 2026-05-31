<?php
session_start();

$servername = "127.0.0.1";
$username = "u839226731_farol";
$password = "Meta6595869!";
$dbname = "u839226731_farol";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verifica se o usuário está logado
if (!isset($_SESSION['username_odonto2'])) {
    echo "<p style='color:red;text-align:center;'>Você precisa estar logado para acessar esta página.</p>";
    $conn->close();
    exit;
}

$eq_user = $_SESSION['username_odonto2'];

// Consulta para verificar se o usuário é assinante nível 2
$stmt = $conn->prepare("SELECT assinantenv3 FROM identificacao_odonto2 WHERE username = ?");
$stmt->bind_param("s", $eq_user);
$stmt->execute();
$stmt->bind_result($assinantenv2);
$stmt->fetch();
$stmt->close();

if ((int)$assinantenv2 !== 1) {
    echo "<p style='color:red;text-align:center;'>Acesso restrito: apenas assinantes nível 2 podem acessar esta página.</p>";
    $conn->close();
    exit;
}

// Se o formulário foi enviado para aprovar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "UPDATE consorcio_cadastro SET aprovacao = 1 WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green; font-weight:bold; text-align:center;'>Consórcio ID $id aprovado com sucesso!</p>";
    } else {
        echo "<p style='color:red; text-align:center;'>Erro ao aprovar: " . $conn->error . "</p>";
    }
}

// Filtros opcionais
$searchQuery = "WHERE aprovacao = 0";
if (!empty($_GET['search_id'])) {
    $search_id = intval($_GET['search_id']);
    $searchQuery .= " AND id = $search_id";
}
if (!empty($_GET['search_eq_user'])) {
    $search_eq_user = $conn->real_escape_string($_GET['search_eq_user']);
    $searchQuery .= " AND eq_user LIKE '%$search_eq_user%'";
}

// Buscar registros pendentes com filtros (inclui imagem)
$sql = "SELECT id, consulta, cidade, estado, eq_user, imagem, aprovacao 
        FROM consorcio_cadastro 
        $searchQuery ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel de Aprovação</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f5f5f5; padding:40px; }
    table { width:100%; border-collapse: collapse; background:#fff; }
    th, td { padding:12px; border:1px solid #ccc; text-align:center; }
    th { background:#00796b; color:#fff; }
    form { display:inline; }
    button { background:#ff9800; color:#fff; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; }
    button:hover { background:#e65100; }
    .filter { text-align:center; margin-bottom:20px; }
    .filter input { padding:8px; margin:5px; border-radius:5px; border:1px solid #ccc; }
    .filter button { padding:8px 15px; border-radius:5px; background:#00796b; color:#fff; border:none; cursor:pointer; }
    img { max-width:120px; border-radius:8px; }
  </style>
</head>
<body>
  <h2 style="text-align:center;">Painel de Aprovação (Somente Assinantes Nível 2)</h2>

  <div class="filter">
    <form method="GET">
      <input type="text" name="search_id" placeholder="Buscar por ID" value="<?= isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : '' ?>">
      <input type="text" name="search_eq_user" placeholder="Buscar por Criador" value="<?= isset($_GET['search_eq_user']) ? htmlspecialchars($_GET['search_eq_user']) : '' ?>">
      <button type="submit">Filtrar</button>
    </form>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Imagem</th>
      <th>Consulta</th>
      <th>Cidade</th>
      <th>Estado</th>
      <th>Criador</th>
      <th>Status</th>
      <th>Ação</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td>
            <?php if (!empty($row['imagem'])): ?>
              <img src="<?= htmlspecialchars('../../site3/cadastro_produto/' . $row['imagem']) ?>" alt="Imagem do Consórcio">
            <?php else: ?>
              <span style="color:#e65100; font-weight:bold;">Sem imagem</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['consulta']) ?></td>
          <td><?= htmlspecialchars($row['cidade']) ?></td>
          <td><?= htmlspecialchars($row['estado']) ?></td>
          <td><?= htmlspecialchars($row['eq_user']) ?></td>
          <td><?= $row['aprovacao'] == 1 ? "Aprovado" : "Pendente" ?></td>
          <td>
            <form method="POST">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit">Aprovar</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8">Nenhum consórcio pendente.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
