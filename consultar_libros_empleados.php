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
</head>
<body>
    <div class="container">
        <h1>Consultar Libros</h1>

        <!-- Mostrar mensajes de error -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <form method="POST" action="consultar_libros_empleados.php">
            <label for="campo">Buscar por:</label>
            <select id="campo" name="campo" required>
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

        <!-- Mostrar resultados -->
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>