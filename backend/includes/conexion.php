<?php
    // Configuración para Docker
    $host = "db";
    $usuario_bd = 'usuario';
    $password_bd = 'pass';
    $nombre_bd = 'rulemkw';

    $enlace = new mysqli($host, $usuario_bd, $password_bd, $nombre_bd);

    // Verificar conexión
    if ($enlace->connect_error) {
        die("Error de conexión a la base de datos: " . $enlace->connect_error);
    }
?>