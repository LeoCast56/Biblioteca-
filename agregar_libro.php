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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = $_POST['isbn'] ?? '';
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
            // Insertar el nuevo libro en la base de datos
            $stmt = $conn->prepare("INSERT INTO libros (isbn, titulo, autor, genero, fecha_publicacion, paginas, editorial, sinopsis) VALUES (:isbn, :titulo, :autor, :genero, :fecha_publicacion, :paginas, :editorial, :sinopsis)");
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
            $success = "El libro ha sido registrado exitosamente.";
        } catch (PDOException $e) {
            $error = "Error al agregar el libro: " . $e->getMessage();
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
    <title>Agregar Libro</title>
    <style>
        /* Ajustar el contenedor principal */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            height: 100vh; /* Altura completa de la ventana */
            overflow-y: scroll; /* Barra de desplazamiento vertical */
            box-sizing: border-box; /* Incluir padding en el cálculo del tamaño */
        }
    </style>
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
        <h1>Agregar Libro</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario para agregar un libro -->
        <form method="POST" action="agregar_libro.php">
            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" oninput="validarISBN(this)" required>

            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="autor">Autor:</label>
            <input type="text" id="autor" name="autor" oninput="validarTexto(this, 'Autor')" required>

            <label for="genero">Género:</label>
            <input type="text" id="genero" name="genero" oninput="validarTexto(this, 'Género')" required>

            <label for="fecha_publicacion">Fecha de Publicación:</label>
            <input type="date" id="fecha_publicacion" name="fecha_publicacion" onchange="validarFecha(this)" required>

            <label for="paginas">Páginas:</label>
            <input type="number" id="paginas" name="paginas" oninput="validarPaginas(this)" required>

            <label for="editorial">Editorial:</label>
            <input type="text" id="editorial" name="editorial" required>

            <label for="sinopsis">Sinopsis:</label>
            <textarea id="sinopsis" name="sinopsis" required></textarea>

            <button type="submit">Agregar Libro</button>
        </form>

        <p><a href="menu_empleados.php">Regresar al menú</a></p>
    </div>
</body>
</html>