<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

$mensaje = "";

// --- 1. LÓGICA DE REGISTRO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_usuario'])) {
    $nombre = $con->real_escape_string($_POST['nombre']);
    $ap_paterno = $con->real_escape_string($_POST['apellido_paterno']);
    $ap_materno = $con->real_escape_string($_POST['apellido_materno']);
    $correo = $con->real_escape_string($_POST['correo']);
    $password = $con->real_escape_string($_POST['password']); 

    // Verificamos si el correo ya existe
    $check = $con->query("SELECT id FROM usuarios_admin WHERE correo = '$correo'");
    if ($check->num_rows > 0) {
        $mensaje = "<div class='alert-golden warning'>⚠️ El correo ya está registrado en el sistema.</div>";
    } else {
        $sql = "INSERT INTO usuarios_admin (nombre, apellido_paterno, apellido_materno, correo, password, estatus) 
                VALUES ('$nombre', '$ap_paterno', '$ap_materno', '$correo', '$password', 1)";
        if ($con->query($sql)) {
            $mensaje = "<div class='alert-golden success'>✓ Usuario administrativo registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert-golden error'>❌ Error al guardar: " . $con->error . "</div>";
        }
    }
}

// --- 2. LÓGICA DE ACCIONES: ELIMINAR / ACTIVAR (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion_usuario'])) {
    $id = intval($_POST['id']);
    
    // Evitar que el administrador maestro se elimine o desactive a sí mismo por accidente
    if ($id == $_SESSION['admin_id']) {
        $mensaje = "<div class='alert-golden error'>❌ No puedes alterar tu propio usuario actual.</div>";
    } else {
        if ($_POST['accion_usuario'] == 'eliminar') {
            $con->query("DELETE FROM usuarios_admin WHERE id = $id");
            $mensaje = "<div class='alert-golden info'>🗑️ Usuario eliminado permanentemente.</div>";
        } elseif ($_POST['accion_usuario'] == 'activar') {
            $con->query("UPDATE usuarios_admin SET estatus = 1 WHERE id = $id");
            $mensaje = "<div class='alert-golden success'>✓ Usuario activado correctamente.</div>";
        } elseif ($_POST['accion_usuario'] == 'desactivar') {
            $con->query("UPDATE usuarios_admin SET estatus = 0 WHERE id = $id");
            $mensaje = "<div class='alert-golden warning'>⚠️ Usuario desactivado.</div>";
        }
    }
}

