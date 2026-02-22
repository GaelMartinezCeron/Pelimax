<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include 'conexion.php';

$mensaje = "";

// ===============================
// FUNCIÓN PARA GENERAR PASSWORD
// ===============================
function generarPassword($longitud = 8) {
    $caracteres = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return substr(str_shuffle($caracteres), 0, $longitud);
}

// ===============================
// REGISTRO DE CLIENTE
// ===============================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {

    $nombre = $con->real_escape_string($_POST['nombre']);
    $apellidos = $con->real_escape_string($_POST['apellidos']);
    $correo = $con->real_escape_string($_POST['correo']);

    // Generar contraseña automática
    $passwordRaw = generarPassword(8);

    // Verificar si ya existe el correo
    $check = $con->query("SELECT id FROM clientes WHERE correo = '$correo'");
    if ($check->num_rows > 0) {

        $mensaje = "<div class='toast-notification error'>⚠️ El correo ya está registrado.</div>";

    } else {

        // ⚠ SE GUARDA EN TEXTO PLANO
        $sql = "INSERT INTO clientes (nombre, apellidos, correo, password, estatus)
                VALUES ('$nombre', '$apellidos', '$correo', '$passwordRaw', 1)";

        if ($con->query($sql)) {

            // ===============================
            // ENVÍO DE CORREO
            // ===============================
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
            require 'PHPMailer/src/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
            $mail->Username = 'gaelceron45@gmail.com'; // TU CORREO
            $mail->Password = 'xecauxliyfkpwvck';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('gaelceron45@gmail.com', 'Golden Cinema');
                $mail->addAddress($correo, $nombre);

                $mail->isHTML(true);
                $mail->Subject = 'Acceso a Golden Cinema';

                $mail->Body = "
                    <h2>🎬 Bienvenido a Golden Cinema</h2>
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>Tu cuenta ha sido creada correctamente.</p>
                    <p><strong>Correo:</strong> $correo</p>
                    <p><strong>Contraseña:</strong> $passwordRaw</p>
                    <br>
                    <p>Guarda esta información en un lugar seguro.</p>
                ";

                $mail->send();

                $mensaje = "<div class='toast-notification success'>
                    <strong>✓ Usuario registrado correctamente</strong><br>
                    <span style='font-family: monospace; background: rgba(0,0,0,0.1); padding: 4px 8px; border-radius: 8px; display: inline-block; margin-top: 8px;'>
                        🔑 Contraseña: $passwordRaw
                    </span>
                </div>";

            } catch (Exception $e) {

                $mensaje = "<div class='toast-notification warning'>
                    <strong>⚠️ Usuario registrado, sin correo</strong><br>
                    <span style='font-family: monospace; background: rgba(0,0,0,0.1); padding: 4px 8px; border-radius: 8px; display: inline-block; margin-top: 8px;'>
                        🔑 Contraseña: $passwordRaw
                    </span>
                </div>";
            }

        } else {
            $mensaje = "<div class='toast-notification error'>❌ Error: " . $con->error . "</div>";
        }
    }
}

// ===============================
// ACCIONES - CORREGIDO: Ahora usa POST para mantener el diseño
// ===============================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_cliente'])) {
    
    $id = intval($_POST['id']);
    
    if ($_POST['accion_cliente'] == 'eliminar') {
        $con->query("DELETE FROM clientes WHERE id = $id");
        $mensaje = "<div class='toast-notification info'>🗑️ Cliente eliminado permanentemente.</div>";
    }
    
    if ($_POST['accion_cliente'] == 'activar') {
        $con->query("UPDATE clientes SET estatus = 1 WHERE id = $id");
        $mensaje = "<div class='toast-notification success'>✓ Cliente activado correctamente.</div>";
    }
    
    if ($_POST['accion_cliente'] == 'desactivar') {
        $con->query("UPDATE clientes SET estatus = 0 WHERE id = $id");
        $mensaje = "<div class='toast-notification warning'>⚠️ Cliente desactivado.</div>";
    }
}

