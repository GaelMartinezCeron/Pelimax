<?php
header("Content-Type: application/json; charset=UTF-8");
include 'conexion.php';

// Verificamos qué acción nos pide la app móvil
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion == 'login') {
    $correo = $con->real_escape_string($_POST['correo']);
    $password = $_POST['password'];

    $sql = "SELECT id, nombre FROM clientes WHERE correo = '$correo' AND password = '$password' AND estatus = 1";
    $res = $con->query($sql);

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        echo json_encode(["status" => "success", "usuario" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "Credenciales incorrectas."]);
    }
} 
elseif ($accion == 'get_peliculas') {
    $sql = "SELECT id, titulo, genero, descripcion, anio, director, imagen_url FROM peliculas WHERE estatus = 1 ORDER BY id DESC";
    $res = $con->query($sql);
    
    $peliculas = array();
    while ($row = $res->fetch_assoc()) {
        // Asegurarnos de mandar la URL completa de la imagen si es local
        if (!filter_var($row['imagen_url'], FILTER_VALIDATE_URL)) {
            // Cambia "tusitio.com" por tu dominio real
            $row['imagen_url'] = "https://tusitio.com/uploads/peliculas/" . $row['imagen_url'];
        }
        $peliculas[] = $row;
    }
    echo json_encode(["status" => "success", "peliculas" => $peliculas]);
} 
else {
    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
}
?>