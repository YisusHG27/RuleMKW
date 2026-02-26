<?php
session_start();
header('Content-Type: application/json');
include '../includes/conexion.php';
require_once '../includes/Logger.php'; // Añadido para logging

// Verificar si hay usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    // LOG: Intento de guardar estadísticas sin sesión
    AppLogger::warning("Intento de guardar estadísticas sin sesión iniciada", [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Debes iniciar sesión para guardar estadísticas'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Desconocido';

// Verificar que se enviaron resultados
if (!isset($data['resultados']) || empty($data['resultados'])) {
    // LOG: Intento de guardar sin resultados
    AppLogger::warning("Intento de guardar estadísticas sin resultados", [
        'usuario_id' => $usuario_id,
        'usuario' => $usuario_nombre
    ]);
    
    echo json_encode(['success' => false, 'message' => 'No hay resultados para guardar']);
    exit;
}

try {
    $enlace->begin_transaction();
    
    $circuitos_actualizados = 0;
    $circuitos_nuevos = 0;
    
    foreach ($data['resultados'] as $circuito) {
        // Verificar si ya existe estadística para este circuito
        $check = $enlace->prepare("SELECT id, veces_seleccionado FROM estadisticas_usuario WHERE usuario_id = ? AND circuito_id = ?");
        $check->bind_param("ii", $usuario_id, $circuito['id']);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            // Actualizar existente
            $check->bind_result($id, $veces_anteriores);
            $check->fetch();
            
            $update = $enlace->prepare("
                UPDATE estadisticas_usuario 
                SET veces_seleccionado = veces_seleccionado + 1,
                    fecha_ultima_seleccion = NOW()
                WHERE usuario_id = ? AND circuito_id = ?
            ");
            $update->bind_param("ii", $usuario_id, $circuito['id']);
            $update->execute();
            $update->close();
            
            $circuitos_actualizados++;
            
            // LOG debug para cada actualización (opcional, puede ser muy verboso)
            AppLogger::debug("Estadística actualizada", [
                'usuario_id' => $usuario_id,
                'circuito_id' => $circuito['id'],
                'circuito_nombre' => $circuito['nombre'] ?? 'Desconocido',
                'veces_anteriores' => $veces_anteriores,
                'veces_nuevas' => $veces_anteriores + 1
            ]);
            
        } else {
            // Insertar nuevo
            $insert = $enlace->prepare("
                INSERT INTO estadisticas_usuario (usuario_id, circuito_id, veces_seleccionado, fecha_ultima_seleccion)
                VALUES (?, ?, 1, NOW())
            ");
            $insert->bind_param("ii", $usuario_id, $circuito['id']);
            $insert->execute();
            $insert->close();
            
            $circuitos_nuevos++;
            
            // LOG debug para cada inserción
            AppLogger::debug("Nueva estadística creada", [
                'usuario_id' => $usuario_id,
                'circuito_id' => $circuito['id'],
                'circuito_nombre' => $circuito['nombre'] ?? 'Desconocido'
            ]);
        }
        
        $check->close();
    }
    
    $enlace->commit();
    
    // LOG: Resumen de la operación
    AppLogger::info("Estadísticas guardadas exitosamente", [
        'usuario_id' => $usuario_id,
        'usuario' => $usuario_nombre,
        'total_circuitos' => count($data['resultados']),
        'circuitos_nuevos' => $circuitos_nuevos,
        'circuitos_actualizados' => $circuitos_actualizados,
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Estadísticas guardadas exitosamente',
        'stats' => [
            'nuevos' => $circuitos_nuevos,
            'actualizados' => $circuitos_actualizados
        ]
    ]);
    
} catch (Exception $e) {
    $enlace->rollback();
    
    // LOG: Error crítico
    AppLogger::critical("Error al guardar estadísticas", [
        'usuario_id' => $usuario_id,
        'usuario' => $usuario_nombre,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$enlace->close();
?>