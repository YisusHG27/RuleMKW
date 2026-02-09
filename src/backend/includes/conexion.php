<?php
// CONFIGURACIÓN PARA DOCKER - CAMBIA ESTO
$host = "mysql";        // ← IMPORTANTE: "mysql" es el nombre del contenedor
$usuario_bd = "rulemkw_user";
$password_bd = "password_segura";
$nombre_bd = "rulemkw";

$enlace = new mysqli($host, $usuario_bd, $password_bd, $nombre_bd);

if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error . 
        "<br>Host: " . $host . 
        "<br>Usuario: " . $usuario_bd);
}

$enlace->set_charset("utf8mb4");
?>