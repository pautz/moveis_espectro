<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Móveis Espectro - Design e Conforto em Mato Grosso</title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="Móveis Espectro oferece móveis planejados e sob medida em Mato Grosso. Veja fotos, localização e entre em contato para transformar seu ambiente.">
  <meta name="keywords" content="móveis, planejados, design, conforto, Mato Grosso, Primavera do Leste, móveis sob medida, espectro">
  <meta name="author" content="Móveis Espectro">

  <!-- Open Graph (para redes sociais) -->
  <meta property="og:title" content="Móveis Espectro - Design e Conforto em Mato Grosso">
  <meta property="og:description" content="Móveis planejados e sob medida com qualidade e estilo.">
  <meta property="og:image" content="img1.png">
  <meta property="og:url" content="https://www.moveisespectro.com.br">
  <meta property="og:type" content="website">

  <!-- Favicon -->
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  
 <style>/* ======== ESTILO GERAL ======== */
body {
  margin: 0;
  font-family: 'Segoe UI', Arial, sans-serif;
  background: rgba(229, 57, 53, 0.85);
  color: #fff;
}

/* ======== NAVBAR ======== */
nav {
  position: fixed;       /* fixo na janela */
  top: 200px;            /* sempre 200px do topo */
  left: 0;
  width: 100%;
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(229, 57, 53, 0.4);
  z-index: 1000;         /* garante que fique acima dos outros elementos */
}

nav a {
  text-decoration: none;
  color: #fff;
  font-weight: bold;
  transition: color 0.3s ease;
  padding: 8px 12px; /* aumenta área clicável */
}

nav a:hover {
  color: #ffebee;
}

nav .brand {
  display: flex;
  gap: 25px;
  justify-content: center;
  align-items: center;
}

nav .brand img {
  height: 55px;
  transition: opacity 0.8s ease-in-out; /* só opacidade, sem mexer em layout */
}

/* ======== CARROSSEL ======== */
.carousel {
  margin-top: 100px;
  position: relative;
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.carousel img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  transition: opacity 1s ease-in-out;
}

.carousel img.active {
  opacity: 1;
}

/* ======== BOTÕES DO CARROSSEL ======== */
.carousel button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  border: 1px solid rgba(229, 57, 53, 0.8);
  padding: 12px;
  cursor: pointer;
  border-radius: 50%;
  color: #fff;
  transition: background 0.3s ease, transform 0.3s ease;
}

.carousel button:hover {
  background: rgba(229, 57, 53, 0.9);
  transform: scale(1.1);
}

.prev { left: 20px; }
.next { right: 20px; }

/* ======== FOOTER ======== */
footer {
  backdrop-filter: blur(8px);
  text-align: center;
  padding: 20px;
  box-shadow: 0 -2px 5px rgba(229, 57, 53, 0.4);
  color: #fff;
}

/* ======== RESPONSIVIDADE ======== */
@media (max-width: 768px) {
  nav {
    flex-direction: column;
  }

  nav .brand {
    flex-direction: column;
    gap: 10px;
  }

  .carousel button {
    padding: 10px;
  }

  nav .brand img {
    height: 45px;
  }
}

/* ======== AJUSTE PARA CONTEÚDO ======== */
</style>
</head>
<body>
    <center><header class="logo">
    <img src="logo.png" alt="Logo" height="200">
  </header></center>
    <div class="container">
  <nav>
  <a href="https://farolqr.com/login/login_farolqr.php">Entrar</a>
  <div class="logo">
    <div class="brand">
  <a href="https://www.farolqr.com/sys/hora_carrinho.php">
    <img src="logo.png" alt="Logotipo Móveis Espectro" style="height:100px;">
  </a>
</div>

    <a href="https://api.whatsapp.com/send/?phone=5555996129682&text=eu%20vim%20de%20farolqr.com" target="_blank">Contato</a>
  <a href="https://maps.app.goo.gl/pVGqqStXZcpHqfRf8" target="_blank">Localização</a>
  </div>
  
</nav>


  <main>
    <div class="carousel">
      <img src="img1.png" class="active" alt="Cozinha planejada moderna">
      <img src="img2.png" alt="Sala de estar com móveis sob medida">
      <img src="img3.png" alt="Quarto elegante com móveis personalizados">
      <button class="prev">&#10094;</button>
      <button class="next">&#10095;</button>
    </div>
  </main>

  <footer>
    <p>&copy; 2026 - Móveis Espectro | Todos os direitos reservados</p>
    <p>Primavera do Leste - Mato Grosso</p>
  </footer>

  <script>
    const images = document.querySelectorAll('.carousel img');
    let current = 0;
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');

    function showImage(index) {
      images.forEach((img, i) => {
        img.classList.toggle('active', i === index);
      });
    }

    prevBtn.addEventListener('click', () => {
      current = (current - 1 + images.length) % images.length;
      showImage(current);
    });

    nextBtn.addEventListener('click', () => {
      current = (current + 1) % images.length;
      showImage(current);
    });

    setInterval(() => {
      current = (current + 1) % images.length;
      showImage(current);
    }, 5000);
  </script>  </div>
</body>
</html>
