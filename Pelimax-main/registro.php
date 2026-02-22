<?php
// Encendemos el reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro_publico'])) {
    $nombre = $con->real_escape_string($_POST['nombre']);
    $apellidos = $con->real_escape_string($_POST['apellidos']);
    $correo = $con->real_escape_string($_POST['correo']);
    $password = $con->real_escape_string($_POST['password']); 

    $check = $con->query("SELECT id FROM clientes WHERE correo = '$correo'");
    
    if ($check->num_rows > 0) {
        $mensaje = "<div class='alert alert-warning border-0 bg-warning text-dark shadow-sm'>Este correo ya está registrado. <a href='index.php' class='fw-bold text-dark'>Inicia sesión aquí</a>.</div>";
    } else {
        $sql = "INSERT INTO clientes (nombre, apellidos, correo, password, estatus) VALUES ('$nombre', '$apellidos', '$correo', '$password', 1)";
        
        if ($con->query($sql)) {
            $mensaje = "<div class='alert alert-success border-0 bg-success text-white shadow-sm animate__animated animate__fadeIn'>
                <h5 class='fw-bold'>¡Registro exitoso!</h5>
                <p class='small mb-0'>Ya puedes abrir la aplicación móvil e iniciar sesión con tus credenciales.</p>
                <a href='index.php' class='btn btn-sm btn-light mt-3 fw-bold rounded-pill px-4'>Ir al Inicio</a>
            </div>";
        } else {
            $mensaje = "<div class='alert alert-danger border-0 bg-danger text-white shadow-sm'>Error crítico: " . $con->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete a MovieMubi - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        /* FONDO ESTILO NETFLIX (Posters con Overlay Negro) */
        body { 
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            /* Imagen de posters de fondo con overlay oscuro */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.9)), 
                        url('https://assets.nflxext.com/ffe/siteui/vlv3/f841d4c7-10e1-40af-bca1-07e3f8eb14b8/f9368c24-4396-4720-9bc1-e2d474472115/MX-es-20220502-popsignuptwoweeks-perspective_alpha_website_medium.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* HEADER / LOGO */
        .header-logo {
            position: absolute;
            top: 25px;
            left: 50px;
            text-decoration: none;
        }

        /* TARJETA DE REGISTRO */
        .registro-card { 
            width: 100%; 
            max-width: 450px; 
            padding: 60px 68px 40px; 
            background: rgba(0, 0, 0, 0.75); 
            border-radius: 4px;
            color: white;
        }

        .titulo-registro { font-size: 2rem; font-weight: 700; margin-bottom: 28px; }

        /* INPUTS ESTILO MODERNO OSCURO */
        .form-control {
            background: #333 !important;
            border: none !important;
            border-bottom: 2px solid transparent !important;
            color: white !important;
            padding: 12px 15px;
            height: 50px;
            border-radius: 4px !important;
        }

        .form-control:focus {
            background: #454545 !important;
            border-bottom: 2px solid #e50914 !important; /* El rojo característico */
            box-shadow: none !important;
            outline: none;
        }

        .form-label { color: #8c8c8c; font-size: 0.9rem; }

        /* BOTÓN DE ACCIÓN */
        .btn-netflix {
            background-color: #e50914;
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            padding: 16px;
            margin-top: 24px;
            border-radius: 4px;
            transition: 0.2s;
        }

        .btn-netflix:hover {
            background-color: #f6121d;
            transform: scale(1.02);
        }

        .footer-link {
            color: #737373;
            text-decoration: none;
            font-size: 0.95rem;
        }
        
        .footer-link:hover { text-decoration: underline; }

        @media (max-width: 500px) {
            .registro-card { padding: 40px 20px; background: black; }
            .header-logo { left: 20px; }
        }
    </style>
</head>
<body>

<a href="index.php" class="header-logo">
    <h2 class="fw-bold text-danger m-0" style="letter-spacing: -1.5px;">MOVIEMUBI</h2>
</a>

<div class="registro-card animate__animated animate__fadeIn">
    
    <?php if(!strpos($mensaje, 'exitoso')): ?>
        <h1 class="titulo-registro text-start">Regístrate</h1>
        
        <?php echo $mensaje; ?>
    
        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label mb-1">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Tu nombre">
                </div>
                <div class="col-md-6">
                    <label class="form-label mb-1">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required placeholder="Tus apellidos">
                </div>
                <div class="col-12">
                    <label class="form-label mb-1">Email</label>
                    <input type="email" name="correo" class="form-control" required placeholder="correo@ejemplo.com">
                </div>
                <div class="col-12">
                    <label class="form-label mb-1">Contraseña</label>
                    <input type="password" name="password" class="form-control" required placeholder="Crea una contraseña">
                </div>
            </div>
            
            <button type="submit" name="registro_publico" class="btn btn-netflix w-100 shadow">Continuar</button>
        </form>

        <div class="mt-4 pt-2">
            <span style="color: #737373;">¿Ya tienes cuenta?</span> 
            <a href="index.php" class="text-white text-decoration-none fw-bold ms-1">Inicia sesión ahora.</a>
        </div>
    
    <?php else: ?>
        <?php echo $mensaje; ?>
    <?php endif; ?>

    <div class="mt-5 border-top border-secondary pt-3 opacity-50">
        <p class="small mb-0" style="color: #737373;">Esta página está protegida por Google reCAPTCHA para asegurar que no eres un robot.</p>
    </div>
</div>

</body>
</html>