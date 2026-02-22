<?php
session_start();
session_destroy(); // Destruye cualquier sesión (admin o cliente)
header("Location: index.php"); // Los regresa a la pantalla principal
exit();
?>