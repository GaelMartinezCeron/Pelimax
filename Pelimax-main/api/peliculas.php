<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

include '../conexion.php';

// Array para guardar los resultados
$peliculas = array();

// IMPORTANTE: Definir la URL base de tu sitio para que Android sepa descargar la imagen
// Cambia 'localhost/Proyecto_Streaming' por tu dominio real (ej: 'midominio.000webhostapp.com')
// Si estás en localhost y pruebas con emulador, usa la IP de tu PC: 'http://192.168.1.50/Proyecto_Streaming'
$base_url = "localhost"; 

// Consultamos solo las activas
$sql = "SELECT id, titulo, genero, descripcion, imagen FROM peliculas WHERE estatus = 1 ORDER BY id DESC";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $item = array();
        $item['id'] = $row['id'];
        $item['titulo'] = $row['titulo'];
        $item['genero'] = $row['genero'];
        $item['descripcion'] = $row['descripcion'];
        // Concatenamos la URL completa de la imagen
        $item['imagen_url'] = $base_url . $row['imagen'];
        
        array_push($peliculas, $item);
    }
}

echo json_encode($peliculas);
?>