# moveis_espectro
Sistema de venda de móveis planejados e sob medida

## 🚀 Passos de configuração

1. **Mover arquivos**
   - Copie todos os arquivos de dentro de `farolqr.com` para dentro do seu `public_html`.
   - Obs: utilizo a **Hostinger.com** para hospedar meus sites.

2. **Banco de dados**
   - Crie o banco a partir do arquivo `u839226731_farol.sql`.
   - Ajuste as configurações de conexão no arquivo `aura_db.php`.

3. **Configuração Mercado Pago**
   - Configure a **API do Mercado Pago** com seu **Access Token** e **Public Key**.
   - No painel do **Mercado Pago Developers**, defina a URL do webhook para:
     ```
     site2/pix_aura/webhook.php
     ```

4. **Alterar credenciais nos arquivos**
   - `gera_pix.php`
     ```php
     $access_token = 'SEU_ACCESS_TOKEN'; // substitua pelo seu Access Token
     ```
   - `gerar_cartao.php`
     ```javascript
     const mp = new MercadoPago('SUA_PUBLIC_KEY'); // substitua pela sua Public Key
     ```
   - `processar_cartao.php`
     ```php
     $access_token = 'SEU_ACCESS_TOKEN'; // substitua pelo seu Access Token
     ```
      - `webhook.php`
 ```javascript
     $access_token = getenv('MP_ACCESS_TOKEN') ?: 'ACESS TOKEN';
 ```

5. **Testar**
   - Gere um Pix ou pagamento no cartão para validar a integração.
   - Verifique se o webhook atualiza corretamente o status no banco.

---

## 📌 Observações
- Sempre mantenha suas credenciais seguras (não versionar tokens no GitHub).
- O sistema foi projetado para ser **instalado e utilizado por qualquer pessoa ou empresa**.
- Funciona tanto com **Pix** quanto com **pagamentos parcelados no cartão**.
