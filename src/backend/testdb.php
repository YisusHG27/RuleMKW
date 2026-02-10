<?php
include 'includes/conexion.php';

echo "<h1>Test de Base de Datos - RuleMKW</h1>";

// Test 1: Verificar conexión
echo "<h2>1. Verificación de conexión</h2>";
if ($enlace->ping()) {
    echo "<p style='color: green;'>✅ Conexión a MySQL exitosa</p>";
} else {
    echo "<p style='color: red;'>❌ Error de conexión: " . $enlace->error . "</p>";
}

// Test 2: Verificar tablas
echo "<h2>2. Verificación de tablas</h2>";
$tables = ['usuarios', 'circuitos', 'copas', 'estadisticas_usuario'];
$allTablesExist = true;

foreach ($tables as $table) {
    $result = $enlace->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Tabla '$table' encontrada</p>";
        
        // Contar registros
        $count = $enlace->query("SELECT COUNT(*) as total FROM $table");
        $row = $count->fetch_assoc();
        echo "<p style='margin-left: 20px;'>📊 Registros: " . $row['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla '$table' NO encontrada</p>";
        $allTablesExist = false;
    }
}

// Test 3: Verificar datos en circuitos y copas
echo "<h2>3. Datos de circuitos y copas</h2>";

// Copas
$result = $enlace->query("SELECT * FROM copas ORDER BY id");
echo "<h3>Copas:</h3>";
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No hay copas en la base de datos</p>";
}

// Circuitos
$result = $enlace->query("SELECT c.*, cop.nombre as copa_nombre FROM circuitos c JOIN copas cop ON c.id_copa = cop.id ORDER BY c.id");
echo "<h3>Circuitos:</h3>";
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Copa ID</th><th>Copa Nombre</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['id_copa'] . "</td>";
        echo "<td>" . $row['copa_nombre'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No hay circuitos en la base de datos</p>";
}

// Test 4: Verificar la consulta que usa get_circuitos.php
echo "<h2>4. Test de consulta de get_circuitos.php</h2>";
$query = "SELECT c.id as circuito_id, c.nombre as circuito_nombre, 
                 cop.id as copa_id, cop.nombre as copa_nombre
          FROM circuitos c
          JOIN copas cop ON c.id_copa = cop.id
          ORDER BY cop.id, c.id";

$result = $enlace->query($query);
if (!$result) {
    echo "<p style='color: red;'>❌ Error en la consulta: " . $enlace->error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Consulta ejecutada correctamente</p>";
    echo "<p>Filas encontradas: " . $result->num_rows . "</p>";
    
    // Mostrar primeras filas
    echo "<h3>Primeros 5 resultados:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Circuito ID</th><th>Circuito Nombre</th><th>Copa ID</th><th>Copa Nombre</th></tr>";
    
    $count = 0;
    while ($row = $result->fetch_assoc() && $count < 5) {
        echo "<tr>";
        echo "<td>" . $row['circuito_id'] . "</td>";
        echo "<td>" . $row['circuito_nombre'] . "</td>";
        echo "<td>" . $row['copa_id'] . "</td>";
        echo "<td>" . $row['copa_nombre'] . "</td>";
        echo "</tr>";
        $count++;
    }
    echo "</table>";
}

$enlace->close();
?>