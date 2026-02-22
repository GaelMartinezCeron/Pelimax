<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

include '../conexion.php';

$peliculas = array();
$base_url = "localhost"; 

$sql = "SELECT id, titulo, genero, descripcion, imagen FROM peliculas WHERE estatus = 1 ORDER BY id DESC";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $item = array();
        $item['id'] = $row['id'];
        $item['titulo'] = $row['titulo'];
        $item['genero'] = $row['genero'];
        $item['descripcion'] = $row['descripcion'];
        $item['imagen_url'] = $base_url . $row['imagen'];
        
        array_push($peliculas, $item);
    }
}

echo json_encode($peliculas);
?>