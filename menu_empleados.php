<?php
session_start();

// Verificar si el empleado ha iniciado sesión
if (!isset($_SESSION['empleado'])) {
    header("Location: login_empleados.php");
    exit();
}

require 'conexion.php';

// Obtener el nombre del bibliotecario actual
$nombre_bibliotecario = $_SESSION['empleado']['nombre'];
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Menú de Bibliotecarios</title>
    <style>
        /* Estilo del encabezado */
        .header {
            background-color: #343a40;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 1.5em;
            margin: 0;
            color: #f8f9fa; /* Cambiado a un blanco claro */
        }

        .header nav {
            display: flex;
            gap: 15px;
        }

        .header nav .menu-item {
            position: relative;
        }

        .header nav .menu-item button {
            background-color: transparent;
            color: white;
            border: none;
            font-size: 1em;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .header nav .menu-item button:hover {
            background-color: #495057;
        }

        .header nav .menu-item .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #343a40;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .header nav .menu-item .dropdown a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .header nav .menu-item .dropdown a:hover {
            background-color: #495057;
        }

        .header nav .menu-item:hover .dropdown {
            display: block;
        }

        .header .logout {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .header .logout:hover {
            background-color: #c82333;
        }

        /* Estilo del contenido */
        .content {
            margin-top: 70px; /* Espacio para el encabezado fijo */
            padding: 20px;
        }

        .content h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <!-- Encabezado fijo -->
    <div class="header">
        <h1>Menú de Bibliotecarios</h1>
        <nav>
            <div class="menu-item">
                <button>Gestión de Libros</button>
                <div class="dropdown">
                    <a href="agregar_libro.php">Agregar Libro</a>
                    <a href="consultar_libros.php">Consultar Libros</a>
                    <a href="actualizar_libro.php">Actualizar Libro</a>
                    <a href="eliminar_libro.php">Eliminar Libro</a> <!-- Nuevo enlace agregado -->
                </div>
            </div>
            <div class="menu-item">
                <button>Gestión de Alumnos</button>
                <div class="dropdown">
                    <a href="registro_alumnos.php">Registrar Alumno</a>
                    <a href="consultar_alumnos.php">Consultar Alumnos</a>
                    <a href="actualizar_alumno.php">Actualizar Alumno</a>
                    <a href="eliminar_alumno.php">Eliminar Alumno</a> <!-- Nuevo enlace agregado -->
                </div>
            </div>
            <div class="menu-item">
                <button>Gestión de Empleados</button>
                <div class="dropdown">
                    <a href="registro_empleados.php">Registrar Empleado</a>
                    <a href="consultar_empleados.php">Consultar Empleados</a>
                    <a href="actualizar_empleado.php">Actualizar Empleado</a>
                    <a href="eliminar_empleado.php">Eliminar Empleado</a> <!-- Nuevo enlace agregado -->
                </div>
            </div>
            <div class="menu-item">
                <button>Gestión de Préstamos</button>
                <div class="dropdown">
                    <a href="gestionar_prestamos.php">Gestionar Préstamos</a>
                    <a href="devolver_libro.php">Devolver Libro</a><
                    <a href="consultar_multas.php">Consultar Multas</a>
                </div>
            </div>
            <a href="logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Contenido -->
    <div class="content">
        <h2>Bienvenido, <?php echo htmlspecialchars($nombre_bibliotecario); ?>!</h2>
        <p>Selecciona una opción del menú para comenzar.</p>
    </div>
</body>
</html>