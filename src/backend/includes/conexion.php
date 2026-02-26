<?php

$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    require_once 'Logger.php';
} else {
    require_once 'LoggerSimple.php';
}

// Usar el usuario específico creado en docker-compose
$host = 'mysql';
$usuario = 'rulemkw_user';      // MYSQL_USER del .env
$password = 'password_segura';  // MYSQL_PASSWORD del .env
$base_datos = 'rulemkw';         // MYSQL_DATABASE del .env

$enlace = new mysqli($host, $usuario, $password, $base_datos);

if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}

$enlace->set_charset("utf8mb4");

// ✅ ELIMINADO: AppLogger::init($enlace); - Ya no es necesario
?>