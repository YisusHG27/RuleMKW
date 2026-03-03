<?php
// backend/api/get_usuario_actual.php
header('Content-Type: application/json');

require_once '../includes/conexion.php';
require_once '../includes/check_session.php';

$session = checkSession();

if (!$session['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

$usuario_id = $session['user_id'];

try {
    // Obtener datos completos del usuario desde la BD
    $query = "SELECT id, usuario as nombre, email, rol, fecha_registro FROM usuarios WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Obtener última actividad (máxima fecha de historial)
        $query_actividad = "SELECT MAX(fecha) as ultima_actividad FROM historial_tiradas WHERE usuario_id = ?";
        $stmt_act = $enlace->prepare($query_actividad);
        $stmt_act->bind_param("i", $usuario_id);
        $stmt_act->execute();
        $result_act = $stmt_act->get_result();
        $ultima_actividad = $result_act->fetch_assoc();
        
        // Formatear fechas
        $fecha_registro = date('Y-m-d H:i:s', strtotime($row['fecha_registro']));
        $ultima_act = $ultima_actividad['ultima_actividad'] ? date('Y-m-d H:i:s', strtotime($ultima_actividad['ultima_actividad'])) : $fecha_registro;
        
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id' => (int)$row['id'],
                'nombre' => $row['nombre'],
                'email' => $row['email'],
                'rol' => $row['rol'],
                'fecha_registro' => $fecha_registro,
                'ultima_actividad' => $ultima_act
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Usuario no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

$enlace->close();
?>