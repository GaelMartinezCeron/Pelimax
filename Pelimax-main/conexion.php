<?php
// conexion.php
$host = "localhost";    
$user = "root";         // ¡Usuario con prefijo!
$pass = "";          // Tu contraseña correcta
$db   = "netflix_db";  // ¡Base de datos con prefijo!

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_error) {
    die("Fallo la conexión: " . $con->connect_error);
}

// Para que los acentos y ñ se vean bien
$con->set_charset("utf8");
?>