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
    // Procesar la búsqueda del libro
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    try {
        // Consultar libros según el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM libros WHERE $campo LIKE :valor");
        $stmt->execute(['valor' => "%$valor%"]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            $error = "No se encontraron resultados.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar libros: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    // Procesar la eliminación del libro
    $isbn = $_POST['isbn'] ?? '';

    // Validar que el ISBN tenga exactamente 13 dígitos
    if (!preg_match('/^\d{13}$/', $isbn)) {
        $error = "El ISBN debe ser exactamente de 13 dígitos.";
    } else {
        try {
            // Verificar si el libro existe
            $stmt = $conn->prepare("SELECT * FROM libros WHERE isbn = :isbn");
            $stmt->execute(['isbn' => $isbn]);
            $libro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($libro) {
                // Eliminar el libro
                $stmt = $conn->prepare("DELETE FROM libros WHERE isbn = :isbn");
                $stmt->execute(['isbn' => $isbn]);

                $success = "El libro con ISBN $isbn ha sido eliminado correctamente.";
            } else {
                $error = "No se encontró un libro con el ISBN proporcionado.";
            }
        } catch (PDOException $e) {
            $error = "Error al eliminar el libro: " . $e->getMessage();
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
    <title>Eliminar Libro</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        form {
            margin-bottom: 20px;
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
        <h1>Eliminar Libro</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para buscar libros -->
        <form method="POST" action="eliminar_libro.php">
            <input type="hidden" name="buscar" value="1">
            <label for="campo">Buscar por:</label>
            <select id="campo" name="campo" required>
                <option value="isbn">ISBN</option>
                <option value="titulo">Título</option>
                <option value="autor">Autor</option>
                <option value="genero">Género</option>
                <option value="fecha_publicacion">Fecha de Publicación</option>
                <option value="editorial">Editorial</option>
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
                        <th>ISBN</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Género</th>
                        <th>Fecha de Publicación</th>
                        <th>Páginas</th>
                        <th>Editorial</th>
                        <th>Sinopsis</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $libro): ?>
                        <tr>
                            <td><?php echo $libro['isbn']; ?></td>
                            <td><?php echo $libro['titulo']; ?></td>
                            <td><?php echo $libro['autor']; ?></td>
                            <td><?php echo $libro['genero']; ?></td>
                            <td><?php echo $libro['fecha_publicacion']; ?></td>
                            <td><?php echo $libro['paginas']; ?></td>
                            <td><?php echo $libro['editorial']; ?></td>
                            <td><?php echo $libro['sinopsis']; ?></td>
                            <td>
                                <form method="POST" action="eliminar_libro.php" style="display:inline;">
                                    <input type="hidden" name="eliminar" value="1">
                                    <input type="hidden" name="isbn" value="<?php echo $libro['isbn']; ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Botón para regresar al menú -->
        <p><a href="menu_empleados.php" class="button">Regresar al menú</a></p>
    </div>
</body>
</html>