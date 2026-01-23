<?php
// Devolver circuitos en formato JSON
include '../includes/conexion.php';
header('Content-Type: application/json');

$sql = "SELECT c.id, c.nombre, c.id_copa, cp.nombre as copa_nombre 
        FROM circuitos c 
        JOIN copas cp ON c.id_copa = cp.id 
        ORDER BY cp.id, c.id";

$resultado = $enlace->query($sql);
$circuitos = array();

while($fila = $resultado->fetch_assoc()) {
    $circuitos[] = array(
        'id' => $fila['id'],
        'nombre' => $fila['nombre'],
        'copa' => array(
            'id' => $fila['id_copa'],
            'nombre' => $fila['copa_nombre']
        )
    );
}

echo json_encode(array(
    'success' => true,
    'circuitos' => $circuitos
));
?>