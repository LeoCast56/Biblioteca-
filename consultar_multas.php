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
$multas_resultados = [];

// Procesar el formulario de consulta de multas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultar_multas'])) {
    $num_control = trim($_POST['num_control']);

    try {
        // Consultar multas pendientes del alumno
        $stmt = $conn->prepare("
            SELECT m.id_multa, m.monto, m.fecha_generada, m.estado, p.isbn, p.fecha_prestamo, p.fecha_devolucion
            FROM multas m
            INNER JOIN prestamos p ON m.id_prestamo = p.id_prestamo
            WHERE p.num_control = :num_control AND m.estado = 'pendiente'
        ");
        $stmt->execute(['num_control' => $num_control]);
        $multas_resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($multas_resultados)) {
            $success = "El alumno no tiene multas pendientes.";
        }
    } catch (PDOException $e) {
        $error = "Error al consultar las multas: " . $e->getMessage();
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
    <title>Consultar Multas</title>
</head>
<body>
    <div class="container">
        <h1>Consultar Multas</h1>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Formulario para consultar multas -->
        <form method="POST" action="consultar_multas.php">
            <label for="num_control">Número de Control del Estudiante:</label>
            <input type="text" id="num_control" name="num_control" required>
            <button type="submit" name="consultar_multas">Consultar Multas</button>
        </form>

        <!-- Mostrar resultados de multas -->
        <?php if (!empty($multas_resultados)): ?>
            <h3>Multas Pendientes</h3>
            <ul>
                <?php foreach ($multas_resultados as $multa): ?>
                    <li>
                        <strong>Multa ID:</strong> <?php echo htmlspecialchars($multa['id_multa']); ?> - 
                        <strong>Monto:</strong> $<?php echo htmlspecialchars($multa['monto']); ?> - 
                        <strong>Fecha Generada:</strong> <?php echo htmlspecialchars($multa['fecha_generada']); ?> - 
                        <strong>ISBN:</strong> <?php echo htmlspecialchars($multa['isbn']); ?> - 
                        <strong>Estado:</strong> <?php echo htmlspecialchars($multa['estado']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="menu_empleados.php" class="button">Regresar al Menú</a>
    </div>
</body>
</html>