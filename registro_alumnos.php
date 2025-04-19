<?php
session_start();
require 'conexion.php';

$error = ""; // Variable para almacenar mensajes de error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_control = $_POST['num_control'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];

    try {
        // Verificar si el número de control ya existe
        $stmt = $conn->prepare("SELECT * FROM personas WHERE num_control = :num_control");
        $stmt->execute(['num_control' => $num_control]);
        $alumno_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($alumno_existente) {
            $error = "El número de control ya está registrado.";
        } else {
            // Registrar nuevo alumno
            $stmt = $conn->prepare("INSERT INTO personas (num_control, nombre, telefono, email) VALUES (:num_control, :nombre, :telefono, :email)");
            $stmt->execute([
                'num_control' => $num_control,
                'nombre' => $nombre,
                'telefono' => $telefono,
                'email' => $email
            ]);

            // Mensaje de éxito
            $_SESSION['message'] = "Alumno registrado correctamente.";
            $_SESSION['message_type'] = "success";
        }
    } catch (PDOException $e) {
        // Mensaje de error
        $error = "Error al registrar el alumno: " . $e->getMessage();
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
    <title>Registro de Alumnos</title>
</head>
<body>
    <div class="container">
        <h1>Registro - Alumnos</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php
            // Limpiar el mensaje después de mostrarlo
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="registro_alumnos.php">
            <label for="num_control">Número de Control:</label>
            <input type="text" id="num_control" name="num_control" required>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Registrar Alumno</button>
        </form>

        <!-- Botón para regresar al menú -->
        <div class="menu-button">
            <a href="menu_empleados.php" class="button">Regresar al Menú</a>
        </div>
    </div>  
</body>
</html>