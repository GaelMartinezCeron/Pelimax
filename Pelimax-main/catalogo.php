<?php
session_start();
include 'conexion.php';

// EL NUEVO CANDADO INTELIGENTE: Deja entrar si eres Cliente O si eres Administrador
if (!isset($_SESSION['cliente_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Identificamos quién entró para saludarlo por su nombre
$nombre_usuario = isset($_SESSION['cliente_nombre']) ? $_SESSION['cliente_nombre'] : $_SESSION['admin_nombre'];

$sql = "SELECT * FROM peliculas WHERE estatus = 1 ORDER BY id DESC";
$resultado = $con->query($sql);

$peliculas_por_genero = [];
$peliculas_destacadas = []; // Para el carrusel automático

if ($resultado && $resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $genero = $row['genero'];
        if (empty($genero)) $genero = "Otros"; 
        $peliculas_por_genero[$genero][] = $row;
        
        // Tomar las primeras 10 como destacadas para el carrusel
        if (count($peliculas_destacadas) < 10) {
            $peliculas_destacadas[] = $row;
        }
    }
}

function convertirAEmbed($url) {
    if (empty($url)) return "";
    if (strpos($url, 'watch?v=') !== false) {
        $partes = explode('watch?v=', $url);
        return "https://www.youtube.com/embed/" . substr($partes[1], 0, 11);
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $partes = explode('youtu.be/', $url);
        return "https://www.youtube.com/embed/" . substr($partes[1], 0, 11);
    }
    return $url;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Golden Cinema - Catálogo</title>
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
            overflow-x: hidden;
            width: 100%;
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

        .search-box {
            flex: 0 1 400px;
        }

        @media (max-width: 992px) {
            .search-box {
                width: 100%;
                flex: none;
            }
        }

        .search-input {
            width: 100%;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 40px;
            color: #fff;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #d4af37;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        }

        .search-input::placeholder {
            color: #8a909e;
        }

        @media (max-width: 480px) {
            .search-input {
                padding: 8px 14px;
                font-size: 0.85rem;
            }
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .user-area {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 480px) {
            .user-area {
                flex-direction: column;
                gap: 10px;
            }
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 40px;
        }

        .user-welcome i {
            color: #d4af37;
            font-size: 1rem;
        }

        .user-welcome span {
            color: #fff;
            font-weight: 500;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .user-welcome {
                padding: 5px 12px;
            }
            
            .user-welcome i {
                font-size: 0.9rem;
            }
            
            .user-welcome span {
                font-size: 0.8rem;
            }
        }

        .btn-golden {
            background: transparent;
            border: 2px solid #d4af37;
            color: #d4af37;
            padding: 6px 16px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-golden:hover {
            background: #d4af37;
            color: #0a0f1e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-golden.outline-light {
            border-color: #8a909e;
            color: #8a909e;
        }

        .btn-golden.outline-light:hover {
            background: #8a909e;
            color: #0a0f1e;
        }

        @media (max-width: 480px) {
            .btn-golden {
                padding: 5px 12px;
                font-size: 0.8rem;
            }
        }

        /* ===== CARRUSEL AUTOMÁTICO ===== */
        .hero-section {
            width: 100%;
            max-width: 1400px;
            margin: 20px auto 40px;
            padding: 0 15px;
        }

        .hero-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hero-title i {
            color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
            padding: 8px;
            border-radius: 50%;
            font-size: 1.3rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.2rem;
            }
        }

        .carousel-container {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            width: 100%;
        }

        .carousel-slide {
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
            transition: transform 8s ease;
        }

        .carousel-slide:hover img {
            transform: scale(1.1);
        }

        /* ===== RESPONSIVE CARRUSEL ===== */
        @media (max-width: 1200px) {
            .carousel-slide {
                height: 450px;
            }
        }

        @media (max-width: 992px) {
            .carousel-slide {
                height: 400px;
            }
        }

        @media (max-width: 768px) {
            .carousel-slide {
                height: 350px;
            }
        }

        @media (max-width: 576px) {
            .carousel-slide {
                height: 300px;
            }
        }

        @media (max-width: 480px) {
            .carousel-slide {
                height: 250px;
            }
        }

        @media (max-width: 360px) {
            .carousel-slide {
                height: 200px;
            }
        }

        .carousel-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(to top, rgba(10, 15, 30, 0.9), transparent);
            color: white;
        }

        .carousel-content h3 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: #d4af37;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .carousel-content p {
            font-size: 1rem;
            max-width: 600px;
            margin-bottom: 15px;
            color: #e2e8f0;
        }

        .carousel-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #d4af37;
            color: #0a0f1e;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 8px;
        }

        @media (max-width: 992px) {
            .carousel-content {
                padding: 30px;
            }
            
            .carousel-content h3 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .carousel-content {
                padding: 25px;
            }
            
            .carousel-content h3 {
                font-size: 1.5rem;
            }
            
            .carousel-content p {
                font-size: 0.9rem;
                margin-bottom: 10px;
            }
            
            .carousel-badge {
                padding: 3px 10px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .carousel-content {
                padding: 20px;
            }
            
            .carousel-content h3 {
                font-size: 1.2rem;
            }
            
            .carousel-content p {
                font-size: 0.8rem;
                display: none; /* Ocultar descripción en móviles muy pequeños */
            }
            
            .carousel-badge {
                padding: 2px 8px;
                font-size: 0.65rem;
            }
        }

        .carousel-indicators-custom {
            position: absolute;
            bottom: 15px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-dot.active {
            background: #d4af37;
            transform: scale(1.2);
        }

        @media (max-width: 576px) {
            .carousel-indicators-custom {
                bottom: 10px;
                right: 15px;
                gap: 5px;
            }
            
            .carousel-dot {
                width: 8px;
                height: 8px;
            }
        }

        /* ===== CATEGORÍAS ===== */
        .catalogo-section {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto 40px;
            padding: 0 15px;
        }

        .categoria-contenedor {
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .categoria-contenedor {
                margin-bottom: 30px;
            }
        }

        .titulo-categoria {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
            padding-left: 12px;
            border-left: 4px solid #d4af37;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .titulo-categoria i {
            color: #d4af37;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .titulo-categoria {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .titulo-categoria {
                font-size: 1.1rem;
                padding-left: 8px;
            }
            
            .titulo-categoria i {
                font-size: 1rem;
            }
        }

        /* ===== FILA DE PELÍCULAS CON SCROLL MEJORADO ===== */
        .fila-genero {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0 15px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #d4af37 rgba(212, 175, 55, 0.1);
        }

        .fila-genero::-webkit-scrollbar {
            height: 6px;
        }

        .fila-genero::-webkit-scrollbar-track {
            background: rgba(212, 175, 55, 0.1);
            border-radius: 10px;
        }

        .fila-genero::-webkit-scrollbar-thumb {
            background: #d4af37;
            border-radius: 10px;
        }

        .fila-genero::-webkit-scrollbar-thumb:hover {
            background: #f5d742;
        }

        /* ===== TARJETAS CON TAMAÑOS RESPONSIVE ===== */
        .tarjeta-carrusel {
            flex: 0 0 auto;
            width: 200px;
            background: #1a1f2e;
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .tarjeta-carrusel:hover {
            transform: translateY(-8px) scale(1.03);
            border-color: #d4af37;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .poster {
            height: 280px;
            object-fit: cover;
            width: 100%;
            transition: all 0.3s ease;
        }

        .tarjeta-carrusel:hover .poster {
            filter: brightness(1.1);
        }

        .card-body {
            padding: 12px;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== RESPONSIVE TARJETAS ===== */
        @media (max-width: 1200px) {
            .tarjeta-carrusel {
                width: 180px;
            }
            
            .poster {
                height: 250px;
            }
        }

        @media (max-width: 992px) {
            .tarjeta-carrusel {
                width: 170px;
            }
            
            .poster {
                height: 230px;
            }
        }

        @media (max-width: 768px) {
            .tarjeta-carrusel {
                width: 160px;
            }
            
            .poster {
                height: 210px;
            }
            
            .card-title {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .tarjeta-carrusel {
                width: 150px;
            }
            
            .poster {
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .tarjeta-carrusel {
                width: 140px;
            }
            
            .poster {
                height: 180px;
            }
            
            .card-body {
                padding: 8px;
            }
            
            .card-title {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 360px) {
            .tarjeta-carrusel {
                width: 120px;
            }
            
            .poster {
                height: 150px;
            }
            
            .card-title {
                font-size: 0.7rem;
            }
        }

        /* ===== MODAL ===== */
        .modal-golden .modal-content {
            background: #1a1f2e;
            border: 2px solid #d4af37;
            border-radius: 16px;
            overflow: hidden;
            margin: 15px;
        }

        .modal-golden .modal-header {
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
            padding: 15px 20px;
        }

        .modal-golden .modal-title {
            color: #d4af37;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .modal-golden .btn-close {
            filter: invert(1) brightness(2);
        }

        .modal-golden .modal-body {
            padding: 20px;
        }

        @media (max-width: 576px) {
            .modal-golden .modal-header {
                padding: 12px 15px;
            }
            
            .modal-golden .modal-title {
                font-size: 1rem;
            }
            
            .modal-golden .modal-body {
                padding: 15px;
            }
        }

        .video-container {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .info-detalles {
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .etiqueta-gris {
            color: #8a909e;
            font-weight: 600;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .info-detalles {
                padding: 12px;
            }
            
            .etiqueta-gris {
                font-size: 0.8rem;
            }
            
            #modalDescripcion {
                font-size: 0.85rem;
            }
            
            #modalTitulo {
                font-size: 1.2rem;
            }
        }

        /* ===== MENSAJES ===== */
        .mensaje-resultados {
            text-align: center;
            padding: 40px 15px;
            background: rgba(26, 31, 46, 0.5);
            border-radius: 16px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            margin: 20px 0;
        }

        .mensaje-resultados i {
            font-size: 3rem;
            color: #d4af37;
            margin-bottom: 15px;
        }

        .mensaje-resultados h3 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .mensaje-resultados p {
            color: #8a909e;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .mensaje-resultados {
                padding: 30px 10px;
            }
            
            .mensaje-resultados i {
                font-size: 2.5rem;
            }
            
            .mensaje-resultados h3 {
                font-size: 1.1rem;
            }
            
            .mensaje-resultados p {
                font-size: 0.8rem;
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
                    <i class="bi bi-camera-reels-fill"></i>
                </div>
                <span class="logo-text">GOLDEN CINEMA</span>
            </div>

            <div class="search-box">
                <input type="text" class="search-input" id="buscadorPeliculas" placeholder="🔍 Buscar películas..." autocomplete="off">
            </div>

            <div class="user-area">
                <div class="user-welcome">
                    <i class="bi bi-star-fill"></i>
                    <span>Hola, <?php echo $nombre_usuario; ?></span>
                </div>
                
                <?php if(isset($_SESSION['admin_id'])): ?>
                    <a href="menu.php" class="btn-golden">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        Panel
                    </a>
                <?php else: ?>
                    <a href="logout.php" class="btn-golden outline-light">
                        <i class="bi bi-door-open-fill"></i>
                        Salir
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- ===== CARRUSEL AUTOMÁTICO ===== -->
<?php if (!empty($peliculas_destacadas)): ?>
<div class="hero-section">
    <h2 class="hero-title">
        <i class="bi bi-stars"></i>
        Destacados de la semana
    </h2>
    
    <div class="carousel-container" id="carouselDestacados">
        <?php foreach ($peliculas_destacadas as $index => $peli): ?>
        <div class="carousel-slide" data-slide="<?php echo $index; ?>" <?php echo $index > 0 ? 'style="display: none;"' : ''; ?>>
            <img src="<?php echo (filter_var($peli['imagen_url'], FILTER_VALIDATE_URL)) ? $peli['imagen_url'] : 'uploads/peliculas/'.$peli['imagen_url']; ?>" 
                 alt="<?php echo $peli['titulo']; ?>"
                 onerror="this.src='https://via.placeholder.com/1400x500?text=Golden+Cinema'">
            <div class="carousel-content">
                <span class="carousel-badge"><?php echo $peli['genero']; ?></span>
                <span class="carousel-badge"><?php echo $peli['anio']; ?></span>
                <h3><?php echo $peli['titulo']; ?></h3>
                <p><?php echo substr($peli['descripcion'], 0, 150) . '...'; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="carousel-indicators-custom" id="carouselIndicators">
            <?php foreach ($peliculas_destacadas as $index => $peli): ?>
            <div class="carousel-dot <?php echo $index == 0 ? 'active' : ''; ?>" onclick="cambiarSlide(<?php echo $index; ?>)"></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<main class="catalogo-section">
    <div class="mensaje-resultados" id="mensajeSinResultados" style="display: none;">
        <i class="bi bi-film"></i>
        <h3>No encontramos ninguna película</h3>
        <p>Intenta buscar con otras palabras</p>
    </div>

    <?php if (empty($peliculas_por_genero)): ?>
        <div class="mensaje-resultados" id="mensajeVacioInicial">
            <i class="bi bi-camera-reels"></i>
            <h3>No hay películas disponibles</h3>
            <p>Pronto añadiremos más contenido</p>
        </div>
    <?php else: ?>
        <?php foreach ($peliculas_por_genero as $genero => $peliculas): ?>
            <div class="categoria-contenedor">
                <h4 class="titulo-categoria">
                    <i class="bi bi-tag-fill"></i>
                    <?php echo $genero; ?>
                </h4>
                <div class="fila-genero">
                    <?php foreach ($peliculas as $p): ?>
                        <?php $link_embed = convertirAEmbed($p['trailer_url']); ?>
                        
                        <div class="tarjeta-carrusel" 
                             data-url="<?php echo $link_embed; ?>"
                             data-titulo="<?php echo htmlspecialchars($p['titulo'], ENT_QUOTES); ?>"
                             data-descripcion="<?php echo htmlspecialchars($p['descripcion'], ENT_QUOTES); ?>"
                             data-genero="<?php echo htmlspecialchars($p['genero'], ENT_QUOTES); ?>"
                             data-anio="<?php echo htmlspecialchars($p['anio'] ?? '', ENT_QUOTES); ?>"
                             data-director="<?php echo htmlspecialchars($p['director'] ?? '', ENT_QUOTES); ?>"
                             data-reparto="<?php echo htmlspecialchars($p['reparto'] ?? '', ENT_QUOTES); ?>"
                             onclick="abrirDetalles(this)">
                             
                            <img src="<?php echo (filter_var($p['imagen_url'], FILTER_VALIDATE_URL)) ? $p['imagen_url'] : 'uploads/peliculas/'.$p['imagen_url']; ?>" 
                                 class="poster" 
                                 onerror="this.src='https://via.placeholder.com/200x280?text=Golden+Cinema'">
                            <div class="card-body">
                                <h6 class="card-title" title="<?php echo $p['titulo']; ?>">
                                    <?php echo $p['titulo']; ?>
                                </h6>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<!-- ===== MODAL DE DETALLES ===== -->
<div class="modal fade modal-golden" id="detallesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-film me-2"></i>
                    Detalles de la película
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="video-container ratio ratio-16x9" id="contenedorVideo">
                    <iframe id="videoIframe" src="" title="Trailer" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                
                <div class="info-detalles">
                    <h2 id="modalTitulo" class="fw-bold mb-3" style="color: #d4af37;"></h2>
                    
                    <div class="mb-3">
                        <span id="modalGenero" class="badge" style="background: #d4af37; color: #0a0f1e; padding: 8px 15px;"></span>
                        <span id="modalAnio" class="badge" style="background: #333; color: #fff; padding: 8px 15px; margin-left: 10px;"></span>
                    </div>
                    
                    <p class="mb-2">
                        <span class="etiqueta-gris">Director:</span>
                        <span id="modalDirector" class="text-light ms-2"></span>
                    </p>
                    
                    <p class="mb-3">
                        <span class="etiqueta-gris">Reparto:</span>
                        <span id="modalReparto" class="text-light ms-2"></span>
                    </p>
                    
                    <p id="modalDescripcion" style="color: #e2e8f0; line-height: 1.6;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ===== CARRUSEL AUTOMÁTICO =====
    let slideActual = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    const totalSlides = slides.length;
    
    function cambiarSlide(index) {
        if (index >= totalSlides) index = 0;
        if (index < 0) index = totalSlides - 1;
        
        slides.forEach((slide, i) => {
            slide.style.display = i === index ? 'block' : 'none';
        });
        
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
        
        slideActual = index;
    }
    
    function siguienteSlide() {
        slideActual = (slideActual + 1) % totalSlides;
        cambiarSlide(slideActual);
    }
    
    // Cambiar automáticamente cada 5 segundos
    if (totalSlides > 0) {
        setInterval(siguienteSlide, 5000);
    }
    
    // ===== MODAL DE DETALLES =====
    const modalDetalles = new bootstrap.Modal(document.getElementById('detallesModal'));
    const iframe = document.getElementById('videoIframe');
    const contVideo = document.getElementById('contenedorVideo');
    
    function abrirDetalles(elemento) {
        const url = elemento.getAttribute('data-url');
        document.getElementById('modalTitulo').innerText = elemento.getAttribute('data-titulo');
        document.getElementById('modalDescripcion').innerText = elemento.getAttribute('data-descripcion');
        document.getElementById('modalGenero').innerText = elemento.getAttribute('data-genero');
        document.getElementById('modalAnio').innerText = elemento.getAttribute('data-anio') || 'ND';
        document.getElementById('modalDirector').innerText = elemento.getAttribute('data-director') || 'No especificado';
        document.getElementById('modalReparto').innerText = elemento.getAttribute('data-reparto') || 'No especificado';

        if(url !== "") {
            iframe.src = url + "?autoplay=1"; 
            contVideo.style.display = "block";
        } else {
            iframe.src = "";
            contVideo.style.display = "none";
        }
        modalDetalles.show();
    }

    document.getElementById('detallesModal').addEventListener('hidden.bs.modal', function () {
        iframe.src = "";
    });

    // ===== BUSCADOR =====
    document.getElementById('buscadorPeliculas').addEventListener('input', function(e) {
        const textoBusqueda = e.target.value.toLowerCase();
        const categorias = document.querySelectorAll('.categoria-contenedor');
        const mensajeVacio = document.getElementById('mensajeSinResultados');
        let totalPeliculasVisibles = 0;

        categorias.forEach(categoria => {
            const tarjetas = categoria.querySelectorAll('.tarjeta-carrusel');
            let visiblesEnCategoria = 0;
            
            tarjetas.forEach(tarjeta => {
                const tituloPelicula = tarjeta.getAttribute('data-titulo').toLowerCase();
                if (tituloPelicula.includes(textoBusqueda)) {
                    tarjeta.style.display = 'block';
                    visiblesEnCategoria++;
                    totalPeliculasVisibles++;
                } else {
                    tarjeta.style.display = 'none';
                }
            });
            
            if (visiblesEnCategoria === 0) {
                categoria.style.display = 'none';
            } else {
                categoria.style.display = 'block';
            }
        });

        mensajeVacio.style.display = totalPeliculasVisibles === 0 ? 'block' : 'none';
    });
</script>

</body>
</html>