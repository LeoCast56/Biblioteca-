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
$alumno = [];
$libro = [];
$alumnos_resultados = [];
$libros_resultados = [];

// Procesar el formulario de búsqueda de alumnos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_alumno'])) {
    $campo_alumno = $_POST['campo_alumno'];
    $valor_alumno = $_POST['valor_alumno'];

    try {
        // Buscar alumnos por el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM personas WHERE $campo_alumno LIKE :valor");
        $stmt->execute(['valor' => "%$valor_alumno%"]);
        $alumnos_resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($alumnos_resultados)) {
            $error = "No se encontraron alumnos con el criterio proporcionado.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar alumnos: " . $e->getMessage();
    }
}

// Procesar el formulario de búsqueda de libros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_libro'])) {
    $campo_libro = $_POST['campo_libro'];
    $valor_libro = $_POST['valor_libro'];

    try {
        // Buscar libros por el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM libros WHERE $campo_libro LIKE :valor");
        $stmt->execute(['valor' => "%$valor_libro%"]);
        $libros_resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($libros_resultados)) {
            $error = "No se encontraron libros con el criterio proporcionado.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar libros: " . $e->getMessage();
    }
}

// Procesar el formulario de préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_prestamo'])) {
    $num_control = $_POST['num_control'];
    $isbn = $_POST['isbn'];
    $dias_prestamo = $_POST['dias_prestamo'];
    $multa = $_POST['multa'];

    try {
        // Verificar si el alumno tiene multas pendientes
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS multas_pendientes
            FROM multas m
            INNER JOIN prestamos p ON m.id_prestamo = p.id_prestamo
            WHERE p.num_control = :num_control AND m.estado = 'pendiente'
        ");
        $stmt->execute(['num_control' => $num_control]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado['multas_pendientes'] > 0) {
            $error = "El alumno tiene multas pendientes. No se puede registrar el préstamo.";
        } else {
            // Verificar si el libro está disponible
            $stmt = $conn->prepare("SELECT * FROM libros WHERE isbn = :isbn AND disponible = 1");
            $stmt->execute(['isbn' => $isbn]);
            $libro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($libro) {
                // Registrar el préstamo
                $stmt = $conn->prepare("INSERT INTO prestamos (num_control, isbn, matricula, fecha_prestamo, fecha_devolucion, estado) 
                                        VALUES (:num_control, :isbn, :matricula, NOW(), DATE_ADD(NOW(), INTERVAL :dias DAY), 'activo')");
                $stmt->execute([
                    'num_control' => $num_control,
                    'isbn' => $isbn,
                    'matricula' => $_SESSION['empleado']['matricula'],
                    'dias' => $dias_prestamo
                ]);

                // Marcar el libro como no disponible
                $stmt = $conn->prepare("UPDATE libros SET disponible = 0 WHERE isbn = :isbn");
                $stmt->execute(['isbn' => $isbn]);

                $success = "Préstamo registrado exitosamente.";
            } else {
                $error = "El libro no está disponible.";
            }
        }
    } catch (PDOException $e) {
        $error = "Error al registrar el préstamo: " . $e->getMessage();
    }
}

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
    <title>Gestionar Préstamos</title>
    <style>
        /* Ajustar el contenedor principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            height: 100vh; /* Altura completa de la ventana */
            overflow-y: scroll; /* Barra de desplazamiento vertical */
            box-sizing: border-box; /* Incluir padding en el cálculo del tamaño */
        }

        /* Estilo para los formularios */
        form {
            display: flex;
            flex-wrap: nowrap; /* Evitar que los elementos se envuelvan */
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
            white-space: nowrap; /* Evitar que el texto del label se divida en varias líneas */
        }

        input, select, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input, select {
            flex: 1; /* Permitir que los campos ocupen el espacio disponible */
            min-width: 150px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 5px;
            white-space: nowrap; /* Evitar que el texto del botón se divida */
        }

        button:hover {
            background-color: #0056b3;
        }

        .button {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #5a6268;
        }

        /* Estilo para las listas de resultados */
        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            form {
                flex-wrap: wrap; /* Permitir que los elementos se envuelvan en pantallas pequeñas */
            }

            input, select, button {
                flex: 1 1 100%; /* Hacer que los elementos ocupen toda la fila */
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestionar Préstamos</h1>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Formulario de búsqueda de alumnos -->
        <h2>Buscar Alumno</h2>
        <form method="POST" action="gestionar_prestamos.php">
            <input type="hidden" name="buscar_alumno" value="1">
            <label for="campo_alumno">Buscar por:</label>
            <select id="campo_alumno" name="campo_alumno" required>
                <option value="num_control">Número de Control</option>
                <option value="nombre">Nombre</option>
                <option value="email">Correo Electrónico</option>
            </select>
            <input type="text" id="valor_alumno" name="valor_alumno" placeholder="Ingrese el valor" required>
            <button type="submit">Buscar Alumno</button>
        </form>

        <!-- Mostrar resultados de búsqueda de alumnos -->
        <?php if (!empty($alumnos_resultados)): ?>
            <h3>Resultados de Alumnos</h3>
            <ul>
                <?php foreach ($alumnos_resultados as $alumno): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($alumno['nombre']); ?></strong> 
                        (Número de Control: <?php echo htmlspecialchars($alumno['num_control']); ?>, 
                        Correo: <?php echo htmlspecialchars($alumno['email']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Formulario de búsqueda de libros -->
        <h2>Buscar Libro</h2>
        <form method="POST" action="gestionar_prestamos.php">
            <input type="hidden" name="buscar_libro" value="1">
            <label for="campo_libro">Buscar por:</label>
            <select id="campo_libro" name="campo_libro" required>
                <option value="isbn">ISBN</option>
                <option value="titulo">Título</option>
                <option value="autor">Autor</option>
                <option value="genero">Género</option>
            </select>
            <input type="text" id="valor_libro" name="valor_libro" placeholder="Ingrese el valor" required>
            <button type="submit">Buscar Libro</button>
        </form>

        <!-- Mostrar resultados de búsqueda de libros -->
        <?php if (!empty($libros_resultados)): ?>
            <h3>Resultados de Libros</h3>
            <ul>
                <?php foreach ($libros_resultados as $libro): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong> 
                        (ISBN: <?php echo htmlspecialchars($libro['isbn']); ?>, 
                        Autor: <?php echo htmlspecialchars($libro['autor']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Formulario para registrar el préstamo -->
        <h2>Registrar Préstamo</h2>
        <form method="POST" action="gestionar_prestamos.php">
            <label for="num_control">Número de Control del Estudiante:</label>
            <input type="text" id="num_control" name="num_control" required>

            <label for="isbn">ISBN del Libro:</label>
            <input type="text" id="isbn" name="isbn" required>

            <label for="dias_prestamo">Días de Préstamo:</label>
            <input type="number" id="dias_prestamo" name="dias_prestamo" min="1" required>

            <label for="multa">Multa en caso de no devolución (en dólares):</label>
            <input type="number" id="multa" name="multa" step="0.01" required>

            <button type="submit" name="registrar_prestamo">Registrar Préstamo</button>
            <a href="menu_empleados.php" class="button">Regresar al menú</a>
        </form>
    </div>
</body>
</html>