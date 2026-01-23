<?php
session_start();
include '../includes/onexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(array('success' => false));
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener estadísticas
$stats = array();

// Total de tiradas
$sql = "SELECT SUM(veces_seleccionado) as total FROM estadisticas_usuario WHERE usuario_id = ?";
$stmt = $enlace->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['total_tiradas'] = $row['total'] ?: 0;

// Circuito más jugado
$sql = "SELECT c.nombre, eu.veces_seleccionado 
        FROM estadisticas_usuario eu
        JOIN circuitos c ON eu.circuito_id = c.id
        WHERE eu.usuario_id = ? 
        ORDER BY eu.veces_seleccionado DESC 
        LIMIT 1";
$stmt = $enlace->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stats['circuito_mas_jugado'] = $row['nombre'];
    $stats['veces_jugado'] = $row['veces_seleccionado'];
}

echo json_encode(array(
    'success' => true,
    'stats' => $stats
));
?>