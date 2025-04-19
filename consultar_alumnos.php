<?php
session_start();

// Verificar si el empleado ha iniciado sesión
if (!isset($_SESSION['empleado'])) {
    header("Location: login_empleados.php");
    exit();
}

require 'conexion.php';

$error = ""; // Variable para almacenar mensajes de error
$alumno = []; // Variable para almacenar los datos del alumno
$mostrar_formulario = false; // Variable para controlar si se muestra el formulario

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    // Validar el campo seleccionado
    $campos_validos = ['num_control', 'nombre', 'email'];
    if (!in_array($campo, $campos_validos)) {
        $error = "Campo de búsqueda no válido.";
    } else {
        try {
            // Consultar el alumno según el campo seleccionado
            $stmt = $conn->prepare("
                SELECT p.num_control, p.nombre, p.telefono, p.email, p.fecha_registro, p.estado, 
                       COUNT(pr.id_prestamo) AS prestamos_pendientes
                FROM personas p
                LEFT JOIN prestamos pr ON p.num_control = pr.num_control AND pr.fecha_devolucion IS NULL
                WHERE p.$campo LIKE :valor
                GROUP BY p.num_control
            ");
            $stmt->execute(['valor' => "%$valor%"]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($alumno) {
                $mostrar_formulario = true; // Mostrar el formulario si se encuentra el alumno
            } else {
                $error = "No se encontró ningún alumno con el criterio proporcionado.";
            }
        } catch (PDOException $e) {
            $error = "Error al buscar el alumno: " . $e->getMessage();
        }
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
    <title>Consultar Alumno</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-group div {
            flex: 1 1 calc(50% - 20px);
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[readonly], select[disabled] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Consultar Alumno</h1>

        <!-- Mostrar mensajes de error -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para buscar un alumno -->
        <?php if (!$mostrar_formulario): ?>
            <form method="POST" action="consultar_alumnos.php">
                <input type="hidden" name="buscar" value="1">
                <label for="campo">Buscar por:</label>
                <select id="campo" name="campo" required>
                    <option value="num_control">Número de Control</option>
                    <option value="nombre">Nombre</option>
                    <option value="email">Correo Electrónico</option>
                </select>
                <label for="valor">Valor:</label>
                <input type="text" id="valor" name="valor" required>
                <button type="submit">Buscar</button>
            </form>
        <?php endif; ?>

        <!-- Formulario para mostrar los datos del alumno -->
        <?php if ($mostrar_formulario): ?>
            <form>
                <div class="form-group">
                    <div>
                        <label for="num_control">Número de Control:</label>
                        <input type="text" id="num_control" name="num_control" value="<?php echo htmlspecialchars($alumno['num_control']); ?>" readonly>
                    </div>
                    <div>
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($alumno['nombre']); ?>" readonly>
                    </div>
                    <div>
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($alumno['telefono']); ?>" readonly>
                    </div>
                    <div>
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($alumno['email']); ?>" readonly>
                    </div>
                    <div>
                        <label for="fecha_registro">Fecha de Registro:</label>
                        <input type="text" id="fecha_registro" name="fecha_registro" value="<?php echo htmlspecialchars($alumno['fecha_registro']); ?>" readonly>
                    </div>
                    <div>
                        <label for="estado">Estado:</label>
                        <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($alumno['estado']); ?>" readonly>
                    </div>
                    <div>
                        <label for="prestamos_pendientes">Préstamos Pendientes:</label>
                        <input type="text" id="prestamos_pendientes" name="prestamos_pendientes" value="<?php echo htmlspecialchars($alumno['prestamos_pendientes']); ?>" readonly>
                    </div>
                </div>
                <button type="button" onclick="window.location.href='consultar_alumnos.php';">Nueva Búsqueda</button>
            </form>
        <?php endif; ?>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>