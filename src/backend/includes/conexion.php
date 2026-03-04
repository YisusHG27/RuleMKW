<?php

$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    require_once 'Logger.php';
} else {
    require_once 'LoggerSimple.php';
}

$host = 'mysql';
$usuario = 'rulemkw_user';
$password = 'password_segura';
$base_datos = 'rulemkw';

$enlace = new mysqli($host, $usuario, $password, $base_datos);

if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}

$enlace->set_charset("utf8mb4");
?>