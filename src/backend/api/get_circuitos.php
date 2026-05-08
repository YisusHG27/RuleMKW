<?php
/* ==========================================================================
   GET_CIRCUITOS.PHP - OBTENER LISTADO DE COPAS Y CIRCUITOS
   ========================================================================== */

/* ========== 1. INCLUIR DEPENDENCIAS ========== */
// ===== 1.1. INCLUIR CONEXIÓN A LA BD =====
include '../includes/conexion.php';

/* ========== 2. OBTENER Y PROCESAR DATOS ========== */
try {
    // ===== 2.1. CONSULTA PARA OBTENER COPAS Y CIRCUITOS =====
    // Obtener todas las copas con sus circuitos ordenados
    $query = "SELECT c.id as circuito_id, c.nombre as circuito_nombre, 
                     cop.id as copa_id, cop.nombre as copa_nombre
              FROM circuitos c
              JOIN copas cop ON c.id_copa = cop.id
              ORDER BY cop.id, c.id";
    
    // ===== 2.2. EJECUTAR CONSULTA =====
    $result = $enlace->query($query);
    
    // ===== 2.3. VALIDAR RESULTADO =====
    if (!$result) {
        throw new Exception("Error en la consulta: " . $enlace->error);
    }
    
    // ===== 2.4. AGRUPAR CIRCUITOS POR COPA =====
    $circuitosPorCopa = [];
    
    while ($row = $result->fetch_assoc()) {
        $copaId = $row['copa_id'];
        
        // Crear entrada de copa si no existe
        if (!isset($circuitosPorCopa[$copaId])) {
            $circuitosPorCopa[$copaId] = [
                'id' => $copaId,
                'nombre' => $row['copa_nombre'],
                'circuitos' => []
            ];
        }
        
        // Añadir circuito a la copa
        $circuitosPorCopa[$copaId]['circuitos'][] = [
            'id' => $row['circuito_id'],
            'nombre' => $row['circuito_nombre'],
            'copa_nombre' => $row['copa_nombre']
        ];
    }
    
    // ===== 2.5. VALIDAR SI HAY DATOS =====
    if (empty($circuitosPorCopa)) {
        echo json_encode([]);
        exit();
    }
    
    // ===== 2.6. CONVERTIR A ARRAY INDEXADO Y RETORNAR =====
    $copas = array_values($circuitosPorCopa);
    echo json_encode($copas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // ===== 2.7. MANEJO DE ERRORES =====
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al cargar circuitos',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// ===== 2.8. CERRAR CONEXIÓN =====
$enlace->close();
?>