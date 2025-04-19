<?php
session_start();
require 'conexion.php';

$error = "";

if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
}

$bloqueado = ($_SESSION['intentos'] >= 3);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {
    $num_control = $_POST['num_control'];
    $nombre = $_POST['nombre'];

    try {
        $stmt = $conn->prepare("SELECT * FROM personas WHERE num_control = :num_control AND nombre = :nombre");
        $stmt->execute(['num_control' => $num_control, 'nombre' => $nombre]);
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($alumno) {
            $_SESSION['alumno'] = $alumno;

            // Reiniciar el contador de intentos
            $_SESSION['intentos'] = 0;

            $_SESSION['message'] = "Inicio de sesión exitoso.";
            $_SESSION['message_type'] = "success";

            header("Location: menu_alumnos.php");
            exit();
        } else {
            $_SESSION['intentos']++;
            if ($_SESSION['intentos'] >= 3) {
                $bloqueado = true;
            } else {
                $error = "Número de control o nombre incorrecto. Intentos restantes: " . (3 - $_SESSION['intentos']);
            }
        }
    } catch (PDOException $e) {
        $error = "Error al iniciar sesión: " . $e->getMessage();
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
</head>
<body>
    <div class="container">
        <h1>Iniciar Sesión - Alumnos</h1>

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

        <?php if ($bloqueado): ?>
            <div class="message error">Has excedido el número de intentos permitidos. Por favor, intenta más tarde.</div>
        <?php endif; ?>

        <form method="POST" action="login_alumnos.php">
            <label for="num_control">Número de Control:</label>
            <input type="text" id="num_control" name="num_control" required <?php echo $bloqueado ? 'disabled' : ''; ?>>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required <?php echo $bloqueado ? 'disabled' : ''; ?>>
            <button type="submit" <?php echo $bloqueado ? 'disabled' : ''; ?>>Iniciar Sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="registro_alumnos.php">Regístrate aquí</a></p>
        <p><a href="index.php">Regresar al menú principal</a></p>
    </div>
</body>
</html>