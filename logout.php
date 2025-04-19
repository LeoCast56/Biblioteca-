<?php
session_start();

// Destruir la sesión
session_destroy();

// Redirigir al inicio
header("Location: index.php");
exit();
?>