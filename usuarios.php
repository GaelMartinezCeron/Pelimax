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
        $mensaje = "<div class='alert alert-warning'>El correo ya está registrado en el sistema.</div>";
    } else {
        $sql = "INSERT INTO usuarios_admin (nombre, apellido_paterno, apellido_materno, correo, password, estatus) 
                VALUES ('$nombre', '$ap_paterno', '$ap_materno', '$correo', '$password', 1)";
        if ($con->query($sql)) {
            $mensaje = "<div class='alert alert-success'>Usuario administrativo registrado correctamente.</div>";
        } 
        
        
        else {
            $mensaje = "<div class='alert alert-danger'>Error al guardar: " . $con->error . "</div>";
        }
    }
}

// --- 2. LÓGICA DE ACCIONES: ELIMINAR / ACTIVAR (GET) ---
if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id = intval($_GET['id']);
    
    // Evitar que el administrador maestro se elimine o desactive a sí mismo por accidente
    if ($id == $_SESSION['admin_id']) {
        $mensaje = "<div class='alert alert-danger'>No puedes alterar tu propio usuario actual.</div>";
    } else {
        if ($_GET['accion'] == 'eliminar') {
            $con->query("DELETE FROM usuarios_admin WHERE id = $id");
            $mensaje = "<div class='alert alert-info'>Usuario eliminado.</div>";
        } elseif ($_GET['accion'] == 'activar') {
            $con->query("UPDATE usuarios_admin SET estatus = 1 WHERE id = $id");
        } elseif ($_GET['accion'] == 'desactivar') {
            $con->query("UPDATE usuarios_admin SET estatus = 0 WHERE id = $id");
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
    <title>Registro de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">

<nav class="navbar navbar-dark bg-dark mb-4 shadow">
    <div class="container-fluid">
        <span class="navbar-brand">Administración de Usuarios (Web)</span>
        <a href="menu.php" class="btn btn-outline-light btn-sm fw-bold">Volver al Menú</a>
    </div>
</nav>

<div class="container-fluid px-4">
    <?php echo $mensaje; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold text-dark">
            Registrar Nuevo Usuario (Acceso Web)
        </div>
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Apellido Materno</label>
                    <input type="text" name="apellido_materno" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted">Correo Electrónico (Usuario)</label>
                    <input type="email" name="correo" class="form-control" required placeholder="ejemplo@streaming.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted">Clave</label>
                    <input type="text" name="password" class="form-control" required placeholder="Asigna una contraseña">
                </div>
                <div class="col-12 text-end mt-4">
                    <button type="submit" name="registrar_usuario" class="btn btn-primary px-5 fw-bold">Registrar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">
            Usuarios Registrados
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-secondary small">
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Correo / Usuario</th>
                            <th>Fecha de Registro</th>
                            <th>Estatus</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $usuarios->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold">
                                <?php echo $fila['nombre'] . " " . $fila['apellido_paterno'] . " " . $fila['apellido_materno']; ?>
                            </td>
                            <td><?php echo $fila['correo']; ?></td>
                            <td class="small text-muted">
                                <?php echo date("d/m/Y H:i", strtotime($fila['fecha_registro'])); ?>
                            </td>
                            <td>
                                <?php if($fila['estatus'] == 1): ?>
                                    <span class="badge bg-success rounded-pill">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($fila['id'] != $_SESSION['admin_id']): ?>
                                    <a href="usuarios.php?accion=eliminar&id=<?php echo $fila['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar usuario definitivamente?');">Eliminar</a>
                                    
                                    <?php if($fila['estatus'] == 0): ?>
                                        <a href="usuarios.php?accion=activar&id=<?php echo $fila['id']; ?>" class="btn btn-sm btn-primary">Activar</a>
                                    <?php else: ?>
                                        <a href="usuarios.php?accion=desactivar&id=<?php echo $fila['id']; ?>" class="btn btn-sm btn-secondary">Desactivar</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Tú (Sesión Actual)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>