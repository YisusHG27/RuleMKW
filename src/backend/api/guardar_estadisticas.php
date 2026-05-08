<?php
/* ==========================================================================
   GUARDAR_ESTADISTICAS.PHP - GUARDAR RESULTADOS DE TIRADAS Y ACTUALIZAR
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. INICIAR SESIÓN Y CONFIGURAR RESPUESTA =====
session_start();
header('Content-Type: application/json');

// ===== 1.2. INCLUIR DEPENDENCIAS =====
require_once '../includes/conexion.php';
require_once '../includes/Logger.php';
require_once '../includes/check_session.php';

/* ========== 2. VALIDAR AUTORIZACIÓN ========== */
// ===== 2.1. VERIFICAR SESIÓN DEL USUARIO =====
$session = checkSession();
if (!$session['logged_in']) {
    AppLogger::warning("Intento de guardar estadísticas sin sesión");
    echo json_encode([
        'success' => false, 
        'message' => 'Debes iniciar sesión para guardar estadísticas'
    ]);
    exit;
}

// ===== 2.2. OBTENER DATOS DE SESIÓN =====
$data = json_decode(file_get_contents('php://input'), true);
$usuario_id = $session['user_id'];
$usuario_nombre = $session['user_name'];

/* ========== 3. VALIDAR DATOS RECIBIDOS ========== */
// ===== 3.1. VERIFICAR QUE HAY RESULTADOS =====
if (!isset($data['resultados']) || empty($data['resultados'])) {
    AppLogger::warning("Intento de guardar estadísticas sin resultados", [
        'usuario_id' => $usuario_id
    ]);
    echo json_encode(['success' => false, 'message' => 'No hay resultados para guardar']);
    exit;
}

// ===== 3.2. OBTENER ID DEL GANADOR =====
$ganador_id = $data['ganador_id'] ?? null;

// ===== 3.3. FALLBACK: USAR PRIMER RESULTADO SI NO VIENE GANADOR =====
if (!$ganador_id) {
    $ganador_id = $data['resultados'][0]['id'] ?? null;
}

/* ========== 4. PROCESAR TIRADA EN TRANSACCIÓN ========== */
try {
    // ===== 4.1. INICIAR TRANSACCIÓN =====
    $enlace->begin_transaction();
    
    /* ========== 5. GUARDAR HISTORIAL DE TIRADAS ========== */
    // ===== 5.1. EXTRAER IDS DE LOS 4 CIRCUITOS =====
    $circuito1_id = $data['resultados'][0]['id'] ?? null;
    $circuito2_id = $data['resultados'][1]['id'] ?? null;
    $circuito3_id = $data['resultados'][2]['id'] ?? null;
    $circuito4_id = $data['resultados'][3]['id'] ?? null;
    
    // ===== 5.2. INSERTAR REGISTRO EN HISTORIAL =====
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
    
    /* ========== 6. ACTUALIZAR ESTADISTICAS DE TODOS LOS CIRCUITOS ========== */
    // ===== 6.1. PROCESAR CADA CIRCUITO DE LA TIRADA =====
    foreach ($data['resultados'] as $circuito) {
        // ===== 6.1.1. VERIFICAR SI EXISTE REGISTRO =====
        $check = $enlace->prepare("
            SELECT id FROM estadisticas_usuario 
            WHERE usuario_id = ? AND circuito_id = ?
        ");
        $check->bind_param("ii", $usuario_id, $circuito['id']);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            // ===== 6.1.2. ACTUALIZAR EXISTENTE =====
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
            // ===== 6.1.3. INSERTAR NUEVO REGISTRO =====
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
    
    /* ========== 7. ACTUALIZAR ESTADISTICAS DEL GANADOR ========== */
    // ===== 7.1. VERIFICAR SI GANADOR EXISTE EN ESTADISTICAS =====
    $check_ganador = $enlace->prepare("
        SELECT id FROM estadisticas_usuario 
        WHERE usuario_id = ? AND circuito_id = ?
    ");
    $check_ganador->bind_param("ii", $usuario_id, $ganador_id);
    $check_ganador->execute();
    $check_ganador->store_result();
    
    if ($check_ganador->num_rows > 0) {
        // ===== 7.2. INCREMENTAR CONTADOR DE GANANCIAS =====
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
    
    // ===== 7.3. CONFIRMAR TRANSACCIÓN =====
    $enlace->commit();
    
    /* ========== 8. REGISTRAR EVENTO EXITOSO ========== */
    // ===== 8.1. LOGUEAR GUARDADO EXITOSO =====
    AppLogger::info("Estadísticas guardadas exitosamente", [
        'usuario_id' => $usuario_id,
        'usuario' => $usuario_nombre,
        'ganador_id' => $ganador_id,
        'total_circuitos' => count($data['resultados']),
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    // ===== 8.2. RETORNAR EXITO =====
    echo json_encode([
        'success' => true, 
        'message' => 'Estadísticas guardadas exitosamente'
    ]);
    
} catch (Exception $e) {
    // ===== 8.3. REVERTIR TRANSACCIÓN EN CASO DE ERROR =====
    $enlace->rollback();
    
    /* ========== 9. REGISTRAR ERROR ========== */
    // ===== 9.1. LOGUEAR ERROR CRÍTICO =====
    AppLogger::critical("Error al guardar estadísticas", [
        'usuario_id' => $usuario_id,
        'error' => $e->getMessage()
    ]);
    
    // ===== 9.2. RETORNAR ERROR =====
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

/* ========== 10. CERRAR CONEXIÓN ========== */
// ===== 10.1. LIBERAR RECURSO DE BD =====
$enlace->close();
?>