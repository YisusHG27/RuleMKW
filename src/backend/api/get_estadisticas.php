<?php
/* ==========================================================================
   GET_ESTADISTICAS.PHP - OBTENER ESTADÍSTICAS DEL USUARIO
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. INICIAR SESIÓN Y CONFIGURAR RESPUESTA =====
session_start();
header('Content-Type: application/json');

// ===== 1.2. INCLUIR DEPENDENCIAS =====
require_once '../includes/conexion.php';
require_once '../includes/check_session.php';

/* ========== 2. VALIDAR AUTORIZACIÓN ========== */
// ===== 2.1. VERIFICAR SESIÓN DEL USUARIO =====
$session = checkSession();
if (!$session['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// ===== 2.2. OBTENER ID DEL USUARIO =====
$usuario_id = $session['user_id'];

/* ========== 3. OBTENER ESTADÍSTICAS ========== */
try {
    // ===== 3.1. CONSULTAR CIRCUITOS GANADORES =====
    $query = "
        SELECT 
            eu.*,
            c.nombre as circuito_nombre,
            cop.nombre as copa_nombre
        FROM estadisticas_usuario eu
        JOIN circuitos c ON eu.circuito_id = c.id
        JOIN copas cop ON c.id_copa = cop.id
        WHERE eu.usuario_id = ? 
        AND eu.veces_ganador > 0
        ORDER BY eu.veces_ganador DESC, eu.veces_seleccionado DESC
    ";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $estadisticas = [];
    while ($row = $result->fetch_assoc()) {
        $row['circuito_nombre_formateado'] = formatearNombreCircuito($row['circuito_nombre']);
        $estadisticas[] = $row;
    }
    
    // ===== 3.2. CALCULAR TOTALES =====
    $query_veces_girado = "SELECT COUNT(*) as total FROM historial_tiradas WHERE usuario_id = ?";
    $stmt_girado = $enlace->prepare($query_veces_girado);
    $stmt_girado->bind_param("i", $usuario_id);
    $stmt_girado->execute();
    $result_girado = $stmt_girado->get_result();
    $veces_girado = $result_girado->fetch_assoc()['total'];
    
    $query_veces_ganador = "SELECT SUM(veces_ganador) as total FROM estadisticas_usuario WHERE usuario_id = ?";
    $stmt_ganador = $enlace->prepare($query_veces_ganador);
    $stmt_ganador->bind_param("i", $usuario_id);
    $stmt_ganador->execute();
    $result_ganador = $stmt_ganador->get_result();
    $veces_ganador = $result_ganador->fetch_assoc()['total'] ?? 0;
    
    $totales = [
        'veces_girado' => (int)$veces_girado,
        'veces_ganador' => (int)$veces_ganador,
        'circuitos_unicos' => count($estadisticas)
    ];
    
    echo json_encode([
        'success' => true,
        'estadisticas' => $estadisticas,
        'totales' => $totales
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

function formatearNombreCircuito($nombre) {
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