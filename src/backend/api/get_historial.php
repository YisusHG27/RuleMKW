<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/conexion.php';
require_once '../includes/check_session.php';

$session = checkSession();
if (!$session['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $session['user_id'];

try {
    $query = "
        SELECT 
            h.id,
            DATE_FORMAT(h.fecha, '%d/%m/%Y %H:%i') as fecha,
            c1.nombre as circuito1,
            c2.nombre as circuito2,
            c3.nombre as circuito3,
            c4.nombre as circuito4,
            cg.nombre as ganador_nombre
        FROM historial_tiradas h
        LEFT JOIN circuitos c1 ON h.circuito1_id = c1.id
        LEFT JOIN circuitos c2 ON h.circuito2_id = c2.id
        LEFT JOIN circuitos c3 ON h.circuito3_id = c3.id
        LEFT JOIN circuitos c4 ON h.circuito4_id = c4.id
        LEFT JOIN circuitos cg ON h.ganador_id = cg.id
        WHERE h.usuario_id = ?
        ORDER BY h.fecha DESC
        LIMIT 10
    ";
    
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $historial = [];
    while ($row = $result->fetch_assoc()) {
        // Crear array con todos los circuitos (filtrando nulls)
        $circuitos = [];
        if ($row['circuito1']) $circuitos[] = formatearNombreCircuito($row['circuito1']);
        if ($row['circuito2']) $circuitos[] = formatearNombreCircuito($row['circuito2']);
        if ($row['circuito3']) $circuitos[] = formatearNombreCircuito($row['circuito3']);
        if ($row['circuito4']) $circuitos[] = formatearNombreCircuito($row['circuito4']);
        
        $historial[] = [
            'id' => $row['id'],
            'fecha' => $row['fecha'],
            'circuitos' => $circuitos,
            'circuitos_texto' => implode(' · ', $circuitos),
            'ganador' => formatearNombreCircuito($row['ganador_nombre'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'historial' => $historial
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Función auxiliar para formatear nombres de circuitos
function formatearNombreCircuito($nombre) {
    if (!$nombre) return '';
    
    $cambios = [
        'CanionFerroviario' => 'Cañón Ferroviario',
        'Circuito Mario Bros.' => 'Circuito Mario Bros.',
        'Ciudad Corona (1)' => 'Ciudad Corona',
        'Ciudad Corona (2)' => 'Ciudad Corona',
        'Estadio Peach (1)' => 'Estadio Peach',
        'Estadio Peach (2)' => 'Estadio Peach',
        'Templo del Bloque ?' => 'Templo del Bloque ?',
        'Senda Arco Iris' => 'Senda Arco Iris',
        'Puerto Espacial DK' => 'Puerto Espacial DK',
        'Desierto Sol-Sol' => 'Desierto Sol-Sol',
        'Bazar Shy Guy' => 'Bazar Shy Guy',
        'Estadio Wario' => 'Estadio Wario',
        'Fortaleza Aérea' => 'Fortaleza Aérea',
        'DK Alpino' => 'DK Alpino',
        'Mirador Estelar' => 'Mirador Estelar',
        'Cielos Helados' => 'Cielos Helados',
        'Galeón de Wario' => 'Galeón de Wario',
        'Playa Koopa' => 'Playa Koopa',
        'Sabana Salpicante' => 'Sabana Salpicante',
        'Playa Peach' => 'Playa Peach',
        'Ciudad Salina' => 'Ciudad Salina',
        'Jungla Dino Dino' => 'Jungla Dino Dino',
        'Cascadas Cheep Cheep' => 'Cascadas Cheep Cheep',
        'Gruta Diente de León' => 'Gruta Diente de León',
        'Cine Boo' => 'Cine Boo',
        'Caverna Ósea' => 'Caverna Ósea',
        'Pradera Mu-Mu' => 'Pradera Mu-Mu',
        'Monte Chocolate' => 'Monte Chocolate',
        'Fábrica de Toad' => 'Fábrica de Toad',
        'Castillo de Bowser' => 'Castillo de Bowser',
        'Aldea Arbórea' => 'Aldea Arbórea',
        'Circuito Mario' => 'Circuito Mario',
        'Senda Arco Iris' => 'Senda Arco Iris'
    ];
    
    return $cambios[$nombre] ?? $nombre;
}

$enlace->close();
?>