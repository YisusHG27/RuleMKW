<?php
// check-docker.php
echo "<h1>Verificación Docker</h1>";

// 1. Verificar PHP
echo "<h2>PHP Info:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Extension mysqli: " . (extension_loaded('mysqli') ? 'Cargada ✓' : 'NO cargada ✗') . "<br>";

// 2. Probar conexión a MySQL
echo "<h2>Prueba de Conexión MySQL:</h2>";
$host = 'mysql';
$user = 'usuario';
$pass = 'contrasena';
$db = 'rulemkw';

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "Error conexión: " . $conn->connect_error . "<br>";
    echo "Código error: " . $conn->connect_errno . "<br>";
} else {
    echo "Conexión exitosa ✓<br>";
    echo "Server version: " . $conn->server_info . "<br>";
    echo "Host info: " . $conn->host_info . "<br>";
    
    // Mostrar bases de datos
    $result = $conn->query("SHOW DATABASES");
    echo "<h3>Bases de datos:</h3>";
    while ($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
    $conn->close();
}

// 3. Verificar archivos
echo "<h2>Estructura de archivos:</h2>";
function listFiles($dir, $prefix = '') {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo $prefix . $file . "<br>";
            if (is_dir($dir . '/' . $file)) {
                listFiles($dir . '/' . $file, $prefix . '&nbsp;&nbsp;&nbsp;');
            }
        }
    }
}

echo "<strong>/var/www/html:</strong><br>";
listFiles('/var/www/html');
?>