<?php
session_start();

// Verificar si el empleado ha iniciado sesión
if (!isset($_SESSION['empleado'])) {
    header("Location: login_empleados.php");
    exit();
}

require 'conexion.php';

$resultados = []; // Variable para almacenar los resultados de la búsqueda
$error = ""; // Variable para almacenar mensajes de error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = "Error al consultar libros: " . $e->getMessage();
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
    <style>
        /* Contenedor principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Formulario de búsqueda */
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-form label {
            font-weight: bold;
        }

        .search-form select, .search-form input, .search-form button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #0056b3;
        }

        /* Tabla de resultados */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Mensajes de error */
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Buscar Libros</h1>

        <!-- Mostrar mensajes de error -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <form method="POST" action="consultar_libros.php" class="search-form">
            <label for="campo">Buscar por:</label>
            <select id="campo" name="campo" required>
                <option value="titulo">Título</option>
                <option value="autor">Autor</option>
                <option value="genero">Género</option>
                <option value="fecha_publicacion">Fecha de Publicación</option>
                <option value="editorial">Editorial</option>
            </select>
            <label for="valor">Valor:</label>
            <input type="text" id="valor" name="valor" placeholder="Ingrese el valor de búsqueda" required>
            <button type="submit">Buscar</button>
        </form>

        <!-- Mostrar resultados -->
        <?php if (!empty($resultados)): ?>
            <h2>Resultados de la búsqueda</h2>
            <div class="table-container">
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $libro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                <td><?php echo htmlspecialchars($libro['genero']); ?></td>
                                <td><?php echo htmlspecialchars($libro['fecha_publicacion']); ?></td>
                                <td><?php echo htmlspecialchars($libro['paginas']); ?></td>
                                <td><?php echo htmlspecialchars($libro['editorial']); ?></td>
                                <td><?php echo htmlspecialchars($libro['sinopsis']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>