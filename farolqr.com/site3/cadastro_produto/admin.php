<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: https://farolqr.com/login/login_farolqr.php");
    exit;
}

$eq_user = $_SESSION["username_odonto2"];

// Conexão com o banco
$host = "127.0.0.1";
$usuario = "u839226731_farol";
$senha = "Meta6595869!";
$banco = "u839226731_farol";

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8");

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

// Processar formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $consulta     = htmlspecialchars($_POST['consulta']);
    $valor_total  = floatval($_POST['valor_total']);
    $entrada      = floatval($_POST['entrada']);
    $cidade       = htmlspecialchars($_POST['cidade']);
    $estado       = htmlspecialchars($_POST['estado']);
    $estoque      = intval($_POST['estoque']);
    $cep_origem   = htmlspecialchars($_POST['cep_origem']);
    $peso         = floatval($_POST['peso']);
    $altura       = intval($_POST['altura']);
    $largura      = intval($_POST['largura']);
    $comprimento  = intval($_POST['comprimento']);
    $servico      = htmlspecialchars($_POST['servico']); // PAC ou SEDEX (códigos dos Correios)
    $lc = htmlspecialchars($_POST['lc']);
$hectare = floatval($_POST['hectare']);
$valor_hectare = floatval($_POST['valor_hectare']);

if ($hectare <= 0) {
    echo "<p style='color:red;text-align:center;'>O hectare plantado deve ser maior que zero.</p>";
    exit;
}

if ($valor_hectare <= 0) {
    echo "<p style='color:red;text-align:center;'>O valor total do hectare deve ser maior que zero.</p>";
    exit;
}

    // Validação
    if ($valor_total <= 0) {
        echo "<p style='color:red;text-align:center;'>O valor total deve ser maior que zero.</p>";
        exit;
    }

    if ($entrada < 0.01 || $entrada > $valor_total) {
        echo "<p style='color:red;text-align:center;'>A entrada deve ser maior que R$ 0,00 e não pode ultrapassar o valor total.</p>";
        exit;
    }

    if ($estoque < 0) {
        echo "<p style='color:red;text-align:center;'>O estoque não pode ser negativo.</p>";
        exit;
    }

    // Upload da imagem
    if ($_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            echo "<p style='color:red;text-align:center;'>A pasta 'uploads' não existe. Crie manualmente no servidor.</p>";
            exit;
        }

        $imagem_nome = $_FILES['imagem']['name'];
        $imagem_tmp  = $_FILES['imagem']['tmp_name'];
        $novo_nome   = uniqid() . "_" . basename($imagem_nome);
        $destino     = $upload_dir . $novo_nome;

        if (move_uploaded_file($imagem_tmp, $destino)) {
            $destino_db = $destino;

            $stmt = $conn->prepare("INSERT INTO consorcio_cadastro 
(consulta, valor_total, entrada, eq_user, cidade, estado, imagem, estoque, cep_origem, 
 peso, altura, largura, comprimento, servico, lc, hectare, valor_hectare) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sddssssiiddiissdd", 
    $consulta, 
    $valor_total, 
    $entrada, 
    $eq_user, 
    $cidade, 
    $estado, 
    $destino_db, 
    $estoque, 
    $cep_origem, 
    $peso, 
    $altura, 
    $largura, 
    $comprimento, 
    $servico, 
    $lc, 
    $hectare, 
    $valor_hectare
);



            if ($stmt->execute()) {
                $consorcio_id = $stmt->insert_id;
                echo "<p style='color:green;text-align:center;'>Consórcio cadastrado com sucesso! ID: $consorcio_id</p>";
                echo "<script>setTimeout(function(){ window.location.href = 'https://farolqr.com/site/odonto_data.php?id=$consorcio_id'; }, 2000);</script>";
            } else {
                echo "<p style='color:red;text-align:center;'>Erro ao cadastrar consórcio.</p>";
            }

            $stmt->close();
        } else {
            echo "<p style='color:red;text-align:center;'>Falha ao mover o arquivo para a pasta uploads.</p>";
        }
    } else {
        echo "<p style='color:red;text-align:center;'>Erro no upload: código " . $_FILES['imagem']['error'] . "</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro Inicial de Consórcio</title>
 <style>body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #fce4ec; /* fundo rosa claro */
  padding: 40px;
  display: flex;
  justify-content: center;
}

