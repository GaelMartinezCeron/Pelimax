<?php
// Permitir acceso desde cualquier origen (CORS) y definir JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Incluimos la conexión que está en la carpeta anterior
include '../conexion.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recibimos los datos enviados por Android (Volley usa POST params)
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Buscamos al usuario
    // NOTA: Verifica que estatus = 1 (Activo) para dejarlo pasar
    $stmt = $con->prepare("SELECT id, nombre, apellidos FROM clientes WHERE correo = ? AND password = ? AND estatus = 1");
    $stmt->bind_param("ss", $correo, $password);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $response['exito'] = true;
        $response['mensaje'] = "Bienvenido " . $fila['nombre'];
        $response['datos_usuario'] = $fila;
    } else {
        $response['exito'] = false;
        $response['mensaje'] = "Correo o contraseña incorrectos, o usuario inactivo.";
    }
} else {
    $response['exito'] = false;
    $response['mensaje'] = "Método no permitido";
}

echo json_encode($response);
?>