// ===============================
// CONSULTA
// ===============================
$clientes = $con->query("SELECT * FROM clientes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nexus - Gestión de Clientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #0a0c0f;
            color: #e4e6eb;
            line-height: 1.6;
        }

        /* ===== NAVBAR NEO-BRUTALISTA ===== */
        .nexus-nav {
            background: #16181c;
            border-bottom: 3px solid #00ff9d;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 0 #0a0c0f;
        }

        .nav-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .nav-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        .logo-block {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-marker {
            width: 50px;
            height: 50px;
            background: #00ff9d;
            clip-path: polygon(0 0, 100% 0, 80% 100%, 0% 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #0a0c0f;
            font-weight: 800;
        }

        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            color: #00ff9d;
            letter-spacing: -1px;
            text-transform: uppercase;
            text-shadow: 3px 3px 0 #0a0c0f, 5px 5px 0 rgba(0, 255, 157, 0.3);
        }

        .nav-actions-nexus {
            display: flex;
            gap: 15px;
        }

        .btn-nexus {
            background: transparent;
            border: 2px solid #00ff9d;
            color: #00ff9d;
            padding: 10px 24px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            clip-path: polygon(10px 0, 100% 0, calc(100% - 10px) 100%, 0 100%);
        }

        .btn-nexus:hover {
            background: #00ff9d;
            color: #0a0c0f;
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 rgba(0, 255, 157, 0.5);
        }

        .btn-nexus.logout {
            border-color: #ff3b3b;
            color: #ff3b3b;
        }

        .btn-nexus.logout:hover {
            background: #ff3b3b;
            color: #0a0c0f;
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .nexus-container {
            max-width: 1600px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .page-header-nexus {
            margin-bottom: 40px;
            position: relative;
        }

        .page-header-nexus h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #00ff9d;
            text-transform: uppercase;
            letter-spacing: -2px;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 4px 4px 0 #0a0c0f, 8px 8px 0 rgba(0, 255, 157, 0.2);
        }

        .page-header-nexus p {
            color: #8b949e;
            font-size: 1.1rem;
            margin-left: 8px;
            border-left: 4px solid #00ff9d;
            padding-left: 20px;
        }

        /* ===== TOAST NOTIFICATIONS ===== */
        .toast-notification {
            background: #1e1e24;
            border-left: 6px solid;
            padding: 20px 25px;
            margin-bottom: 30px;
            font-size: 1rem;
            box-shadow: 8px 8px 0 #0a0c0f;
            position: relative;
        }

        .toast-notification.success { border-color: #00ff9d; color: #00ff9d; }
        .toast-notification.error { border-color: #ff3b3b; color: #ff3b3b; }
        .toast-notification.warning { border-color: #ffb86b; color: #ffb86b; }
        .toast-notification.info { border-color: #6b8cff; color: #6b8cff; }

        /* ===== FORMULARIO ===== */
        .nexus-card {
            background: #16181c;
            border: 2px solid #2a2e35;
            margin-bottom: 40px;
            box-shadow: 10px 10px 0 #0a0c0f;
        }

        .card-header-nexus {
            padding: 20px 25px;
            border-bottom: 2px solid #2a2e35;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header-nexus h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #00ff9d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .card-header-nexus i {
            font-size: 1.6rem;
            color: #00ff9d;
        }

        .card-body-nexus {
            padding: 25px;
        }

        .form-grid-nexus {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .form-group-nexus {
            margin-bottom: 0;
        }

        .form-label-nexus {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #8b949e;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-control-nexus {
            width: 100%;
            background: #0a0c0f;
            border: 2px solid #2a2e35;
            color: #e4e6eb;
            padding: 14px 18px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-control-nexus:focus {
            outline: none;
            border-color: #00ff9d;
            box-shadow: 5px 5px 0 rgba(0, 255, 157, 0.2);
        }

        .btn-nexus-primary {
            background: #00ff9d;
            border: none;
            color: #0a0c0f;
            font-weight: 700;
            padding: 14px 32px;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
            clip-path: polygon(15px 0, 100% 0, calc(100% - 15px) 100%, 0 100%);
        }

        .btn-nexus-primary:hover {
            transform: translate(-3px, -3px);
            box-shadow: 8px 8px 0 rgba(0, 255, 157, 0.4);
        }

        /* ===== TABLA ===== */
        .table-wrapper {
            overflow-x: auto;
        }

        .nexus-table {
            width: 100%;
            border-collapse: collapse;
        }

        .nexus-table th {
            background: #0f1115;
            color: #00ff9d;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 18px 15px;
            border-bottom: 3px solid #00ff9d;
            text-align: left;
        }

        .nexus-table td {
            padding: 16px 15px;
            border-bottom: 1px solid #2a2e35;
            color: #e4e6eb;
        }

        .nexus-table tr:hover td {
            background: #1e2028;
        }

        .password-chip {
            background: #0a0c0f;
            border: 1px dashed #00ff9d;
            padding: 5px 10px;
            font-family: monospace;
            font-size: 0.9rem;
            color: #00ff9d;
            display: inline-block;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            clip-path: polygon(8px 0, 100% 0, calc(100% - 8px) 100%, 0 100%);
        }

        .status-active {
            background: #00ff9d;
            color: #0a0c0f;
        }

        .status-inactive {
            background: #ff3b3b;
            color: #0a0c0f;
        }

        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            border: none;
            padding: 8px 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            font-family: 'Space Grotesk', sans-serif;
        }

        .btn-action.delete {
            background: #ff3b3b;
            color: #0a0c0f;
        }

        .btn-action.delete:hover {
            background: #ff6b6b;
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0 rgba(255, 59, 59, 0.4);
        }

        .btn-action.activate {
            background: #00ff9d;
            color: #0a0c0f;
        }

        .btn-action.activate:hover {
            background: #33ffad;
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0 rgba(0, 255, 157, 0.4);
        }

        .btn-action.deactivate {
            background: #ffb86b;
            color: #0a0c0f;
        }

        .btn-action.deactivate:hover {
            background: #ffc98f;
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0 rgba(255, 184, 107, 0.4);
        }

        .btn-action:hover {
            transform: translate(-2px, -2px);
        }

        /* ===== FORMULARIOS OCULTOS PARA ACCIONES ===== */
        .action-form {
            display: inline-block;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .form-grid-nexus {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .nav-content {
                flex-direction: column;
                height: auto;
                padding: 15px 0;
                gap: 15px;
            }
            
            .page-header-nexus h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .form-grid-nexus {
                grid-template-columns: 1fr;
            }
            
            .action-group {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
            
            .action-form {
                width: 100%;
            }
            
            .action-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR NEO-BRUTALISTA ===== -->
<nav class="nexus-nav">
    <div class="nav-container">
        <div class="nav-content">
            <div class="logo-block">
                <div class="logo-marker">N</div>
                <span class="logo-text">NEXUS</span>
            </div>

            <div class="nav-actions-nexus">
                <a href="menu.php" class="btn-nexus">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    DASHBOARD
                </a>
                <a href="logout.php" class="btn-nexus logout">
                    <i class="bi bi-door-open-fill"></i>
                    SALIR
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<main class="nexus-container">
    <div class="page-header-nexus">
        <h1>CLIENTES</h1>
        <p>Gestión de usuarios de la plataforma</p>
    </div>

    <?php echo $mensaje; ?>

    <!-- FORMULARIO -->
    <div class="nexus-card">
        <div class="card-header-nexus">
            <i class="bi bi-person-plus-fill"></i>
            <h3>REGISTRAR NUEVO CLIENTE</h3>
        </div>
        <div class="card-body-nexus">
            <form method="POST" class="form-grid-nexus">
                <div class="form-group-nexus">
                    <label class="form-label-nexus">Nombre</label>
                    <input type="text" name="nombre" class="form-control-nexus" placeholder="Ej: Juan" required>
                </div>

                <div class="form-group-nexus">
                    <label class="form-label-nexus">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control-nexus" placeholder="Ej: Pérez García" required>
                </div>

                <div class="form-group-nexus">
                    <label class="form-label-nexus">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control-nexus" placeholder="usuario@ejemplo.com" required>
                </div>

                <div class="form-group-nexus">
                    <label class="form-label-nexus">Contraseña</label>
                    <input type="text" class="form-control-nexus" value="🔐 GENERACIÓN AUTOMÁTICA" disabled style="color: #00ff9d;">
                </div>

                <div style="grid-column: span 4; text-align: right; margin-top: 10px;">
                    <button type="submit" name="registrar" class="btn-nexus-primary">
                        <i class="bi bi-save-fill"></i>
                        REGISTRAR CLIENTE
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA -->
    <div class="nexus-card">
        <div class="card-header-nexus">
            <i class="bi bi-people-fill"></i>
            <h3>LISTADO DE CLIENTES</h3>
        </div>
        <div class="card-body-nexus" style="padding: 0;">
            <div class="table-wrapper">
                <table class="nexus-table">
                    <thead>
                        <tr>
                            <th>NOMBRE COMPLETO</th>
                            <th>CORREO</th>
                            <th>CONTRASEÑA</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $clientes->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-person-circle" style="color: #00ff9d; font-size: 1.2rem;"></i>
                                    <?php echo $fila['nombre'] . " " . $fila['apellidos']; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-envelope-fill" style="color: #6b8cff;"></i>
                                    <?php echo $fila['correo']; ?>
                                </div>
                            </td>
                            <td>
                                <span class="password-chip">
                                    <i class="bi bi-key-fill" style="margin-right: 5px;"></i>
                                    <?php echo $fila['password']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($fila['estatus'] == 1): ?>
                                    <span class="status-badge status-active">ACTIVO</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">INACTIVO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <!-- Formulario para eliminar (POST) -->
                                    <form method="POST" class="action-form" onsubmit="return confirm('¿Eliminar permanentemente este cliente?');">
                                        <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                        <input type="hidden" name="accion_cliente" value="eliminar">
                                        <button type="submit" class="btn-action delete">
                                            <i class="bi bi-trash-fill"></i>
                                            ELIMINAR
                                        </button>
                                    </form>

                                    <?php if($fila['estatus'] == 0): ?>
                                        <!-- Formulario para activar -->
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                            <input type="hidden" name="accion_cliente" value="activar">
                                            <button type="submit" class="btn-action activate">
                                                <i class="bi bi-eye-fill"></i>
                                                ACTIVAR
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Formulario para desactivar -->
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                            <input type="hidden" name="accion_cliente" value="desactivar">
                                            <button type="submit" class="btn-action deactivate">
                                                <i class="bi bi-eye-slash-fill"></i>
                                                DESACTIVAR
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>