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
$libro = []; // Variable para almacenar los datos del libro
$mostrar_formulario = false; // Variable para controlar si se muestra el formulario

// Procesar la búsqueda del libro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['campo']) && isset($_POST['valor'])) {
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    try {
        // Consultar libros según el campo seleccionado
        $stmt = $conn->prepare("SELECT * FROM libros WHERE $campo LIKE :valor");
        $stmt->execute(['valor' => "%$valor%"]);
        $libro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($libro) {
            $mostrar_formulario = true; // Mostrar el formulario si se encuentra el libro
        } else {
            $error = "Libro no encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Error al buscar el libro: " . $e->getMessage();
    }
}

// Procesar la actualización del libro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isbn'])) {
    $isbn = $_POST['isbn'];
    $titulo = $_POST['titulo'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $fecha_publicacion = $_POST['fecha_publicacion'] ?? '';
    $paginas = $_POST['paginas'] ?? 0;
    $editorial = $_POST['editorial'] ?? '';
    $sinopsis = $_POST['sinopsis'] ?? '';

    // Validaciones
    if (!preg_match('/^\d{13}$/', $isbn)) {
        $error = "El ISBN debe ser exactamente de 13 dígitos.";
    } elseif (preg_match('/\d/', $autor)) {
        $error = "El autor no puede contener números.";
    } elseif (preg_match('/\d/', $genero)) {
        $error = "El género no puede contener números.";
    } elseif (strtotime($fecha_publicacion) > time()) {
        $error = "La fecha de publicación no puede ser futura.";
    } elseif ($paginas < 0) {
        $error = "El número de páginas no puede ser negativo.";
    } else {
        try {
            // Actualizar el libro en la base de datos
            $stmt = $conn->prepare("UPDATE libros SET titulo = :titulo, autor = :autor, genero = :genero, fecha_publicacion = :fecha_publicacion, paginas = :paginas, editorial = :editorial, sinopsis = :sinopsis WHERE isbn = :isbn");
            $stmt->execute([
                'isbn' => $isbn,
                'titulo' => $titulo,
                'autor' => $autor,
                'genero' => $genero,
                'fecha_publicacion' => $fecha_publicacion,
                'paginas' => $paginas,
                'editorial' => $editorial,
                'sinopsis' => $sinopsis
            ]);

            // Mensaje de éxito
            $success = "El libro ha sido actualizado exitosamente.";
        } catch (PDOException $e) {
            $error = "Error al actualizar el libro: " . $e->getMessage();
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
    <title>Actualizar Libro</title>
    <script>
        // Validaciones en tiempo real
        function validarISBN(input) {
            const isbnRegex = /^\d{0,13}$/;
            if (!isbnRegex.test(input.value)) {
                alert("El ISBN debe contener solo números y un máximo de 13 dígitos.");
                input.value = input.value.slice(0, -1); // Eliminar el último carácter ingresado
            }
        }

        function validarTexto(input, campo) {
            const textoRegex = /^[a-zA-Z\s]*$/;
            if (!textoRegex.test(input.value)) {
                alert(`El campo ${campo} no puede contener números.`);
                input.value = input.value.slice(0, -1); // Eliminar el último carácter ingresado
            }
        }

        function validarFecha(input) {
            const fechaIngresada = new Date(input.value);
            const fechaActual = new Date();
            if (fechaIngresada > fechaActual) {
                alert("La fecha de publicación no puede ser futura.");
                input.value = ""; // Limpiar el campo
            }
        }

        function validarPaginas(input) {
            if (input.value < 0) {
                alert("El número de páginas no puede ser negativo.");
                input.value = ""; // Limpiar el campo
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Actualizar Libro</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para buscar el libro -->
        <?php if (!$mostrar_formulario): ?>
            <form method="POST" action="actualizar_libro.php">
                <label for="campo">Buscar por:</label>
                <select id="campo" name="campo" required>
                    <option value="isbn">ISBN</option>
                    <option value="titulo">Título</option>
                    <option value="autor">Autor</option>
                </select>
                <label for="valor">Valor:</label>
                <input type="text" id="valor" name="valor" required>
                <button type="submit">Buscar</button>
            </form>
        <?php endif; ?>

        <!-- Formulario para actualizar el libro -->
        <?php if ($mostrar_formulario): ?>
            <form method="POST" action="actualizar_libro.php">
                <input type="hidden" name="isbn" value="<?php echo $libro['isbn']; ?>">

                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo $libro['titulo']; ?>" required>

                <label for="autor">Autor:</label>
                <input type="text" id="autor" name="autor" value="<?php echo $libro['autor']; ?>" oninput="validarTexto(this, 'Autor')" required>

                <label for="genero">Género:</label>
                <input type="text" id="genero" name="genero" value="<?php echo $libro['genero']; ?>" oninput="validarTexto(this, 'Género')" required>

                <label for="fecha_publicacion">Fecha de Publicación:</label>
                <input type="date" id="fecha_publicacion" name="fecha_publicacion" value="<?php echo $libro['fecha_publicacion']; ?>" onchange="validarFecha(this)" required>

                <label for="paginas">Páginas:</label>
                <input type="number" id="paginas" name="paginas" value="<?php echo $libro['paginas']; ?>" oninput="validarPaginas(this)" required>

                <label for="editorial">Editorial:</label>
                <input type="text" id="editorial" name="editorial" value="<?php echo $libro['editorial']; ?>" required>

                <label for="sinopsis">Sinopsis:</label>
                <textarea id="sinopsis" name="sinopsis" required><?php echo $libro['sinopsis']; ?></textarea>

                <button type="submit">Actualizar Libro</button>
            </form>
        <?php endif; ?>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>