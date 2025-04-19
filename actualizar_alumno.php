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
$alumno = []; // Variable para almacenar los datos del alumno
$mostrar_formulario = false; // Variable para controlar si se muestra el formulario

// Procesar la búsqueda del alumno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    try {
        // Consultar alumnos según el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM personas WHERE $campo LIKE :valor");
        $stmt->execute(['valor' => "%$valor%"]);
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($alumno) {
            $mostrar_formulario = true; // Mostrar el formulario si se encuentra el alumno
        } else {
            $error = "Alumno no encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar el alumno: " . $e->getMessage();
    }
}

// Procesar la actualización del alumno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $num_control = $_POST['num_control'];
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';

    // Validaciones
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif (!preg_match('/^\d{10}$/', $telefono)) {
        $error = "El teléfono debe contener exactamente 10 dígitos.";
    } else {
        try {
            // Actualizar el alumno en la base de datos
            $stmt = $conn->prepare("UPDATE personas SET nombre = :nombre, telefono = :telefono, email = :email, estado = :estado WHERE num_control = :num_control");
            $stmt->execute([
                'num_control' => $num_control,
                'nombre' => $nombre,
                'telefono' => $telefono,
                'email' => $email,
                'estado' => $estado
            ]);

            $success = "El alumno ha sido actualizado exitosamente.";
        } catch (PDOException $e) {
            $error = "Error al actualizar el alumno: " . $e->getMessage();
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
    <link rel="stylesheet" href="styles.css">
    <title>Actualizar Alumno</title>
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

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
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
        <h1>Actualizar Alumno</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para buscar alumnos -->
        <?php if (!$mostrar_formulario): ?>
            <form method="POST" action="actualizar_alumno.php">
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

        <!-- Formulario para actualizar el alumno -->
        <?php if ($mostrar_formulario): ?>
            <form method="POST" action="actualizar_alumno.php">
                <input type="hidden" name="actualizar" value="1">
                <input type="hidden" name="num_control" value="<?php echo $alumno['num_control']; ?>">

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($alumno['nombre']); ?>" required>

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($alumno['telefono']); ?>" required>

                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($alumno['email']); ?>" required>

                <label for="estado">Estado:</label>
                <select id="estado" name="estado" required>
                    <option value="activo" <?php echo $alumno['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $alumno['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>

                <button type="submit">Actualizar Alumno</button>
            </form>
        <?php endif; ?>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>