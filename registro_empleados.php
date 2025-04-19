<?php
session_start();
require 'conexion.php';

$error = ""; // Variable para almacenar mensajes de error
$success = ""; // Variable para almacenar mensajes de éxito

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar nuevo empleado
    $matricula = $_POST['matricula'];
    $nombre = $_POST['nombre'];
    $clave = $_POST['clave'];

    try {
        // Verificar si la matrícula ya existe
        $stmt = $conn->prepare("SELECT * FROM empleados WHERE matricula = :matricula");
        $stmt->execute(['matricula' => $matricula]);
        $empleado_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado_existente) {
            $error = "La matrícula ya está registrada. Por favor, utiliza una diferente.";
        } else {
            // Insertar nuevo empleado en la base de datos
            $stmt = $conn->prepare("INSERT INTO empleados (matricula, nombre, clave) VALUES (:matricula, :nombre, :clave)");
            $stmt->execute([
                'matricula' => $matricula,
                'nombre' => $nombre,
                'clave' => $clave // Clave en texto plano
            ]);

            // Mensaje de éxito
            $success = "Empleado registrado correctamente.";
        }
    } catch (PDOException $e) {
        $error = "Error al registrar el empleado: " . $e->getMessage();
    }
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
    <title>Registro de Empleados</title>
</head>
<body>
    <div class="container">
        <h1>Registro - Empleados</h1>
        
        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Formulario de registro -->
        <form method="POST" action="registro_empleados.php">
            <label for="matricula">Matrícula:</label>
            <input type="text" id="matricula" name="matricula" required>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="clave">Clave:</label>
            <input type="password" id="clave" name="clave" required>
            <button type="submit">Registrar Empleado</button>
        </form>

        <!-- Botón para regresar al menú de empleados -->
        <div class="menu-button">
            <a href="menu_empleados.php" class="button">Regresar al Menú</a>
        </div>
    </div>
</body>
</html>