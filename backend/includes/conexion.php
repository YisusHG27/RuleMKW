<?php
    // Configuración bd
    $host = "localhost";
    $usuario_bd = 'root';
    $password_bd = '';
    $nombre_bd = 'rulemkw';

    $enlace = new mysqli($host, $usuario_bd, $password_bd, $nombre_bd);

    // Verificar conexión
    if ($enlace->connect_error) {
        die("Error de conexión a la base de datos: " . $enlace->connect_error);
    }
    $enlace->set_charset("utf8mb4");
?>