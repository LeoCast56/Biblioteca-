<?php
session_start();
require 'conexion.php';

// Verificar si el bibliotecario ha iniciado sesión
if (!isset($_SESSION['empleado'])) {
    header("Location: login_empleados.php");
    exit();
}

$error = "";
$success = "";
$mostrar_formulario_pago = false;
$prestamo_atrasado = null;

// Procesar el formulario de devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devolver_libro'])) {
    $num_control = trim($_POST['num_control']); // Eliminar espacios en blanco
    $isbn = trim($_POST['id_libro']); // Eliminar espacios en blanco

    try {
        // Verificar si el préstamo existe y está activo
        $stmt = $conn->prepare("
            SELECT * 
            FROM prestamos 
            WHERE num_control = :num_control 
              AND isbn = :isbn 
              AND estado = 'activo'
        ");
        $stmt->execute(['num_control' => $num_control, 'isbn' => $isbn]);
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($prestamo) {
            // Verificar si el libro fue devuelto fuera de tiempo
            $fecha_actual = date('Y-m-d');
            if ($fecha_actual > $prestamo['fecha_devolucion']) {
                // Generar multa por devolución tardía
                $monto_multa = 50.00; // Ejemplo: monto fijo de la multa
                $stmt = $conn->prepare("
                    INSERT INTO multas (id_prestamo, monto, fecha_generada, estado)
                    VALUES (:id_prestamo, :monto, :fecha_generada, 'pendiente')
                ");
                $stmt->execute([
                    'id_prestamo' => $prestamo['id_prestamo'],
                    'monto' => $monto_multa,
                    'fecha_generada' => $fecha_actual
                ]);

                $mostrar_formulario_pago = true;
                $prestamo_atrasado = $prestamo;
            } else {
                // Registrar la devolución sin multa
                $stmt = $conn->prepare("
                    UPDATE prestamos 
                    SET fecha_devolucion = NOW(), estado = 'completado' 
                    WHERE id_prestamo = :id_prestamo
                ");
                $stmt->execute(['id_prestamo' => $prestamo['id_prestamo']]);

                // Actualizar la disponibilidad del libro
                $stmt = $conn->prepare("
                    UPDATE libros 
                    SET disponible = 1 
                    WHERE isbn = :isbn
                ");
                $stmt->execute(['isbn' => $isbn]);

                $success = "Devolución registrada exitosamente. La disponibilidad del libro ha sido actualizada.";
            }
        } else {
            $error = "No se encontró un préstamo activo para este libro y estudiante.";
        }
    } catch (PDOException $e) {
        $error = "Error al registrar la devolución: " . $e->getMessage();
    }
}

// Procesar el formulario de confirmación de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pago'])) {
    $id_prestamo = $_POST['id_prestamo'];
    $isbn = $_POST['isbn'];
    $admin_password = $_POST['admin_password'];

    try {
        // Verificar la contraseña del administrador
        $stmt = $conn->prepare("
            SELECT * 
            FROM empleados 
            WHERE matricula = :matricula 
              AND clave = :clave 
              AND rol = 'admin'
        ");
        $stmt->execute([
            'matricula' => $_SESSION['empleado']['matricula'],
            'clave' => $admin_password
        ]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Registrar la devolución y confirmar el pago
            $stmt = $conn->prepare("
                UPDATE prestamos 
                SET fecha_devolucion = NOW(), estado = 'completado' 
                WHERE id_prestamo = :id_prestamo
            ");
            $stmt->execute(['id_prestamo' => $id_prestamo]);

            // Actualizar la disponibilidad del libro
            $stmt = $conn->prepare("
                UPDATE libros 
                SET disponible = 1 
                WHERE isbn = :isbn
            ");
            $stmt->execute(['isbn' => $isbn]);

            // Actualizar el estado de la multa a "pagada"
            $stmt = $conn->prepare("
                UPDATE multas 
                SET estado = 'pagada', fecha_pago = NOW() 
                WHERE id_prestamo = :id_prestamo
            ");
            $stmt->execute(['id_prestamo' => $id_prestamo]);

            $success = "Pago confirmado y devolución registrada exitosamente. La disponibilidad del libro ha sido actualizada.";
        } else {
            $error = "Contraseña de administrador incorrecta.";
        }
    } catch (PDOException $e) {
        $error = "Error al confirmar el pago: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Devolver Libro</title>
</head>
<body>
    <div class="container">
        <h1>Devolver Libro</h1>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!$mostrar_formulario_pago): ?>
            <form method="POST" action="devolver_libro.php">
                <input type="hidden" name="devolver_libro" value="1">
                <label for="num_control">Número de Control del Estudiante:</label>
                <input type="text" id="num_control" name="num_control" required>

                <label for="id_libro">ISBN del Libro:</label>
                <input type="text" id="id_libro" name="id_libro" required>

                <button type="submit">Registrar Devolución</button>
                <a href="menu_empleados.php" class="button">Regresar al Menú</a>
            </form>
        <?php else: ?>
            <h2>Confirmar Pago de Multa</h2>
            <form method="POST" action="devolver_libro.php">
                <input type="hidden" name="confirmar_pago" value="1">
                <input type="hidden" name="id_prestamo" value="<?php echo htmlspecialchars($prestamo_atrasado['id_prestamo']); ?>">
                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($prestamo_atrasado['isbn']); ?>">

                <label for="admin_password">Contraseña del Administrador:</label>
                <input type="password" id="admin_password" name="admin_password" required>

                <button type="submit">Confirmar Pago</button>
                <a href="menu_empleados.php" class="button">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
