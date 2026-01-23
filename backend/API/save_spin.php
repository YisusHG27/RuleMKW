<?php
session_start();
include '../includes/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(array('success' => false, 'message' => 'No hay sesión'));
    exit();
}

// Recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true);
$circuito_id = $input['circuito_id'];
$usuario_id = $_SESSION['usuario_id'];

// Insertar o actualizar estadística
$sql = "INSERT INTO estadisticas_usuario (usuario_id, circuito_id, veces_seleccionado) 
        VALUES (?, ?, 1) 
        ON DUPLICATE KEY UPDATE 
        veces_seleccionado = veces_seleccionado + 1";

$stmt = $enlace->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $circuito_id);

if ($stmt->execute()) {
    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false));
}
?>