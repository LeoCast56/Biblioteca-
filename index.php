<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="imagenes/2821c92b-0913-46b6-b9a4-47378b3eb04d.png">
    <link rel="stylesheet" href="styles.css">
    <title>Inicio - Biblioteca</title>
    <style>
        /* Estilos para centrar el contenido */
        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        /* Estilos para el botón */
        .button {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            background-color: #007BFF;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenido a la Biblioteca</h1>
        <a href="login_empleados.php" class="button">Continuar</a>
    </div>
</body>
</html>