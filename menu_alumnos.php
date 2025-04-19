<?php
session_start();

// Verificar si el alumno ha iniciado sesión
if (!isset($_SESSION['alumno'])) {
    header("Location: login_alumnos.php");
    exit();
}
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="imagenes/2821c92b-0913-46b6-b9a4-47378b3eb04d.png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Menú de Alumnos</h1>
        <div class="menu">
            <h2>Opciones</h2>
            <ul>
                <li><a href="consultar_libros.php" class="menu-link">Consultar Libros</a></li>
                <li><a href="pedir_libro.php" class="menu-link">Pedir Libro Prestado</a></li>
                <li><a href="devolver_libro.php" class="menu-link">Devolver Libro</a></li>
            </ul>
        </div>
        <p><a href="logout.php" class="logout-link">Cerrar Sesión</a></p>
    </div>
</body>
</html>