.container {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(233,30,99,0.3); /* sombra rosa */
  max-width: 600px;
  width: 100%;
}

h2 {
  text-align: center;
  color: #d81b60; /* título rosa forte */
  margin-bottom: 20px;
}

label {
  font-weight: bold;
  display: block;
  margin-top: 15px;
  margin-bottom: 5px;
  color: #880e4f; /* tom vinho-rosa */
}

input[type="text"],
input[type="number"],
input[type="file"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #f48fb1; /* borda rosa suave */
  border-radius: 6px;
  font-size: 16px;
  transition: box-shadow 0.3s ease;
}

input[type="text"]:focus,
input[type="number"]:focus,
input[type="file"]:focus {
  box-shadow: 0 0 8px #ec407a; /* brilho rosa ao focar */
  outline: none;
}

button {
  background: linear-gradient(90deg, #e91e63, #ad1457); /* degradê rosa */
  color: white;
  border: none;
  padding: 14px;
  font-size: 16px;
  border-radius: 28px;
  cursor: pointer;
  width: 100%;
  margin-top: 20px;
  font-weight: bold;
  transition: transform 0.2s ease, background 0.3s ease;
}

button:hover {
  transform: scale(1.05);
  background: linear-gradient(90deg, #ec407a, #c2185b);
}
</style>
</head>
<body>
  <div class="container">
    <h2>Cadastro Inicial de Consórcio</h2>
    <p>Ao cadastrar irá para aprovação.</p>
    <form method="post" enctype="multipart/form-data">
      <label for="consulta">Tipo de Bem ou Serviço:</label>
      <input type="text" name="consulta" id="consulta" required>
<label for="hectare">Hectare Plantado:</label>
<input type="number" name="hectare" id="hectare" step="0.01" min="0.01" required>

<label for="valor_hectare">Valor Total do Hectare (KG):</label>
<input type="number" name="valor_hectare" id="valor_hectare" step="0.01" min="0.01" required>

      <label for="valor_total">Valor(R$):</label>
      <input type="number" name="valor_total" id="valor_total" step="0.01" required>

      <label for="entrada">Entrada Mínima (R$):</label>
      <input type="number" name="entrada" id="entrada" step="0.01" required>
<label for="lc">L.C.:</label>
<input type="text" name="lc" id="lc" required>

      <label for="cidade">Cidade:</label>
      <input type="text" name="cidade" id="cidade" required>

      <label for="estado">Estado:</label>
      <input type="text" name="estado" id="estado" required>

      <label for="cep_origem">CEP de Origem:</label>
      <input type="text" name="cep_origem" id="cep_origem" required>

      <label for="peso">Peso (kg):</label>
      <input type="number" name="peso" id="peso" step="0.01" required>

      <label for="altura">Altura (cm):</label>
      <input type="number" name="altura" id="altura" required>

      <label for="largura">Largura (cm):</label>
      <input type="number" name="largura" id="largura" required>

      <label for="comprimento">Comprimento (cm):</label>
      <input type="number" name="comprimento" id="comprimento" required>

      <label for="servico">Serviço dos Correios (40010=SEDEX, 41106=PAC):</label>
      <input type="text" name="servico" id="servico" required>

      <label for="imagem">Imagem do Bem:</label>
      <input type="file" name="imagem" id="imagem" accept="image/*" required>

      <label for="estoque">Estoque disponível:</label>
      <input type="number" name="estoque" id="estoque" min="0" required>

      <button type="submit">Avançar para Parcelas</button>
    </form>
  </div>
</body>
</html>
