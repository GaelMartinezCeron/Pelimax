<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
include '../conexion.php'; // Asegúrate de que apunte bien a tu conexion.php

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // 1. Verificar si el correo ya existe
    $stmt = $con->prepare("SELECT id FROM clientes WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $response['exito'] = false;
        $response['mensaje'] = "Este correo ya está registrado.";
    } else {
        // 2. Insertar nuevo cliente (estatus 1 = Activo por defecto)
        $stmt_insert = $con->prepare("INSERT INTO clientes (nombre, apellidos, correo, password, estatus) VALUES (?, ?, ?, ?, 1)");
        $stmt_insert->bind_param("ssss", $nombre, $apellidos, $correo, $password);
        
        if ($stmt_insert->execute()) {
            $response['exito'] = true;
            $response['mensaje'] = "Registro exitoso. Ya puedes iniciar sesión.";
        } else {
            $response['exito'] = false;
            $response['mensaje'] = "Error al registrar en la base de datos.";
        }
        $stmt_insert->close();
    }
    $stmt->close();
} else {
    $response['exito'] = false;
    $response['mensaje'] = "Método no permitido.";
}

echo json_encode($response);
$con->close();
?>