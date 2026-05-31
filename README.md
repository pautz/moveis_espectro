# moveis_espectro
venda de moveis


move os arquivos de dentro de farolqr.com para dentro do seu public_html, lembrando que eu utilizo a hostinger.com para hospedar meus sites.
crie o db apartir de u839226731_farol.sql, 
basta mudar as configuracoes de banco, api do mercado pago com acess token e public key, e no mercado pago developers configurar a url de webhook para site2
/pix_aura/webhook.php

devo mudar public key e acess token em gera_pix.php gerar_cartao.php processar_cartao.php

gerar_cartao.php
 const mp = new MercadoPago('PUBLICKEY'); // substitua pela sua Public Key

gerar_pix.php
$access_token = 'acess token'; //substitua pelo acess token

processar_cartao.php
$access_token = 'acess token'; // substitua pelo seu Access Token 
