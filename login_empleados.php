<?php
session_start();
require 'conexion.php';

$error = "";

if (!isset($_SESSION['intentos_empleado'])) {
    $_SESSION['intentos_empleado'] = 0;
}

$bloqueado = ($_SESSION['intentos_empleado'] >= 3);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {
    $matricula = $_POST['matricula'];
    $clave = $_POST['clave'];

    try {
        // Verificar si el usuario es un administrador
        $stmt = $conn->prepare("SELECT * FROM empleados WHERE matricula = :matricula AND clave = :clave");
        $stmt->execute(['matricula' => $matricula, 'clave' => $clave]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            $_SESSION['empleado'] = $empleado;

            $_SESSION['message'] = "Inicio de sesión exitoso.";
            $_SESSION['message_type'] = "success";

            header("Location: menu_empleados.php");
            exit();
        } else {
            $_SESSION['intentos_empleado']++;
            if ($_SESSION['intentos_empleado'] >= 3) {
                $bloqueado = true;
            } else {
                $error = "Matrícula o clave incorrecta. Intentos restantes: " . (3 - $_SESSION['intentos_empleado']);
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
        <h1>Iniciar Sesión - Empleados</h1>

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

        <form method="POST" action="login_empleados.php">
            <label for="matricula">Matrícula:</label>
            <input type="text" id="matricula" name="matricula" required <?php echo $bloqueado ? 'disabled' : ''; ?>>
            <label for="clave">Clave:</label>
            <input type="password" id="clave" name="clave" required <?php echo $bloqueado ? 'disabled' : ''; ?>>
            <button type="submit" <?php echo $bloqueado ? 'disabled' : ''; ?>>Iniciar Sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="registro_empleados.php">Regístrate aquí</a></p>
        <p><a href="index.php">Regresar al menú principal</a></p>
    </div>
</body>
</html>