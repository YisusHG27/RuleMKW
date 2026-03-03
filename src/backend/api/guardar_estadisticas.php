<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/conexion.php';
require_once '../includes/Logger.php';
require_once '../includes/check_session.php';

$session = checkSession();
if (!$session['logged_in']) {
    AppLogger::warning("Intento de guardar estadísticas sin sesión");
    echo json_encode([
        'success' => false, 
        'message' => 'Debes iniciar sesión para guardar estadísticas'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$usuario_id = $session['user_id'];
$usuario_nombre = $session['user_name'];

if (!isset($data['resultados']) || empty($data['resultados'])) {
    AppLogger::warning("Intento de guardar estadísticas sin resultados", [
        'usuario_id' => $usuario_id
    ]);
    echo json_encode(['success' => false, 'message' => 'No hay resultados para guardar']);
    exit;
}

// Obtener el ID del ganador (viene explícitamente desde JavaScript)
$ganador_id = $data['ganador_id'] ?? null;

// Si no viene, usamos el primero como fallback (no debería ocurrir)
if (!$ganador_id) {
    $ganador_id = $data['resultados'][0]['id'] ?? null;
}

// Log para depuración
error_log("=== GUARDANDO HISTORIAL ===");
error_log("Usuario ID: " . $usuario_id);
error_log("Ganador ID: " . $ganador_id);
error_log("Circuitos recibidos: " . count($data['resultados']));

try {
    $enlace->begin_transaction();
    
    // Guardar en historial de tiradas (TODOS los circuitos)
    $circuito1_id = $data['resultados'][0]['id'] ?? null;
    $circuito2_id = $data['resultados'][1]['id'] ?? null;
    $circuito3_id = $data['resultados'][2]['id'] ?? null;
    $circuito4_id = $data['resultados'][3]['id'] ?? null;
    
    $insert_historial = $enlace->prepare("
        INSERT INTO historial_tiradas 
        (usuario_id, circuito1_id, circuito2_id, circuito3_id, circuito4_id, ganador_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert_historial->bind_param("iiiiii", 
        $usuario_id, 
        $circuito1_id, 
        $circuito2_id, 
        $circuito3_id, 
        $circuito4_id, 
        $ganador_id
    );
    $insert_historial->execute();
    $insert_historial->close();
    
    // Actualizar estadísticas para TODOS los circuitos (incrementar veces_seleccionado)
    foreach ($data['resultados'] as $circuito) {
        $check = $enlace->prepare("
            SELECT id FROM estadisticas_usuario 
            WHERE usuario_id = ? AND circuito_id = ?
        ");
        $check->bind_param("ii", $usuario_id, $circuito['id']);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            // Actualizar existente - solo incrementar veces_seleccionado
            $update = $enlace->prepare("
                UPDATE estadisticas_usuario 
                SET veces_seleccionado = veces_seleccionado + 1,
                    fecha_ultima_seleccion = NOW()
                WHERE usuario_id = ? AND circuito_id = ?
            ");
            $update->bind_param("ii", $usuario_id, $circuito['id']);
            $update->execute();
            $update->close();
        } else {
            // Insertar nuevo (veces_seleccionado = 1, veces_ganador = 0 por defecto)
            $insert = $enlace->prepare("
                INSERT INTO estadisticas_usuario 
                (usuario_id, circuito_id, veces_seleccionado, fecha_ultima_seleccion)
                VALUES (?, ?, 1, NOW())
            ");
            $insert->bind_param("ii", $usuario_id, $circuito['id']);
            $insert->execute();
            $insert->close();
        }
        
        $check->close();
    }
    
    // Actualizar SOLO el ganador (incrementar veces_ganador)
    $check_ganador = $enlace->prepare("
        SELECT id FROM estadisticas_usuario 
        WHERE usuario_id = ? AND circuito_id = ?
    ");
    $check_ganador->bind_param("ii", $usuario_id, $ganador_id);
    $check_ganador->execute();
    $check_ganador->store_result();
    
    if ($check_ganador->num_rows > 0) {
        // Actualizar ganador
        $update_ganador = $enlace->prepare("
            UPDATE estadisticas_usuario 
            SET veces_ganador = veces_ganador + 1
            WHERE usuario_id = ? AND circuito_id = ?
        ");
        $update_ganador->bind_param("ii", $usuario_id, $ganador_id);
        $update_ganador->execute();
        $update_ganador->close();
    }
    
    $check_ganador->close();
    
    $enlace->commit();
    
    AppLogger::info("Estadísticas guardadas exitosamente", [
        'usuario_id' => $usuario_id,
        'usuario' => $usuario_nombre,
        'ganador_id' => $ganador_id,
        'total_circuitos' => count($data['resultados']),
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Estadísticas guardadas exitosamente'
    ]);
    
} catch (Exception $e) {
    $enlace->rollback();
    
    AppLogger::critical("Error al guardar estadísticas", [
        'usuario_id' => $usuario_id,
        'error' => $e->getMessage()
    ]);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$enlace->close();
?>