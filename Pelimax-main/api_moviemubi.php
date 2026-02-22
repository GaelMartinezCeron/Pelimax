<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'conexion.php';

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Support both POST form data and JSON body
$accion = '';
$inputData = [];

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if (strpos($contentType, 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $inputData = json_decode($json, true);
    $accion = isset($inputData['accion']) ? $inputData['accion'] : '';
} else {
    $inputData = $_POST;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
}

function getParam($key, $inputData) {
    return isset($inputData[$key]) ? $inputData[$key] : '';
}

// =============================================
// LOGIN
// =============================================
if ($accion == 'login') {
    $correo = $con->real_escape_string(getParam('correo', $inputData));
    $password = getParam('password', $inputData);

    $sql = "SELECT id, nombre, paterno, materno, correo FROM users WHERE correo = '$correo' AND password = '$password' AND activo = 1";
    $res = $con->query($sql);

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        echo json_encode(["status" => "success", "mensaje" => "Bienvenido " . $user['nombre'], "usuario" => $user]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Correo o contraseña incorrectos, o usuario inactivo."]);
    }
}

// =============================================
// REGISTRO (auto-genera password y la envia por correo)
// =============================================
elseif ($accion == 'registro') {
    $nombre = $con->real_escape_string(getParam('nombre', $inputData));
    $paterno = $con->real_escape_string(getParam('paterno', $inputData));
    $materno = $con->real_escape_string(getParam('materno', $inputData));
    $correo = $con->real_escape_string(getParam('correo', $inputData));

    // Validar campos
    if (empty($nombre) || empty($paterno) || empty($materno) || empty($correo)) {
        echo json_encode(["status" => "error", "mensaje" => "Todos los campos son obligatorios."]);
        exit();
    }

    // Verificar si el correo ya existe
    $check = $con->query("SELECT id FROM users WHERE correo = '$correo'");
    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "mensaje" => "Este correo ya esta registrado."]);
        exit();
    }

    // Generar contraseña aleatoria segura
    $password = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%"), 0, 10);

    // Insertar en BD
    $stmt = $con->prepare("INSERT INTO users (nombre, paterno, materno, correo, password, activo) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $nombre, $paterno, $materno, $correo, $password);

    if ($stmt->execute()) {
        // Enviar correo con la contraseña
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gaelceron45@gmail.com';
            $mail->Password = 'xecauxliyfkpwvck';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('gaelceron45@gmail.com', 'Golden Cinema');
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = 'Bienvenido a Golden Cinema - Tu contraseña';
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: 'Montserrat', Arial, sans-serif; background: #0a0f1e; margin: 0; padding: 0; }
                        .container { max-width: 600px; margin: 0 auto; background: #1a1f2e; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
                        .header { background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%); padding: 30px; text-align: center; }
                        .header h1 { color: #0a0f1e; margin: 0; font-size: 28px; font-weight: 800; }
                        .content { padding: 40px 30px; color: #ffffff; }
                        .password-box { background: rgba(212, 175, 55, 0.1); border: 2px solid #d4af37; border-radius: 15px; padding: 20px; margin: 25px 0; text-align: center; }
                        .password { font-size: 32px; font-weight: 800; color: #d4af37; letter-spacing: 3px; font-family: monospace; }
                        .footer { background: rgba(10, 15, 30, 0.5); padding: 20px; text-align: center; color: #b0b7c4; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>GOLDEN CINEMA</h1>
                        </div>
                        <div class='content'>
                            <h2 style='color: #d4af37; margin-top: 0;'>Bienvenido, $nombre!</h2>
                            <p>Tu cuenta ha sido creada exitosamente. Aqui tienes tu contraseña para iniciar sesion:</p>
                            <div class='password-box'>
                                <div class='password'>$password</div>
                            </div>
                            <p><strong>Importante:</strong> Guarda esta contraseña en un lugar seguro.</p>
                            <p>Tu correo de acceso: <strong>$correo</strong></p>
                        </div>
                        <div class='footer'>
                            <p>Golden Cinema - Tu cine en casa</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->send();
            echo json_encode(["status" => "success", "mensaje" => "Registro exitoso. Hemos enviado tu contraseña a $correo"]);
        } catch (Exception $e) {
            // Se registro pero fallo el correo
            echo json_encode(["status" => "success", "mensaje" => "Registro exitoso, pero hubo un problema enviando el correo. Tu contraseña es: $password"]);
        }
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Error al registrar en la base de datos."]);
    }
    $stmt->close();
}

// =============================================
// RECUPERAR CONTRASEÑA
// =============================================
elseif ($accion == 'recuperar') {
    $correo = $con->real_escape_string(getParam('correo', $inputData));

    if (empty($correo)) {
        echo json_encode(["status" => "error", "mensaje" => "Ingresa tu correo electronico."]);
        exit();
    }

    $buscar = $con->query("SELECT * FROM users WHERE correo = '$correo'");

    if ($buscar->num_rows > 0) {
        $usuario = $buscar->fetch_assoc();
        $nombre = $usuario['nombre'];

        // Generar nueva contraseña aleatoria
        $nuevaClave = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%"), 0, 10);

        // Actualizar en BD
        $con->query("UPDATE users SET password='$nuevaClave' WHERE correo='$correo'");

        // Enviar correo
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gaelceron45@gmail.com';
            $mail->Password = 'xecauxliyfkpwvck';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('gaelceron45@gmail.com', 'Golden Cinema');
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de contraseña - Golden Cinema';
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: 'Montserrat', Arial, sans-serif; background: #0a0f1e; margin: 0; padding: 0; }
                        .container { max-width: 600px; margin: 0 auto; background: #1a1f2e; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
                        .header { background: linear-gradient(135deg, #d4af37 0%, #f5d742 100%); padding: 30px; text-align: center; }
                        .header h1 { color: #0a0f1e; margin: 0; font-size: 28px; font-weight: 800; }
                        .content { padding: 40px 30px; color: #ffffff; }
                        .password-box { background: rgba(212, 175, 55, 0.1); border: 2px solid #d4af37; border-radius: 15px; padding: 20px; margin: 25px 0; text-align: center; }
                        .password { font-size: 32px; font-weight: 800; color: #d4af37; letter-spacing: 3px; font-family: monospace; }
                        .footer { background: rgba(10, 15, 30, 0.5); padding: 20px; text-align: center; color: #b0b7c4; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>GOLDEN CINEMA</h1>
                        </div>
                        <div class='content'>
                            <h2 style='color: #d4af37; margin-top: 0;'>Recuperacion de contraseña</h2>
                            <p>Hola $nombre, hemos generado una nueva contraseña para tu cuenta:</p>
                            <div class='password-box'>
                                <div class='password'>$nuevaClave</div>
                            </div>
                            <p><strong>Importante:</strong> Te recomendamos guardar esta contraseña en un lugar seguro.</p>
                        </div>
                        <div class='footer'>
                            <p>Golden Cinema - Tu cine en casa</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->send();
            echo json_encode(["status" => "success", "mensaje" => "Hemos enviado una nueva contraseña a tu correo electronico."]);
        } catch (Exception $e) {
            echo json_encode(["status" => "success", "mensaje" => "Nueva contraseña generada, pero hubo un problema enviando el correo. Tu nueva contraseña es: $nuevaClave"]);
        }
    } else {
        echo json_encode(["status" => "error", "mensaje" => "El correo ingresado no esta registrado."]);
    }
}

// =============================================
// OBTENER PELICULAS
// =============================================
elseif ($accion == 'get_peliculas') {
    $base_url = "https://sandybrown-manatee-779276.hostingersite.com/";
    
    $sql = "SELECT id, nombre, genero, descripcion, imagen, video_url FROM movies WHERE activa = 1 ORDER BY id DESC";
    $res = $con->query($sql);

    $peliculas = array();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $pelicula = array();
            $pelicula['id'] = $row['id'];
            $pelicula['nombre'] = $row['nombre'];
            $pelicula['genero'] = $row['genero'];
            $pelicula['descripcion'] = $row['descripcion'];
            $pelicula['imagen'] = $row['imagen'];
            $pelicula['video_url'] = $row['video_url'];
            $peliculas[] = $pelicula;
        }
    }
    echo json_encode(["status" => "success", "peliculas" => $peliculas]);
}

// =============================================
// ACCION NO VALIDA
// =============================================
else {
    echo json_encode(["status" => "error", "mensaje" => "Accion no valida. Acciones disponibles: login, registro, recuperar, get_peliculas"]);
}

$con->close();
?>
