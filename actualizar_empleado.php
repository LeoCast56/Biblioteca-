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
$empleado = []; // Variable para almacenar los datos del empleado
$mostrar_formulario = false; // Variable para controlar si se muestra el formulario

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    try {
        // Consultar el empleado según el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM empleados WHERE $campo LIKE :valor");
        $stmt->execute(['valor' => "%$valor%"]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            $mostrar_formulario = true; // Mostrar el formulario si se encuentra el empleado
        } else {
            $error = "Empleado no encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar el empleado: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $matricula = $_POST['matricula'];
    $nombre = $_POST['nombre'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $rol = $_POST['rol'] ?? 'bibliotecario';

    try {
        // Actualizar el empleado en la base de datos
        $stmt = $conn->prepare("UPDATE empleados SET nombre = :nombre, clave = :clave, rol = :rol WHERE matricula = :matricula");
        $stmt->execute([
            'matricula' => $matricula,
            'nombre' => $nombre,
            'clave' => $clave, // Clave en texto plano
            'rol' => $rol
        ]);

        $success = "El empleado ha sido actualizado exitosamente.";
    } catch (PDOException $e) {
        $error = "Error al actualizar el empleado: " . $e->getMessage();
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
    <title>Actualizar Empleado</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
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
        <h1>Actualizar Empleado</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para buscar un empleado -->
        <?php if (!$mostrar_formulario): ?>
            <form method="POST" action="actualizar_empleado.php">
                <input type="hidden" name="buscar" value="1">
                <label for="campo">Buscar por:</label>
                <select id="campo" name="campo" required>
                    <option value="matricula">Matrícula</option>
                    <option value="nombre">Nombre</option>
                </select>
                <label for="valor">Valor:</label>
                <input type="text" id="valor" name="valor" required>
                <button type="submit">Buscar</button>
            </form>
        <?php endif; ?>

        <!-- Formulario para actualizar el empleado -->
        <?php if ($mostrar_formulario): ?>
            <form method="POST" action="actualizar_empleado.php">
                <input type="hidden" name="actualizar" value="1">
                <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($empleado['matricula']); ?>">

                <div class="form-group">
                    <div>
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required>
                    </div>
                    <div>
                        <label for="clave">Clave:</label>
                        <input type="text" id="clave" name="clave" value="<?php echo htmlspecialchars($empleado['clave']); ?>" required>
                    </div>
                    <div>
                        <label for="rol">Rol:</label>
                        <select id="rol" name="rol" required>
                            <option value="bibliotecario" <?php echo $empleado['rol'] === 'bibliotecario' ? 'selected' : ''; ?>>Bibliotecario</option>
                            <option value="admin" <?php echo $empleado['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>
                </div>
                <button type="submit">Actualizar Empleado</button>
            </form>
        <?php endif; ?>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>