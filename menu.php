<?php
session_start();
// Si no hay sesión de administrador, lo regresamos al login
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include 'conexion.php';

// Contar películas
$result_pelis = $con->query("SELECT COUNT(*) as total FROM peliculas WHERE estatus = 1");
$total_pelis = $result_pelis->fetch_assoc()['total'];

// Contar administradores
$result_admins = $con->query("SELECT COUNT(*) as total FROM usuarios_admin WHERE estatus = 1");
$total_admins = $result_admins->fetch_assoc()['total'];

// Contar clientes activos
$result_clientes = $con->query("SELECT COUNT(*) as total FROM clientes WHERE estatus = 1");
$total_clientes = $result_clientes->fetch_assoc()['total'];

// Últimas películas
$ultimas_pelis = $con->query("SELECT * FROM peliculas WHERE estatus = 1 ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Cinema - Panel Administrativo</title>
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
            background: #e0c8e4;
            color: #fff;
        }

        /* ===== NAVBAR PRINCIPAL ===== */
        .navbar-premium {
            background: linear-gradient(135deg, #020617 0%, #0a0f1e 100%);
            border-bottom: 2px solid #d4af37;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        /* Logo */
        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #1b4cde;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-text-main {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .logo-text-sub {
            font-size: 0.7rem;
            color: #8a909e;
            letter-spacing: 1px;
        }

        /* Menú de navegación */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 5px;
            height: 100%;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: #b0b7c4;
            text-decoration: none;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
            height: 50px;
        }

        .nav-link i {
            font-size: 1.2rem;
            color: #d4af37;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(212, 175, 55, 0.1);
            color: #fff;
        }

        .nav-link:hover i {
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(212, 175, 55, 0.15);
            color: #d4af37;
            border-bottom: 2px solid #d4af37;
            border-radius: 10px 10px 0 0;
        }

        /* Área de usuario */
        .user-area {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 18px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 50px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0a0f1e;
            font-size: 1.2rem;
        }

        .user-details {
            line-height: 1.4;
        }

        .user-name {
            font-weight: 700;
            color: #fff;
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: #d4af37;
        }

        .btn-logout {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(255, 107, 107, 0.2);
            border-color: #ff6b6b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {            
            .nav-menu {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .navbar-content {
                height: auto;
                padding: 15px 0;
                flex-direction: column;
                gap: 15px;
            }
            
            .user-area {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
<!-- ===== NAVBAR RESPONSIVE ===== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container-fluid">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="menu.php">
            <i class="bi bi-camera-reels-fill text-warning fs-4 me-2"></i>
            <div>
                <span class="fw-bold">GOLDEN CINEMA</span>
                <small class="d-block">ADMIN PANEL</small>
            </div>
        </a>

        <!-- Botón móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenido colapsable -->
        <div class="collapse navbar-collapse" id="navbarAdmin">

            <!-- Menú -->
            <ul class="navbar-nav mx-auto text-center">
                <li class="nav-item">
                    <a class="nav-link" href="menu.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="peliculas.php">
                        <i class="bi bi-film"></i> Catálogo
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        <i class="bi bi-people"></i> Admins
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clientes.php">
                        <i class="bi bi-shield-lock"></i> Seguridad
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="catalogo.php">
                        <i class="bi bi-display"></i> Vista Pública
                    </a>
                </li>
            </ul>

            <!-- Usuario -->
            <div class="d-flex align-items-center flex-column flex-lg-row text-center">
                <div class="me-lg-3 mb-2 mb-lg-0">
                    <i class="bi bi-shield-fill text-warning"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>
                </div>
                <a href="logout.php" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>

        </div>
    </div>
</nav>

<!-- ===== SECCIÓN PRINCIPAL ===== -->
<section class="container py-5">

    <div class="text-center mb-5">
        <h1 class="fw-bold section-title">Bienvenido a Golden Cinema</h1>
        <p class="lead">
            Plataforma digital profesional para la gestión y visualización de películas premium.
        </p>
    </div>

    <div class="row g-4">

        <!-- Quiénes Somos -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card bg-dark text-light h-100 shadow border-0">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-1 text-warning mb-3"></i>
                    <h4>¿Quiénes Somos?</h4>
                    <p>
                        Golden Cinema es una plataforma desarrollada para administrar
                        y gestionar contenido cinematográfico de manera eficiente,
                        segura y moderna.
                    </p>
                </div>
            </div>
        </div>

        <!-- Objetivo -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card bg-dark text-light h-100 shadow border-0">
                <div class="card-body text-center">
                    <i class="bi bi-bullseye fs-1 text-warning mb-3"></i>
                    <h4>Nuestro Objetivo</h4>
                    <p>
                        Ofrecer una experiencia intuitiva para la administración
                        de películas, usuarios y contenido multimedia,
                        simulando plataformas de streaming profesionales.
                    </p>
                </div>
            </div>
        </div>

        <!-- Misión y Visión -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card bg-dark text-light h-100 shadow border-0">
                <div class="card-body text-center">
                    <i class="bi bi-lightning-fill fs-1 text-warning mb-3"></i>
                    <h4>Misión & Visión</h4>
                    <p>
                        <strong>Misión:</strong> Brindar entretenimiento digital accesible y organizado.
                        <br><br>
                        <strong>Visión:</strong> Convertirnos en una plataforma líder en gestión
                        cinematográfica online.
                    </p>
                </div>
            </div>
        </div>

    </div>

</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});
</script>
</body>
</html>