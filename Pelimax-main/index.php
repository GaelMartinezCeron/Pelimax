<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'conexion.php';

// Si ya hay una sesión iniciada, lo mandamos a su panel correspondiente
if (isset($_SESSION['admin_id'])) {
    header("Location: menu.php");
    exit();
} elseif (isset($_SESSION['cliente_id'])) {
    header("Location: catalogo.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escapamos los datos por seguridad
    $usuario = $con->real_escape_string($_POST['usuario']);
    $password = $con->real_escape_string($_POST['password']);

    // PASO 1: Buscar si el usuario es un ADMINISTRADOR
    $sql_admin = "SELECT * FROM usuarios_admin WHERE correo = '$usuario' AND password = '$password' AND estatus = 1";
    $result_admin = $con->query($sql_admin);

    if ($result_admin->num_rows > 0) {
        $row = $result_admin->fetch_assoc();
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_nombre'] = $row['nombre'];
        header("Location: menu.php");
        exit();
    } else {
      
            $error = "Usuario o contraseña incorrectos.";
        
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Cinema - Inicio</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="globals.css">
</head>
<body>
    <!-- Fondo dinámico con partículas -->
    <div class="premium-bg">
        <div class="gradient-sphere sphere-1"></div>
        <div class="gradient-sphere sphere-2"></div>
        <div class="gradient-sphere sphere-3"></div>
    </div>
    
    <div class="floating-particles" id="particles"></div>

    <!-- Header con logo -->
    <a href="index.php" class="header-logo">
        <span class="logo-icon">
            <i class="bi bi-camera-reels-fill"></i>
        </span>
        <span class="logo-text">GOLDEN CINEMA</span>
    </a>

    <!-- Contenedor principal -->
    <div class="login-container">
        <!-- Tarjeta de login -->
        <div class="login-card">
            <div class="card-header">
                <h1 class="card-title">Bienvenido</h1>
                <p class="card-subtitle">Ingresa tus datos para continuar</p>
            </div>
            
            <?php if($error != ""): ?>
                <div class="alert-premium">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" class="login-form">
                <div class="input-group-modern">
                    <div class="input-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <input type="email" 
                           name="usuario" 
                           class="form-control-premium" 
                           placeholder="Correo electrónico" 
                           required
                           id="emailInput">
                    <div class="input-line"></div>
                </div>
                
                <div class="input-group-modern">
                    <div class="input-icon">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <input type="password" 
                           name="password" 
                           class="form-control-premium" 
                           placeholder="Contraseña" 
                           required
                           id="passwordInput">
                    <button type="button" class="password-toggle" onclick="togglePassword()" id="togglePassword">
                        <i class="bi bi-eye-slash-fill"></i>
                    </button>
                    <div class="input-line"></div>
                </div>
                
                <div class="form-options">
                  
                    <a href="recuperar.php" class="link-premium">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                
                <button type="submit" class="btn-premium">
                    <span class="btn-text">Iniciar sesión</span>
                    <i class="bi bi-arrow-right-short"></i>
                </button>
            </form>

           

            
        </div>
    </div>

    <!-- Scripts -->
    <script src="inde.js"></script>
</body>
</html>