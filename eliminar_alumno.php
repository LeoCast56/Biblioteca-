<?php
session_start();

// Verificar si el empleado ha iniciado sesión
if (!isset($_SESSION['empleado'])) {
    header("Location: login_empleados.php");
    exit();
}

require 'conexion.php';

$error = ""; // Variable para almacenar mensajes de error
$success = ""; // Variable para almacenar mensajes de éxito
$resultados = []; // Variable para almacenar los resultados de la búsqueda

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    try {
        // Consultar alumnos según el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM personas WHERE $campo LIKE :valor");
        $stmt->execute(['valor' => "%$valor%"]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            $error = "No se encontraron resultados.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar alumnos: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $num_control = $_POST['num_control'] ?? '';

    try {
        // Eliminar el alumno
        $stmt = $conn->prepare("DELETE FROM personas WHERE num_control = :num_control");
        $stmt->execute(['num_control' => $num_control]);

        $success = "El alumno con número de control $num_control ha sido eliminado correctamente.";
    } catch (PDOException $e) {
        $error = "Error al eliminar el alumno: " . $e->getMessage();
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
    <title>Eliminar Alumno</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
            font-weight: bold;
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

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Eliminar Alumno</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para buscar alumnos -->
        <form method="POST" action="eliminar_alumno.php">
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

        <!-- Mostrar resultados de la búsqueda -->
        <?php if (!empty($resultados)): ?>
            <h2>Resultados de la búsqueda</h2>
            <table>
                <thead>
                    <tr>
                        <th>Número de Control</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Fecha de Registro</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $alumno): ?>
                        <tr>
                            <td><?php echo $alumno['num_control']; ?></td>
                            <td><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['telefono']; ?></td>
                            <td><?php echo $alumno['email']; ?></td>
                            <td><?php echo $alumno['fecha_registro']; ?></td>
                            <td><?php echo $alumno['estado']; ?></td>
                            <td>
                                <form method="POST" action="eliminar_alumno.php" style="display:inline;">
                                    <input type="hidden" name="eliminar" value="1">
                                    <input type="hidden" name="num_control" value="<?php echo $alumno['num_control']; ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="menu_empleados.php" class="button">Regresar al menú</a></p>
    </div>
</body>
</html>