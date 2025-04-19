<?php

$host = 'localhost';
$dbname = 'biblioteca';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("SET NAMES 'utf8'");
    
    // Mensaje de éxito
    $_SESSION['message'] = "Conexión a la base de datos establecida correctamente.";
    $_SESSION['message_type'] = "success";
} catch (PDOException $e) {
    // Mensaje de error
    $_SESSION['message'] = "Error al conectar a la base de datos: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}
?>