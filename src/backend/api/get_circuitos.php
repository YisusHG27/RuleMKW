<?php
include '../includes/conexion.php';

try {
    // Obtener todas las copas con sus circuitos
    $query = "SELECT c.id as circuito_id, c.nombre as circuito_nombre, 
                     cop.id as copa_id, cop.nombre as copa_nombre
              FROM circuitos c
              JOIN copas cop ON c.id_copa = cop.id
              ORDER BY cop.id, c.id";
    
    $result = $enlace->query($query);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . $enlace->error);
    }
    
    $circuitosPorCopa = [];
    
    while ($row = $result->fetch_assoc()) {
        $copaId = $row['copa_id'];
        
        if (!isset($circuitosPorCopa[$copaId])) {
            $circuitosPorCopa[$copaId] = [
                'id' => $copaId,
                'nombre' => $row['copa_nombre'],
                'circuitos' => []
            ];
        }
        
        $circuitosPorCopa[$copaId]['circuitos'][] = [
            'id' => $row['circuito_id'],
            'nombre' => $row['circuito_nombre'],
            'copa_nombre' => $row['copa_nombre']
        ];
    }
    
    // Si no hay datos, retornar array vacío
    if (empty($circuitosPorCopa)) {
        echo json_encode([]);
        exit();
    }
    
    // Convertir a array indexado
    $copas = array_values($circuitosPorCopa);
    
    echo json_encode($copas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al cargar circuitos',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$enlace->close();
?>