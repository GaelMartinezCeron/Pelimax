<?php
include 'conexion.php';

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = "";
$tipoMensaje = ""; // 'success' o 'error'

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $correoIngresado = $con->real_escape_string($_POST['correo']);

    $buscarUsuario = $con->query("SELECT * FROM clientes WHERE correo='$correoIngresado'");

    if($buscarUsuario->num_rows > 0){

        // Generar contraseña aleatoria
        $nuevaClave = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789"),0,8);

        // Actualizar contraseña en BD
        $con->query("UPDATE clientes SET password='$nuevaClave' WHERE correo='$correoIngresado'");

        // Enviar correo
        $mail = new PHPMailer(true);

        try{
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gaelceron45@gmail.com'; // TU CORREO
            $mail->Password = 'xecauxliyfkpwvck';   // CONTRASEÑA DE APLICACION
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('gaelceron45@gmail.com','Golden Cinema');
            $mail->addAddress($correoIngresado);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de contrasena - Golden Cinema';
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body {
                            font-family: 'Montserrat', Arial, sans-serif;
                            background: #0a0f1e;
                            margin: 0;
                            padding: 0;
                        }
                        .container {
                            max-width: 600px;
                            margin: 0 auto;
                            background: #1a1f2e;
                            border-radius: 20px;
                            overflow: hidden;
                            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                        }
                        .header {
                            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
                            padding: 30px;
                            text-align: center;
                        }
                        .header h1 {
                            color: #0a0f1e;
                            margin: 0;
                            font-size: 28px;
                            font-weight: 800;
                        }
                        .content {
                            padding: 40px 30px;
                            color: #ffffff;
                        }
                        .password-box {
                            background: rgba(212, 175, 55, 0.1);
                            border: 2px solid #d4af37;
                            border-radius: 15px;
                            padding: 20px;
                            margin: 25px 0;
                            text-align: center;
                        }
                        .password {
                            font-size: 32px;
                            font-weight: 800;
                            color: #d4af37;
                            letter-spacing: 3px;
                            font-family: monospace;
                        }
                        .footer {
                            background: rgba(10, 15, 30, 0.5);
                            padding: 20px;
                            text-align: center;
                            color: #b0b7c4;
                            font-size: 14px;
                        }
                        .button {
                            display: inline-block;
                            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
                            color: #0a0f1e;
                            text-decoration: none;
                            padding: 12px 30px;
                            border-radius: 10px;
                            font-weight: 700;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🎬 GOLDEN CINEMA</h1>
                        </div>
                        <div class='content'>
                            <h2 style='color: #d4af37; margin-top: 0;'>Recuperación de contraseña</h2>
                            <p>Hemos generado una nueva contraseña para tu cuenta:</p>
                            
                            <div class='password-box'>
                                <div class='password'>$nuevaClave</div>
                            </div>
                            
                            <p><strong>⚠️ Importante:</strong> Te recomendamos cambiar esta contraseña después de iniciar sesión por razones de seguridad.</p>
                            
                            
                            
                        </div>
                        
                    </div>
                </body>
                </html>
            ";

            $mail->send();
            $mensaje = "¡Hemos enviado una nueva contraseña a tu correo electrónico!";
            $tipoMensaje = "success";

       } catch(Exception $e) {
            $mensaje = "Error al enviar el correo. Por favor intenta más tarde.";
            $tipoMensaje = "error";

            $errorReal = "[" . date("Y-m-d H:i:s") . "] ";
            $errorReal .= "Error PHPMailer: " . $e->getMessage() . PHP_EOL;

            error_log($errorReal);
            
            // Crear carpeta de logs si no existe
            if (!file_exists(__DIR__ . "/logs")) {
                mkdir(__DIR__ . "/logs", 0777, true);
            }
            file_put_contents(__DIR__ . "/logs/mail_errors.txt", $errorReal, FILE_APPEND);
        }

    } else {
        $mensaje = "El correo ingresado no está registrado en Golden Cinema.";
        $tipoMensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Cinema - Recuperar Contraseña</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS Personalizado (mismo que login.css) -->
    <link rel="stylesheet" href="globals.css">
    
    <style>
        /* Estilos adicionales específicos para recuperar.php */
        .recovery-card {
            max-width: 500px;
        }
        
        .recovery-icon {
            width: 80px;
            height: 80px;
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            color: var(--primary);
            animation: pulseIcon 2s infinite;
        }
        
        @keyframes pulseIcon {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 20px 5px rgba(212, 175, 55, 0.3);
            }
        }
        
        .info-box {
            background: rgba(212, 175, 55, 0.05);
            border-left: 4px solid var(--primary);
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .info-box i {
            color: var(--primary);
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .info-box p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.6;
        }
        
        .info-box strong {
            color: var(--primary);
            font-weight: 600;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            margin-top: 20px;
        }
        
        .back-link:hover {
            color: var(--primary);
            transform: translateX(-5px);
        }
        
        .back-link i {
            transition: var(--transition);
        }
        
        .back-link:hover i {
            transform: translateX(-3px);
        }
        
        /* Estilos específicos para mensajes */
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #4caf50;
        }
        
        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .recovery-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
            
            .info-box {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Fondo dinámico con partículas (mismo que login) -->
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
        <!-- Tarjeta de recuperación -->
        <div class="login-card recovery-card">
            <!-- Icono animado -->
            <div class="recovery-icon">
                <i class="bi bi-key-fill"></i>
            </div>
            
            <div class="card-header text-center">
                <h1 class="card-title">¿Olvidaste tu contraseña?</h1>
                <p class="card-subtitle">No te preocupes, te ayudamos a recuperarla</p>
            </div>
            
            <?php if($mensaje != ""): ?>
                <div class="alert-premium <?php echo $tipoMensaje == 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <i class="bi <?php echo $tipoMensaje == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
                    <span><?php echo $mensaje; ?></span>
                </div>
            <?php endif; ?>

            <!-- Caja de información -->
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <p>
                    <strong>¿Cómo funciona?</strong><br>
                    Ingresa tu correo electrónico y te enviaremos una nueva contraseña temporal. 
                    Luego podrás cambiarla en la configuración de tu perfil.
                </p>
            </div>

            <form method="POST" action="" id="recoveryForm" class="login-form">
                <div class="input-group-modern">
                    <div class="input-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <input type="email" 
                           name="correo" 
                           class="form-control-premium" 
                           placeholder="tu@email.com" 
                           required
                           id="emailInput"
                           value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                    <div class="input-line"></div>
                </div>
                
                <button type="submit" class="btn-premium" id="submitBtn">
                    <span class="btn-text">Enviar nueva contraseña</span>
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>

            <div class="divider-premium">
                <span>¿Recordaste tu contraseña?</span>
            </div>

            <div class="text-center">
                <a href="index.php" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Volver al inicio de sesión
                </a>
            </div>

            
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // ===== PARTÍCULAS FLOTANTES =====
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            initFormValidation();
        });

        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;
            
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 6 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 15 + 15) + 's';
                
                particlesContainer.appendChild(particle);
            }
        }

        // ===== VALIDACIÓN DEL FORMULARIO =====
        function initFormValidation() {
            const form = document.getElementById('recoveryForm');
            if (!form) return;
            
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('emailInput');
                const btn = document.getElementById('submitBtn');
                
                if (!validateEmail(email.value)) {
                    e.preventDefault();
                    showError(email, 'Ingresa un correo electrónico válido');
                } else {
                    // Animación de carga
                    const btnText = btn.querySelector('.btn-text');
                    const btnIcon = btn.querySelector('i');
                    
                    btnText.textContent = 'Enviando...';
                    btnIcon.className = 'bi bi-arrow-repeat spin';
                    btn.style.pointerEvents = 'none';
                }
            });
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function showError(input, message) {
            // Remover error anterior si existe
            const group = input.parentElement;
            const oldError = group.querySelector('.input-error');
            if (oldError) oldError.remove();
            
            // Crear nuevo mensaje de error
            const error = document.createElement('div');
            error.className = 'input-error';
            error.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${message}`;
            error.style.cssText = `
                color: #ff6b6b;
                font-size: 0.85rem;
                margin-top: 8px;
                margin-left: 15px;
                display: flex;
                align-items: center;
                gap: 5px;
                animation: slideDown 0.3s ease;
            `;
            
            group.appendChild(error);
            input.style.borderColor = '#ff6b6b';
            
            // Remover el error después de 3 segundos
            setTimeout(() => {
                if (error.parentNode) {
                    error.remove();
                    input.style.borderColor = '';
                }
            }, 3000);
        }

        // ===== ANIMACIONES ADICIONALES =====
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
                display: inline-block;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);

        // ===== EFECTO EN EL INPUT AL TENER VALOR =====
        const emailInput = document.getElementById('emailInput');
        if (emailInput && emailInput.value !== '') {
            emailInput.parentElement.classList.add('focused');
        }
    </script>
</body>
</html>