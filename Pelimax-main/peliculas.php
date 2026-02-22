<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
include 'conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$mensaje = "";

// --- 1. LÓGICA PARA REGISTRAR ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
    // CORRECCIÓN: Usar null coalescing operator para evitar null en real_escape_string
    $titulo = isset($_POST['titulo']) ? $con->real_escape_string($_POST['titulo']) : '';
    $genero = isset($_POST['genero']) ? $con->real_escape_string($_POST['genero']) : '';
    $descripcion = isset($_POST['descripcion']) ? $con->real_escape_string($_POST['descripcion']) : '';
    $trailer_url = isset($_POST['trailer_url']) ? $con->real_escape_string($_POST['trailer_url']) : '';
    $anio = isset($_POST['anio']) ? $con->real_escape_string($_POST['anio']) : '';
    $director = isset($_POST['director']) ? $con->real_escape_string($_POST['director']) : '';
    
    $imagen_final = "";
    $error_imagen = "";
    
    // Prioridad a la URL
    if (!empty($_POST['imagen_url_input'])) {
        $imagen_final = $con->real_escape_string($_POST['imagen_url_input']);
    } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['imagen']['type'];
        $file_size = $_FILES['imagen']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validar extensión por seguridad adicional
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_type, $allowed_types) || !in_array($extension, $allowed_extensions)) {
            $error_imagen = "Solo se permiten imágenes (JPG, PNG, GIF, WEBP)";
        } elseif ($file_size > $max_size) {
            $error_imagen = "La imagen no puede ser mayor a 5MB";
        } else {
            // Crear carpeta si no existe
            if (!file_exists("uploads/peliculas/")) {
                mkdir("uploads/peliculas/", 0777, true);
            }
            
            $nombre_generado = time() . "_" . uniqid() . "." . $extension;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], "uploads/peliculas/" . $nombre_generado)) {
                $imagen_final = $nombre_generado;
            } else {
                $error_imagen = "Error al subir la imagen";
            }
        }
    } else {
        $error_imagen = "Debes proporcionar una imagen (URL o archivo)";
    }

    if ($error_imagen != "") {
        $mensaje = "<div class='alert-moderno alert-error'>❌ " . $error_imagen . "</div>";
    } elseif ($imagen_final != "") {
        // CORRECCIÓN: Eliminado el campo 'reparto' que no existe en la tabla
        $sql = "INSERT INTO peliculas (titulo, genero, descripcion, trailer_url, anio, director, imagen_url, estatus) 
                VALUES ('$titulo', '$genero', '$descripcion', '$trailer_url', '$anio', '$director', '$imagen_final', 1)";
        if($con->query($sql)) {
            $mensaje = "<div class='alert-moderno alert-success'>✓ Película registrada con éxito.</div>";
        } else {
            $mensaje = "<div class='alert-moderno alert-error'>Error en la base de datos: " . $con->error . "</div>";
        }
    }
}

// --- 2. LÓGICA PARA ACTUALIZAR ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id_edit = intval($_POST['id_pelicula']);
    
    // CORRECCIÓN: Usar null coalescing operator
    $titulo = isset($_POST['titulo']) ? $con->real_escape_string($_POST['titulo']) : '';
    $genero = isset($_POST['genero']) ? $con->real_escape_string($_POST['genero']) : '';
    $descripcion = isset($_POST['descripcion']) ? $con->real_escape_string($_POST['descripcion']) : '';
    $trailer_url = isset($_POST['trailer_url']) ? $con->real_escape_string($_POST['trailer_url']) : '';
    $anio = isset($_POST['anio']) ? $con->real_escape_string($_POST['anio']) : '';
    $director = isset($_POST['director']) ? $con->real_escape_string($_POST['director']) : '';
    
    $update_imagen = "";
    $error_imagen = "";
    
    if (isset($_FILES['imagen_edit']) && $_FILES['imagen_edit']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['imagen_edit']['type'];
        $file_size = $_FILES['imagen_edit']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validar extensión
        $extension = strtolower(pathinfo($_FILES['imagen_edit']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_type, $allowed_types) || !in_array($extension, $allowed_extensions)) {
            $error_imagen = "Solo se permiten imágenes (JPG, PNG, GIF, WEBP)";
        } elseif ($file_size > $max_size) {
            $error_imagen = "La imagen no puede ser mayor a 5MB";
        } else {
            if (!file_exists("uploads/peliculas/")) {
                mkdir("uploads/peliculas/", 0777, true);
            }
            
            $nombre_final = time() . "_" . uniqid() . "." . $extension;
            if (move_uploaded_file($_FILES['imagen_edit']['tmp_name'], "uploads/peliculas/" . $nombre_final)) {
                $update_imagen = ", imagen_url = '$nombre_final'";
            } else {
                $error_imagen = "Error al subir la imagen";
            }
        }
    }

    if ($error_imagen != "") {
        $mensaje = "<div class='alert-moderno alert-error'>❌ " . $error_imagen . "</div>";
    } else {
        $sql_update = "UPDATE peliculas SET titulo='$titulo', genero='$genero', descripcion='$descripcion', 
                       trailer_url='$trailer_url', anio='$anio', director='$director' $update_imagen 
                       WHERE id = $id_edit";
                       
        if ($con->query($sql_update)) {
            $mensaje = "<div class='alert-moderno alert-success'>✓ Datos actualizados correctamente.</div>";
        } else {
            $mensaje = "<div class='alert-moderno alert-error'>Error al actualizar: " . $con->error . "</div>";
        }
    }
}

