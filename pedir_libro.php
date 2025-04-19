<?php
session_start();

// Verificar si el alumno ha iniciado sesión
if (!isset($_SESSION['alumno'])) {
    header("Location: login_alumnos.php");
    exit();
}

require 'conexion.php';

$error = ""; // Variable para almacenar mensajes de error
$libros_disponibles = []; // Variable para almacenar los libros disponibles
$num_control = $_SESSION['alumno']['num_control']; // Obtener el número de control del alumno

// Verificar si el alumno tiene una devolución pendiente
try {
    $stmt = $conn->prepare("SELECT * FROM prestamos WHERE num_control = :num_control AND fecha_devolucion IS NULL");
    $stmt->execute(['num_control' => $num_control]);
    $prestamo_pendiente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prestamo_pendiente) {
        $error = "No puedes pedir un libro prestado hasta que devuelvas el libro actual.";
    } else {
        // Obtener la lista de libros disponibles
        $stmt = $conn->prepare("SELECT * FROM libros WHERE isbn NOT IN (SELECT isbn FROM prestamos WHERE fecha_devolucion IS NULL)");
        $stmt->execute();
        $libros_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($libros_disponibles)) {
            $error = "No hay libros disponibles en este momento.";
        }
    }
} catch (PDOException $e) {
    $error = "Error al verificar préstamos: " . $e->getMessage();
}

// Procesar la solicitud de préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $isbn = $_POST['isbn'];

    try {
        // Registrar el préstamo
        $stmt = $conn->prepare("INSERT INTO prestamos (isbn, num_control, fecha_prestamo) VALUES (:isbn, :num_control, NOW())");
        $stmt->execute(['isbn' => $isbn, 'num_control' => $num_control]);

        // Mensaje de éxito
        $_SESSION['message'] = "Libro prestado correctamente.";
        $_SESSION['message_type'] = "success";

        // Redirigir al menú de alumnos
        header("Location: menu_alumnos.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error al pedir el libro: " . $e->getMessage();
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
        <h1>Pedir Libro Prestado</h1>

        <!-- Mostrar mensajes de error -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Mostrar libros disponibles -->
        <?php if (!empty($libros_disponibles)): ?>
            <form method="POST" action="pedir_libro.php">
                <label for="isbn">Selecciona un libro:</label>
                <select id="isbn" name="isbn" required>
                    <?php foreach ($libros_disponibles as $libro): ?>
                        <option value="<?php echo $libro['isbn']; ?>">
                            <?php echo $libro['titulo']; ?> (<?php echo $libro['autor']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Pedir Libro</button>
            </form>
        <?php endif; ?>

        <p><a href="menu_alumnos.php">Regresar al menú</a></p>
    </div>
</body>
</html>