// --- 3. CONSULTA PARA LA TABLA ---
$usuarios = $con->query("SELECT * FROM usuarios_admin ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Cinema - Gestión de Administradores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #0a0f1e 0%, #1a1f2e 100%);
            color: #fff;
            min-height: 100vh;
        }

        /* ===== NAVBAR PREMIUM ===== */
        .navbar-premium {
            background: rgba(10, 15, 30, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #d4af37;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #0a0f1e;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-actions {
            display: flex;
            gap: 15px;
        }

        .btn-golden {
            background: transparent;
            border: 2px solid #d4af37;
            color: #d4af37;
            padding: 8px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-golden:hover {
            background: #d4af37;
            color: #0a0f1e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-golden.logout {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        .btn-golden.logout:hover {
            background: #ff6b6b;
            color: #0a0f1e;
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .main-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header h1 i {
            background: rgba(212, 175, 55, 0.1);
            padding: 15px;
            border-radius: 50%;
            color: #d4af37;
        }

        .page-header p {
            color: #8a909e;
            font-size: 1.1rem;
            margin-left: 60px;
        }

        /* ===== ALERTAS GOLDEN ===== */
        .alert-golden {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
            border-left: 6px solid;
            background: #1a1f2e;
            color: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .alert-golden.success { border-color: #4caf50; }
        .alert-golden.error { border-color: #ff6b6b; }
        .alert-golden.warning { border-color: #ffb86b; }
        .alert-golden.info { border-color: #6b8cff; }

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

        /* ===== TARJETA DE FORMULARIO ===== */
        .card-golden {
            background: #1a1f2e;
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 20px;
            margin-bottom: 40px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .card-header-golden {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(0, 0, 0, 0.2);
        }

        .card-header-golden i {
            font-size: 1.5rem;
            color: #d4af37;
        }

        .card-header-golden h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #d4af37;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-body-golden {
            padding: 25px;
        }

        /* ===== FORMULARIO ===== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #8a909e;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            background: #0a0f1e;
            border: 2px solid #2a2f3e;
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        }

        .form-control::placeholder {
            color: #4a5568;
        }

        .full-width {
            grid-column: span 2;
        }

        .btn-golden-primary {
            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
            border: none;
            color: #0a0f1e;
            font-weight: 700;
            padding: 14px 32px;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 40px;
            width: 100%;
        }

        .btn-golden-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
        }

        /* ===== TABLA ===== */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 20px;
        }

        .table-golden {
            width: 100%;
            border-collapse: collapse;
            background: #1a1f2e;
        }

        .table-golden th {
            background: #0f1322;
            color: #d4af37;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 18px 15px;
            border-bottom: 2px solid #d4af37;
            text-align: left;
        }

        .table-golden td {
            padding: 16px 15px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            color: #e2e8f0;
        }

        .table-golden tr:hover td {
            background: rgba(212, 175, 55, 0.05);
        }

        .badge-golden {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-active {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .badge-inactive {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }

        .badge-current {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            border: 1px solid #d4af37;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
        }

        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            border: none;
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid transparent;
        }

        .btn-action.delete {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border-color: #ff6b6b;
        }

        .btn-action.delete:hover {
            background: #ff6b6b;
            color: #0a0f1e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-action.activate {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border-color: #4caf50;
        }

        .btn-action.activate:hover {
            background: #4caf50;
            color: #0a0f1e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-action.deactivate {
            background: rgba(255, 184, 107, 0.1);
            color: #ffb86b;
            border-color: #ffb86b;
        }

        .btn-action.deactivate:hover {
            background: #ffb86b;
            color: #0a0f1e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 184, 107, 0.3);
        }

        .action-form {
            display: inline-block;
        }

        /* ===== ANIMACIONES ===== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-golden, .table-wrapper {
            animation: fadeIn 0.5s ease-out;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .navbar-content {
                flex-direction: column;
                height: auto;
                padding: 15px 0;
                gap: 15px;
            }
            
            .page-header h1 {
                font-size: 2rem;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header p {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .full-width {
                grid-column: span 1;
            }
            
            .action-group {
                flex-direction: column;
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

<!-- ===== NAVBAR PREMIUM ===== -->
<nav class="navbar-premium">
    <div class="navbar-container">
        <div class="navbar-content">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <span class="logo-text">GOLDEN ADMIN</span>
            </div>

            <div class="nav-actions">
                <a href="menu.php" class="btn-golden">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    DASHBOARD
                </a>
                <a href="logout.php" class="btn-golden logout">
                    <i class="bi bi-door-open-fill"></i>
                    SALIR
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<main class="main-container">
    <div class="page-header">
        <h1>
            <i class="bi bi-people-fill"></i>
            Gestión de Administradores
        </h1>
        <p>Controla los accesos al panel administrativo de Golden Cinema</p>
    </div>

    <?php echo $mensaje; ?>

    <!-- FORMULARIO DE REGISTRO -->
    <div class="card-golden">
        <div class="card-header-golden">
            <i class="bi bi-person-plus-fill"></i>
            <h3>Registrar Nuevo Administrador</h3>
        </div>
        <div class="card-body-golden">
            <form method="POST" action="" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" class="form-control" placeholder="Ej: Pérez" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Apellido Materno</label>
                    <input type="text" name="apellido_materno" class="form-control" placeholder="Ej: García" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" placeholder="admin@example.com" required>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Contraseña</label>
                    <input type="text" name="password" class="form-control" placeholder="Ingresa una contraseña segura" required>
                </div>
                
                <div style="grid-column: span 4; margin-top: 10px;">
                    <button type="submit" name="registrar_usuario" class="btn-golden-primary">
                        <i class="bi bi-save-fill"></i>
                        REGISTRAR ADMINISTRADOR
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA DE USUARIOS -->
    <div class="card-golden">
        <div class="card-header-golden">
            <i class="bi bi-list-ul"></i>
            <h3>Administradores Registrados</h3>
        </div>
        <div class="card-body-golden" style="padding: 0;">
            <div class="table-wrapper">
                <table class="table-golden">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $usuarios->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-person-badge-fill" style="color: #d4af37;"></i>
                                    <?php echo $fila['nombre'] . " " . $fila['apellido_paterno'] . " " . $fila['apellido_materno']; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-envelope-fill" style="color: #6b8cff;"></i>
                                    <?php echo $fila['correo']; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="bi bi-calendar-event-fill" style="color: #d4af37;"></i>
                                    <?php echo date("d/m/Y H:i", strtotime($fila['fecha_registro'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php if($fila['id'] == $_SESSION['admin_id']): ?>
                                    <span class="badge-current">
                                        <i class="bi bi-star-fill"></i> SESIÓN ACTUAL
                                    </span>
                                <?php else: ?>
                                    <?php if($fila['estatus'] == 1): ?>
                                        <span class="badge-golden badge-active">
                                            <i class="bi bi-check-circle-fill"></i> ACTIVO
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-golden badge-inactive">
                                            <i class="bi bi-x-circle-fill"></i> INACTIVO
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($fila['id'] != $_SESSION['admin_id']): ?>
                                    <div class="action-group">
                                        <!-- Formulario eliminar -->
                                        <form method="POST" class="action-form" onsubmit="return confirm('¿Eliminar permanentemente este administrador?');">
                                            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                            <input type="hidden" name="accion_usuario" value="eliminar">
                                            <button type="submit" class="btn-action delete">
                                                <i class="bi bi-trash-fill"></i>
                                                ELIMINAR
                                            </button>
                                        </form>

                                        <?php if($fila['estatus'] == 0): ?>
                                            <!-- Formulario activar -->
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                                <input type="hidden" name="accion_usuario" value="activar">
                                                <button type="submit" class="btn-action activate">
                                                    <i class="bi bi-eye-fill"></i>
                                                    ACTIVAR
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Formulario desactivar -->
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                                <input type="hidden" name="accion_usuario" value="desactivar">
                                                <button type="submit" class="btn-action deactivate">
                                                    <i class="bi bi-eye-slash-fill"></i>
                                                    DESACTIVAR
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge-current">TÚ</span>
                                <?php endif; ?>
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