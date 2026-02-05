<?php
    // conexion/conexion.php

    // Configuración para Docker
    $host = getenv('DB_HOST') ?: 'mysql';  // 'mysql' es el nombre del servicio en docker-compose
    $usuario_bd = getenv('DB_USER') ?: 'usuariomkw';
    $password_bd = getenv('DB_PASSWORD') ?: 'contrasena';
    $nombre_bd = getenv('DB_NAME') ?: 'rulemkw';

    // **IMPORTANTE**: Versión compatible con desarrollo local (XAMPP) y Docker
    if (getenv('DOCKER_ENV') === 'true') {
        // Configuración para Docker
        $host = 'mysql'; // Nombre del servicio en docker-compose
        $usuario_bd = 'usuario';
        $password_bd = 'contrasena';
    } else {
        // Configuración para XAMPP local
        $host = 'localhost';
        $usuario_bd = 'root';
        $password_bd = '';
    }

    $enlace = new mysqli($host, $usuario_bd, $password_bd, $nombre_bd);

    // Verificar conexión
    if ($enlace->connect_error) {
        die("Error de conexión a la base de datos: " . $enlace->connect_error);
    }

    // Configurar charset
    $enlace->set_charset("utf8");
?>
