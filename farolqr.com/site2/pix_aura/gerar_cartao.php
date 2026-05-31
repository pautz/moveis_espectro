<?php
$caixa_postal = $_GET['caixa_postal'] ?? '';
$aura = intval($_GET['aura'] ?? 0);

// preço fixo por aura
$preco_por_aura = 1.30;
$valor_reais = round($aura * $preco_por_aura, 2);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Pagamento com Cartão</title>
  <script src="https://sdk.mercadopago.com/js/v2"></script>
  <style>
  /* Reset básico */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", Arial, sans-serif;
}

/* Fundo da página */
body {
  background: linear-gradient(135deg, #f5f7fa, #e4ebf7);
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}

/* Card do formulário */
form {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
  max-width: 420px;
  width: 100%;
  animation: fadeIn 0.6s ease-in-out;
}

/* Título */
h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #333;
  font-size: 22px;
  font-weight: 700;
}

/* Labels */
label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #444;
}

/* Inputs e selects */
input, select {
  width: 100%;
  padding: 12px 14px;
  margin-bottom: 18px;
  border: 1px solid #ccc;
  border-radius: 8px;
  transition: border-color 0.3s, box-shadow 0.3s;
  font-size: 15px;
}

input:focus, select:focus {
  border-color: #007bff;
  box-shadow: 0 0 6px rgba(0,123,255,0.3);
  outline: none;
}

/* Botão */
button {
  width: 100%;
  padding: 14px;
  background: #007bff;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

button:hover {
  background: #0056b3;
  transform: scale(1.02);
}

/* Responsividade */
@media (max-width: 480px) {
  form {
    padding: 20px;
  }
  h2 {
    font-size: 20px;
  }
  input, select, button {
    font-size: 14px;
  }
}

/* Animação de entrada */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(-10px);}
  to {opacity: 1; transform: translateY(0);}
}

  </style>
</head>
<body>
  <form id="paymentForm" method="POST" action="processar_cartao.php">
    <h2>Pagamento com Cartão</h2>
    <label>Caixa Postal</label>
    <input type="text" name="caixa_postal" value="<?php echo htmlspecialchars($caixa_postal); ?>" readonly />

    <label>Aura</label>
    <input type="number" name="aura" value="<?php echo $aura; ?>" readonly />

    <label>Valor em Reais</label>
    <input type="text" value="R$ <?php echo number_format($valor_reais, 2, ',', '.'); ?>" readonly />

    <label>Email</label>
    <input type="email" name="email" required />

    <label>Número do cartão</label>
    <input type="text" id="cardNumber" maxlength="16" required />

    <label>Validade (MM/AA)</label>
    <input type="text" id="cardExpirationMonth" maxlength="2" required />
    <input type="text" id="cardExpirationYear" maxlength="2" required />

    <label>CVV</label>
    <input type="text" id="securityCode" maxlength="4" required />

    <label>Nome do titular</label>
    <input type="text" id="cardholderName" required />

    <label>Parcelas</label>
    <select name="installments" id="installments" required>
      <option value="">Selecione...</option>
    </select>

    <!-- Campos ocultos -->
    <input type="hidden" name="token" id="token" />
    <input type="hidden" name="payment_method_id" id="payment_method_id" />

    <button type="submit">Pagar R$ <?php echo number_format($valor_reais, 2, ',', '.'); ?></button>
  </form>

  <script>
    const mp = new MercadoPago('PUBLICKEY'); // substitua pela sua Public Key
    const form = document.getElementById('paymentForm');
    const cardNumberElement = document.getElementById('cardNumber');
    const installmentsSelect = document.getElementById('installments');
    const valor = <?php echo $valor_reais; ?>;

    // Regra própria de parcelamento baseada no valor
    function gerarParcelas(valor) {
      installmentsSelect.innerHTML = "";
      let maxParcelas = 1;

      if (valor >= 100 && valor < 300) {
        maxParcelas = 3;
      } else if (valor >= 300 && valor < 600) {
        maxParcelas = 6;
      } else if (valor >= 600) {
        maxParcelas = 12;
      }

      for (let i = 1; i <= maxParcelas; i++) {
        const opt = document.createElement("option");
        opt.value = i;
        opt.textContent = `${i}x de R$ ${(valor/i).toFixed(2)}`;
        installmentsSelect.appendChild(opt);
      }
    }

    // Gera parcelas ao carregar a página
    gerarParcelas(valor);

    // Gera token e envia
    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      const cardData = {
        cardNumber: cardNumberElement.value,
        cardExpirationMonth: document.getElementById('cardExpirationMonth').value,
        cardExpirationYear: document.getElementById('cardExpirationYear').value,
        securityCode: document.getElementById('securityCode').value,
        cardholderName: document.getElementById('cardholderName').value,
      };

      try {
        const token = await mp.createCardToken(cardData);
        document.getElementById('token').value = token.id;
        form.submit();
      } catch (error) {
        console.error("Erro ao gerar token:", error);
        alert("Não foi possível gerar o token do cartão.");
      }
    });
  </script>
</body>
</html>
