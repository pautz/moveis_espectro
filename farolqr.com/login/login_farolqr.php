<?php
// Initialize the session
session_start();
// Forçar HSTS (apenas se estiver usando HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin_odonto2"]) && $_SESSION["loggedin_odonto2"] === true){
    header("location: https://farolqr.com/farolqr/identificacao_farolqr.php");
    exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM odonto2_users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin_odonto2"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username_odonto2"] = $username;                            
                            
                            // Redirect user to welcome page
                            header("location: https://farolqr.com/farolqr/identificacao_farolqr.php");
                        } else{
                            // Display an error message if password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Brutal Bikes</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
  <style>body {
  font-family: 'Montserrat', sans-serif;
  background: linear-gradient(to right, #880e4f, #ad1457, #c2185b); /* tons de rosa */
  color: #fff;
  text-align: center;
  overflow-x: hidden;
}

.login-container {
  margin-top: 5%;
  animation: fadeIn 2s ease-in-out;
}

.login-icon {
  width: 250px;
  height: 250px;
  margin: 20px auto;
  background: url('../logo.png') no-repeat center; /* usa logo.png */
  background-size: contain;
  animation: zoomIn 1.5s ease-in-out;
}

h2 {
  font-weight: 700;
  margin-bottom: 10px;
  animation: slideDown 1.2s ease-in-out;
}

p {
  font-size: 16px;
  animation: fadeIn 2.5s ease-in-out;
}

form {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  padding: 30px;
  width: 90%;
  max-width: 400px;
  margin: 0 auto;
  animation: fadeInUp 2s ease-in-out;
}

input[type="text"], input[type="password"] {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border: none;
  border-radius: 5px;
  background-color: rgba(255, 255, 255, 0.8);
  color: #333;
  transition: 0.3s;
}

input[type="text"]:focus, input[type="password"]:focus {
  background-color: #fff;
  box-shadow: 0 0 10px #ec407a; /* rosa claro */
}

.btn-xl {
  padding: 10px 20px;
  font-size: 16px;
  border-radius: 10px;
  width: 45%;
  margin: 5px;
  transition: 0.3s;
}

.btn-xl:hover {
  transform: scale(1.05);
}

/* Botões com cores rosa */
.btn-primary {
  background-color: #d81b60;
  border-color: #d81b60;
}

.btn-primary:hover {
  background-color: #ad1457;
  border-color: #ad1457;
}

.btn-info {
  background-color: #f06292;
  border-color: #f06292;
}

.btn-info:hover {
  background-color: #ec407a;
  border-color: #ec407a;
}

.btn-success {
  background-color: #e91e63;
  border-color: #e91e63;
}

.btn-success:hover {
  background-color: #c2185b;
  border-color: #c2185b;
}

/* Animações */
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes zoomIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }

@media (max-width: 600px) {
  .btn-xl { width: 90%; }
  form { padding: 20px; }
  .login-icon { width: 90px; height: 90px; }
}
</style>
</head>
<body>
  <div class="login-container">
    <div class="login-icon"></div>
    <h2>Entrar</h2>
    <p>Preencha seus dados para acessar o sistema.</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <input type="text" name="username" placeholder="Usuário">
      <input type="password" name="password" placeholder="Senha">
      <input type="submit" class="btn btn-primary btn-xl" value="Entrar">
      <a class="btn btn-info btn-xl" href="https://farolqr.com/">Início</a>
      <p>Não tem conta?<br><a href="register_odonto2.php" class="btn btn-success btn-xl">Registrar-se</a></p>
    </form>
  </div>

  <!-- VLibras -->
  <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper><div class="vw-plugin-top-wrapper"></div></div>
  </div>
  <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
  <script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>
</body>
</html>
