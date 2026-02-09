<?php
session_start();
header('Content-Type: application/json');
include '../includes/conexion.php';

// Verificar si hay usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Debes iniciar sesión para guardar estadísticas'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];

// Verificar que se enviaron resultados
if (!isset($data['resultados']) || empty($data['resultados'])) {
    echo json_encode(['success' => false, 'message' => 'No hay resultados para guardar']);
    exit;
}

try {
    $enlace->begin_transaction();
    
    foreach ($data['resultados'] as $circuito) {
        // Verificar si ya existe estadística para este circuito
        $check = $enlace->prepare("SELECT id FROM estadisticas_usuario WHERE usuario_id = ? AND circuito_id = ?");
        $check->bind_param("ii", $usuario_id, $circuito['id']);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            // Actualizar existente
            $update = $enlace->prepare("
                UPDATE estadisticas_usuario 
                SET veces_seleccionado = veces_seleccionado + 1,
                    fecha_ultima_seleccion = NOW()
                WHERE usuario_id = ? AND circuito_id = ?
            ");
            $update->bind_param("ii", $usuario_id, $circuito['id']);
            $update->execute();
        } else {
            // Insertar nuevo
            $insert = $enlace->prepare("
                INSERT INTO estadisticas_usuario (usuario_id, circuito_id, veces_seleccionado, fecha_ultima_seleccion)
                VALUES (?, ?, 1, NOW())
            ");
            $insert->bind_param("ii", $usuario_id, $circuito['id']);
            $insert->execute();
        }
        
        $check->close();
    }
    
    $enlace->commit();
    echo json_encode(['success' => true, 'message' => 'Estadísticas guardadas exitosamente']);
    
} catch (Exception $e) {
    $enlace->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$enlace->close();
?>