// --- 3. LÓGICA PARA ESTADO ---
if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id = intval($_GET['id']);
    $st = ($_GET['accion'] == 'activar') ? 1 : 0;
    $con->query("UPDATE peliculas SET estatus = $st WHERE id = $id");
    header("Location: peliculas.php");
    exit();
}

$result_peliculas = $con->query("SELECT * FROM peliculas ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>StudioFlow - Catálogo de Películas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            width: 100%;
        }

        /* ===== ALERTAS ===== */
        .alert-moderno {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
            color: white;
            width: 100%;
            flex-wrap: wrap;
        }

        .alert-moderno.alert-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        }

        .alert-moderno.alert-error {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
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

        @media (max-width: 480px) {
            .alert-moderno {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
        }

        /* ===== NAVBAR ===== */
        .navbar-moderno {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
        }

        .navbar-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
            flex-wrap: wrap;
        }

        @media (max-width: 992px) {
            .navbar-content {
                flex-direction: column;
                padding: 15px 0;
                gap: 15px;
            }
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 480px) {
            .logo-icon {
                width: 35px;
                height: 35px;
                font-size: 1.2rem;
            }
            
            .logo-text {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 360px) {
            .logo-icon {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }
            
            .logo-text {
                font-size: 1rem;
            }
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .btn-nav i {
            color: #667eea;
        }

        .btn-nav:hover {
            background: white;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.1);
        }

        .btn-logout {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            color: #e53e3e;
            padding: 8px 16px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-logout:hover {
            background: #fed7d7;
            border-color: #fc8181;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(229, 62, 62, 0.2);
        }

        @media (max-width: 480px) {
            .btn-nav, .btn-logout {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .btn-nav i, .btn-logout i {
                font-size: 0.9rem;
            }
        }

        /* ===== CONTENIDO PRINCIPAL ===== */
        .main-content {
            width: 100%;
            max-width: 1400px;
            margin: 20px auto 40px;
            padding: 0 15px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin: 15px auto 30px;
            }
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .page-header h1 i {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 12px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-left: 45px;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header p {
                margin-left: 0;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .page-header h1 i {
                padding: 8px;
                font-size: 1.2rem;
            }
            
            .page-header p {
                font-size: 0.9rem;
            }
        }

        /* ===== TARJETA DE FORMULARIO ===== */
        .card-form {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        @media (max-width: 768px) {
            .card-form {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .card-form {
                padding: 15px;
                border-radius: 16px;
            }
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
            flex-wrap: wrap;
        }

        .card-title i {
            color: #667eea;
            font-size: 1.3rem;
        }

        /* ===== FORMULARIO ===== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        .form-group {
            width: 100%;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #718096;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
            color: #2d3748;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        @media (max-width: 480px) {
            .form-control, .form-select {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }

        .full-width {
            grid-column: span 2;
        }

        @media (max-width: 576px) {
            .full-width {
                grid-column: span 1;
            }
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 480px) {
            .btn-primary {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        /* ===== GRID DE PELÍCULAS ===== */
        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .movies-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        .movie-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.5);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px rgba(102, 126, 234, 0.15);
        }

        .movie-poster {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .movie-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }

        .movie-card:hover .movie-poster img {
            transform: scale(1.1);
        }

        .movie-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 1;
            white-space: nowrap;
        }

        .status-active {
            background: #48bb78;
            color: white;
        }

        .status-inactive {
            background: #f56565;
            color: white;
        }

        .movie-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .movie-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .movie-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .movie-year {
            color: #718096;
            font-size: 0.85rem;
            background: #f7fafc;
            padding: 3px 8px;
            border-radius: 20px;
        }

        .movie-genre {
            color: #667eea;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .movie-director {
            color: #718096;
            font-size: 0.85rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .movie-director i {
            color: #667eea;
        }

        .movie-description {
            color: #718096;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .movie-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
            flex-wrap: wrap;
        }

        @media (max-width: 480px) {
            .movie-actions {
                flex-direction: column;
            }
        }

        .btn-action {
            flex: 1;
            padding: 8px 0;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            min-width: 0;
        }

        .btn-edit {
            background: #ebf8ff;
            color: #3182ce;
        }

        .btn-edit:hover {
            background: #3182ce;
            color: white;
        }

        .btn-toggle {
            background: #fff5f5;
            color: #e53e3e;
        }

        .btn-toggle.active {
            background: #f0fff4;
            color: #38a169;
        }

        /* ===== MODAL ===== */
        .modal-moderno .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            margin: 15px;
        }

        .modal-moderno .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 15px 20px;
            border: none;
        }

        .modal-moderno .modal-title {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
        }

        .modal-moderno .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-moderno .modal-body {
            padding: 20px;
        }

        @media (max-width: 480px) {
            .modal-moderno .modal-header {
                padding: 12px 15px;
            }
            
            .modal-moderno .modal-title {
                font-size: 1rem;
            }
            
            .modal-moderno .modal-body {
                padding: 15px;
            }
        }

        /* ===== HEADER INFO ===== */
        .header-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-info h3 {
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .total-badge {
            background: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
            color: #667eea;
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .header-info h3 {
                font-size: 1.1rem;
            }
            
            .total-badge {
                padding: 4px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar-moderno">
    <div class="navbar-container">
        <div class="navbar-content">
            <!-- Logo -->
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <span class="logo-text">StudioFlow</span>
            </div>

            <!-- Acciones -->
            <div class="nav-actions">
                <a href="menu.php" class="btn-nav">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="bi bi-door-open-fill"></i>
                    <span>Salir</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<main class="main-content">
    <div class="page-header">
        <h1>
            <i class="bi bi-collection-play-fill"></i>
            Catálogo de Películas
        </h1>
        <p>Administra todo el contenido de la plataforma</p>
    </div>

    <?php echo $mensaje; ?>

    <!-- Formulario para nueva película -->
    <div class="card-form">
        <div class="card-title">
            <i class="bi bi-plus-circle-fill"></i>
            Agregar nueva película
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Inception" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Género</label>
                    <select name="genero" class="form-select">
                        <option value="Accion">Acción</option>
                        <option value="Terror">Terror</option>
                        <option value="Animacion">Animación</option>
                        <option value="Familia">Familia</option>
                        <option value="Drama">Drama</option>
                        <option value="Comedia">Comedia</option>
                        <option value="Ciencia Ficción">Ciencia Ficción</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Año</label>
                    <input type="number" name="anio" class="form-control" placeholder="2024">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Director</label>
                    <input type="text" name="director" class="form-control" placeholder="Director">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Póster <span style="color: #f56565; font-size: 0.7rem;">(JPG, PNG, GIF, WEBP - Máx 5MB)</span></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="text" name="imagen_url_input" class="form-control" placeholder="URL de imagen">
                        <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Link del Tráiler (YouTube)</label>
                    <input type="url" name="trailer_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Sinopsis</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción de la película..." required></textarea>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" name="registrar" class="btn-primary">
                    <i class="bi bi-save-fill"></i>
                    Guardar película
                </button>
            </div>
        </form>
    </div>

    <!-- Grid de películas -->
    <div style="margin-top: 30px;">
        <div class="header-info">
            <h3>Películas registradas</h3>
            <span class="total-badge">
                <?php echo $result_peliculas->num_rows; ?> títulos
            </span>
        </div>
        
        <div class="movies-grid">
            <?php while($f = $result_peliculas->fetch_assoc()): ?>
            <div class="movie-card">
                <div class="movie-poster">
                    <img src="<?php echo (filter_var($f['imagen_url'], FILTER_VALIDATE_URL)) ? $f['imagen_url'] : 'uploads/peliculas/'.$f['imagen_url']; ?>" 
                         alt="<?php echo $f['titulo']; ?>"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Sin+Imagen'">
                    <div class="movie-status <?php echo ($f['estatus'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ($f['estatus'] == 1) ? 'Activa' : 'Inactiva'; ?>
                    </div>
                </div>
                
                <div class="movie-info">
                    <h4 class="movie-title"><?php echo $f['titulo']; ?></h4>
                    
                    <div class="movie-meta">
                        <span class="movie-year"><?php echo $f['anio']; ?></span>
                        <span class="movie-genre"><?php echo $f['genero']; ?></span>
                    </div>
                    
                    <div class="movie-director">
                        <i class="bi bi-camera-reels-fill"></i>
                        <?php echo $f['director']; ?>
                    </div>
                    
                    <p class="movie-description"><?php echo $f['descripcion']; ?></p>
                    
                    <div class="movie-actions">
                        <button class="btn-action btn-edit" 
                                onclick='abrirEditar(<?php echo json_encode([
                                    'id' => $f['id'],
                                    'titulo' => $f['titulo'],
                                    'genero' => $f['genero'],
                                    'anio' => $f['anio'],
                                    'descripcion' => $f['descripcion'],
                                    'director' => $f['director'],
                                    'trailer' => $f['trailer_url']
                                ]); ?>)'>
                            <i class="bi bi-pencil-fill"></i>
                            Editar
                        </button>
                        
                        <?php if($f['estatus'] == 1): ?>
                            <a href="peliculas.php?accion=desactivar&id=<?php echo $f['id']; ?>" class="btn-action btn-toggle">
                                <i class="bi bi-eye-slash-fill"></i>
                                Ocultar
                            </a>
                        <?php else: ?>
                            <a href="peliculas.php?accion=activar&id=<?php echo $f['id']; ?>" class="btn-action btn-toggle active">
                                <i class="bi bi-eye-fill"></i>
                                Mostrar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<!-- Modal de edición -->
<div class="modal fade modal-moderno" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i>
                    Editar película
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id_pelicula" id="edit_id">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group">
                            <label class="form-label">Género</label>
                            <select name="genero" id="edit_genero" class="form-select">
                                <option value="Accion">Acción</option>
                                <option value="Terror">Terror</option>
                                <option value="Animacion">Animación</option>
                                <option value="Familia">Familia</option>
                                <option value="Drama">Drama</option>
                                <option value="Comedia">Comedia</option>
                                <option value="Ciencia Ficción">Ciencia Ficción</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Año</label>
                            <input type="number" name="anio" id="edit_anio" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="form-label">Director</label>
                        <input type="text" name="director" id="edit_director" class="form-control">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="form-label">Cambiar imagen <span style="color: #f56565; font-size: 0.7rem;">(JPG, PNG, GIF, WEBP - Máx 5MB)</span></label>
                        <input type="file" name="imagen_edit" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="form-label">Sinopsis</label>
                        <textarea name="descripcion" id="edit_desc" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="editar" class="btn-primary" style="width: 100%;">
                        <i class="bi bi-check-lg"></i>
                        Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicializar modal
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
    
    // Función para abrir modal de edición
    function abrirEditar(pelicula) {
        document.getElementById('edit_id').value = pelicula.id;
        document.getElementById('edit_titulo').value = pelicula.titulo;
        document.getElementById('edit_genero').value = pelicula.genero;
        document.getElementById('edit_anio').value = pelicula.anio;
        document.getElementById('edit_desc').value = pelicula.descripcion;
        document.getElementById('edit_director').value = pelicula.director;
        modalEditar.show();
    }

    // Marcar link activo en navbar
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.btn-nav');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage) {
                link.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                link.style.color = 'white';
                link.style.border = 'none';
                link.querySelector('i').style.color = 'white';
            }
        });
    });
</script>
</body>
</html>