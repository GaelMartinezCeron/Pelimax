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
        $mensaje = "<div class='alert alert-danger'>❌ " . $error_imagen . "</div>";
    } elseif ($imagen_final != "") {
        // CORRECCIÓN: Eliminado el campo 'reparto' que no existe en la tabla
        $sql = "INSERT INTO peliculas (titulo, genero, descripcion, trailer_url, anio, director, imagen_url, estatus) 
                VALUES ('$titulo', '$genero', '$descripcion', '$trailer_url', '$anio', '$director', '$imagen_final', 1)";
        if($con->query($sql)) {
            $mensaje = "<div class='alert alert-success'>✓ Película registrada con éxito.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error en la base de datos: " . $con->error . "</div>";
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
        $mensaje = "<div class='alert alert-danger'>❌ " . $error_imagen . "</div>";
    } else {
        $sql_update = "UPDATE peliculas SET titulo='$titulo', genero='$genero', descripcion='$descripcion', 
                       trailer_url='$trailer_url', anio='$anio', director='$director' $update_imagen 
                       WHERE id = $id_edit";
                       
        if ($con->query($sql_update)) {
            $mensaje = "<div class='alert alert-success'>✓ Datos actualizados correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al actualizar: " . $con->error . "</div>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudioFlow - Catálogo de Películas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family: 'Inter', sans-serif; min-height: 100vh;">

<!-- ===== NAVBAR ===== -->
<nav style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid rgba(255, 255, 255, 0.3);">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 30px;">
        <div style="display: flex; align-items: center; justify-content: space-between; height: 80px;">
            <!-- Logo -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <span style="font-size: 1.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">StudioFlow</span>
            </div>

            <!-- Acciones -->
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="menu.php" style="display: flex; align-items: center; gap: 8px; padding: 8px 20px; border-radius: 40px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; background: #f7fafc; color: #4a5568; border: 1px solid #e2e8f0;">
                    <i class="bi bi-grid-3x3-gap-fill" style="color: #667eea;"></i>
                    Dashboard
                </a>
                <a href="logout.php" style="background: #fff5f5; border: 1px solid #feb2b2; color: #e53e3e; padding: 8px 16px; border-radius: 40px; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                    <i class="bi bi-door-open-fill"></i>
                    Salir
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<main style="max-width: 1400px; margin: 40px auto; padding: 0 30px;">
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2rem; font-weight: 700; color: white; margin-bottom: 5px; display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-collection-play-fill" style="background: rgba(255, 255, 255, 0.2); padding: 10px; border-radius: 12px;"></i>
            Catálogo de Películas
        </h1>
        <p style="color: rgba(255, 255, 255, 0.8); font-size: 1rem; margin-left: 45px;">Administra todo el contenido de la plataforma</p>
    </div>

    <?php echo $mensaje; ?>

    <!-- Formulario para nueva película -->
    <div style="background: white; border-radius: 24px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);">
        <div style="font-size: 1.2rem; font-weight: 600; color: #2d3748; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">
            <i class="bi bi-plus-circle-fill" style="color: #667eea; font-size: 1.3rem;"></i>
            Agregar nueva película
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Título</label>
                    <input type="text" name="titulo" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; transition: all 0.3s ease;" placeholder="Ej: Inception" required>
                </div>
                
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Género</label>
                    <select name="genero" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;">
                        <option value="Accion">Acción</option>
                        <option value="Terror">Terror</option>
                        <option value="Animacion">Animación</option>
                        <option value="Familia">Familia</option>
                        <option value="Drama">Drama</option>
                        <option value="Comedia">Comedia</option>
                        <option value="Ciencia Ficción">Ciencia Ficción</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Año</label>
                    <input type="number" name="anio" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" placeholder="2024">
                </div>
                
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Director</label>
                    <input type="text" name="director" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" placeholder="Director">
                </div>
                
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Póster <span style="color: #f56565; font-size: 0.8rem;">(JPG, PNG, GIF, WEBP - Máx 5MB)</span></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="text" name="imagen_url_input" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" placeholder="URL de imagen">
                        <input type="file" name="imagen" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    </div>
                </div>
                
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Link del Tráiler (YouTube)</label>
                    <input type="url" name="trailer_url" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" placeholder="https://youtube.com/watch?v=...">
                </div>
                
                <div style="grid-column: span 4;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Sinopsis</label>
                    <textarea name="descripcion" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" rows="3" placeholder="Descripción de la película..." required></textarea>
                </div>
            </div>
            
            <div style="margin-top: 25px;">
                <button type="submit" name="registrar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; padding: 14px 28px; border-radius: 40px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease; border: 1px solid transparent; cursor: pointer; width: 100%;">
                    <i class="bi bi-save-fill"></i>
                    Guardar película
                </button>
            </div>
        </form>
    </div>

    <!-- Grid de películas -->
    <div style="margin-top: 30px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="color: white; font-weight: 700;">Películas registradas</h3>
            <span style="background: white; padding: 5px 15px; border-radius: 30px; font-weight: 600; color: #667eea;">
                <?php echo $result_peliculas->num_rows; ?> títulos
            </span>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php while($f = $result_peliculas->fetch_assoc()): ?>
            <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.5);">
                <div style="height: 200px; overflow: hidden; position: relative;">
                    <img src="<?php echo (filter_var($f['imagen_url'], FILTER_VALIDATE_URL)) ? $f['imagen_url'] : 'uploads/peliculas/'.$f['imagen_url']; ?>" 
                         alt="<?php echo $f['titulo']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: all 0.5s ease;"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Sin+Imagen'">
                    <div style="position: absolute; top: 10px; right: 10px; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; <?php echo ($f['estatus'] == 1) ? 'background: #48bb78; color: white;' : 'background: #f56565; color: white;'; ?>">
                        <?php echo ($f['estatus'] == 1) ? 'Activa' : 'Inactiva'; ?>
                    </div>
                </div>
                
                <div style="padding: 20px;">
                    <h4 style="font-size: 1.2rem; font-weight: 700; color: #2d3748; margin-bottom: 8px;"><?php echo $f['titulo']; ?></h4>
                    
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <span style="color: #718096; font-size: 0.9rem; background: #f7fafc; padding: 3px 10px; border-radius: 20px;"><?php echo $f['anio']; ?></span>
                        <span style="color: #667eea; font-size: 0.9rem; font-weight: 500;"><?php echo $f['genero']; ?></span>
                    </div>
                    
                    <div style="color: #718096; font-size: 0.9rem; margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                        <i class="bi bi-camera-reels-fill" style="color: #667eea;"></i>
                        <?php echo $f['director']; ?>
                    </div>
                    
                    <p style="color: #718096; font-size: 0.9rem; line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo $f['descripcion']; ?></p>
                    
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn-action" 
                                onclick='abrirEditar(<?php echo json_encode([
                                    'id' => $f['id'],
                                    'titulo' => $f['titulo'],
                                    'genero' => $f['genero'],
                                    'anio' => $f['anio'],
                                    'descripcion' => $f['descripcion'],
                                    'director' => $f['director'],
                                    'trailer' => $f['trailer_url']
                                ]); ?>)'
                                style="flex: 1; padding: 8px 0; border-radius: 30px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 5px; transition: all 0.3s ease; border: none; cursor: pointer; background: #ebf8ff; color: #3182ce;">
                            <i class="bi bi-pencil-fill"></i>
                            Editar
                        </button>
                        
                        <?php if($f['estatus'] == 1): ?>
                            <a href="peliculas.php?accion=desactivar&id=<?php echo $f['id']; ?>" style="flex: 1; padding: 8px 0; border-radius: 30px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 5px; transition: all 0.3s ease; border: none; cursor: pointer; text-decoration: none; background: #fff5f5; color: #e53e3e;">
                                <i class="bi bi-eye-slash-fill"></i>
                                Ocultar
                            </a>
                        <?php else: ?>
                            <a href="peliculas.php?accion=activar&id=<?php echo $f['id']; ?>" style="flex: 1; padding: 8px 0; border-radius: 30px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 5px; transition: all 0.3s ease; border: none; cursor: pointer; text-decoration: none; background: #f0fff4; color: #38a169;">
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
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 24px 24px 0 0; padding: 20px 25px; border: none;">
                <h5 class="modal-title" style="font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-pencil-square"></i>
                    Editar película
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id_pelicula" id="edit_id">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Título</label>
                        <input type="text" name="titulo" id="edit_titulo" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Género</label>
                            <select name="genero" id="edit_genero" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;">
                                <option value="Accion">Acción</option>
                                <option value="Terror">Terror</option>
                                <option value="Animacion">Animación</option>
                                <option value="Familia">Familia</option>
                                <option value="Drama">Drama</option>
                                <option value="Comedia">Comedia</option>
                                <option value="Ciencia Ficción">Ciencia Ficción</option>
                            </select>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Año</label>
                            <input type="number" name="anio" id="edit_anio" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Director</label>
                        <input type="text" name="director" id="edit_director" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Cambiar imagen <span style="color: #f56565; font-size: 0.8rem;">(JPG, PNG, GIF, WEBP - Máx 5MB)</span></label>
                        <input type="file" name="imagen_edit" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #718096; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Sinopsis</label>
                        <textarea name="descripcion" id="edit_desc" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem;" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="editar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; padding: 14px 28px; border-radius: 40px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease; border: 1px solid transparent; cursor: pointer; width: 100%;">
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
</script>
</body>
</html>