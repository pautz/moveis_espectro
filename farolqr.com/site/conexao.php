<?php
// ⚙️ Configurações de conexão
$host = '127.0.0.1';
$usuario = 'u839226731_farol';
$senha = 'Meta6595869!';
$banco = 'u839226731_farol';

// 🧠 Conecta ao MySQL
$cx = new mysqli($host, $usuario, $senha, $banco);

// 🚨 Verifica erro de conexão
if ($cx->connect_error) {
    die("Erro na conexão com o banco de dados: " . $cx->connect_error);
}

// 🛡️ Define charset para UTF-8
$cx->set_charset("utf8");
