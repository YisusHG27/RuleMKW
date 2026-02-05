<?php
session_start();
header('Content-Type: application/json');
include '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $query = "
        SELECT eu.*, c.nombre as circuito_nombre, cop.nombre as copa_nombre
        FROM estadisticas_usuario eu
        JOIN circuitos c ON eu.circuito_id = c.id
        JOIN copas cop ON c.id_copa = cop.id
        WHERE eu.usuario_id = ?
        ORDER BY eu.veces_seleccionado DESC
    ";
    
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $estadisticas = [];
    while ($row = $result->fetch_assoc()) {
        $estadisticas[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'estadisticas' => $estadisticas
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>