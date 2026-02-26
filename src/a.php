<?php
// test_timezone.php
echo "Zona horaria de PHP: " . date_default_timezone_get() . "<br>";
echo "Hora actual: " . date('Y-m-d H:i:s') . "<br>";
echo "Hora UTC: " . gmdate('Y-m-d H:i:s') . "<br>";

require_once 'backend/includes/logger.php';
AppLogger::info("Test de zona horaria", [
    'zona_php' => date_default_timezone_get(),
    'hora_php' => date('Y-m-d H:i:s'),
    'hora_utc' => gmdate('Y-m-d H:i:s')
]);
echo "Log de prueba creado. Revisa el archivo.";