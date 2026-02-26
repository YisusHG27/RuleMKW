<?php
// src/backend/api/get_logs.php
session_start();
header('Content-Type: application/json');

require_once '../includes/check_session.php';
require_once '../includes/conexion.php';

// Solo admin puede ver logs
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$action = $_GET['action'] ?? 'get_logs';

switch ($action) {
    case 'get_logs':
        getLogs($enlace);
        break;
    case 'get_stats':
        getStats($enlace);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function getLogs($enlace) {
    $tipo = $_GET['tipo'] ?? '';
    $usuario_id = $_GET['usuario_id'] ?? '';
    $busqueda = $_GET['busqueda'] ?? '';
    $fecha_desde = $_GET['fecha_desde'] ?? '';
    $fecha_hasta = $_GET['fecha_hasta'] ?? '';
    $limite = intval($_GET['limite'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $where = [];
    $params = [];
    $types = "";
    
    if (!empty($tipo)) {
        $where[] = "l.tipo = ?";
        $params[] = $tipo;
        $types .= "s";
    }
    
    if (!empty($usuario_id)) {
        $where[] = "l.usuario_id = ?";
        $params[] = $usuario_id;
        $types .= "i";
    }
    
    if (!empty($busqueda)) {
        $where[] = "(l.descripcion LIKE ? OR l.accion LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= "ss";
    }
    
    if (!empty($fecha_desde)) {
        $where[] = "DATE(l.fecha) >= ?";
        $params[] = $fecha_desde;
        $types .= "s";
    }
    
    if (!empty($fecha_hasta)) {
        $where[] = "DATE(l.fecha) <= ?";
        $params[] = $fecha_hasta;
        $types .= "s";
    }
    
    $where_clause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
    
    // Consulta principal
    $sql = "
        SELECT 
            l.*,
            u.usuario as usuario_nombre
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        $where_clause
        ORDER BY l.fecha DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limite;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $enlace->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    // Contar total
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        $where_clause
    ";
    
    // Eliminar los últimos 2 parámetros (limite y offset) para el count
    $count_params = array_slice($params, 0, -2);
    $count_types = substr($types, 0, -2);
    
    $stmt_count = $enlace->prepare($count_sql);
    if (!empty($count_params)) {
        $stmt_count->bind_param($count_types, ...$count_params);
    }
    $stmt_count->execute();
    $count_result = $stmt_count->get_result();
    $total = $count_result->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $logs,
        'total' => $total,
        'offset' => $offset,
        'limite' => $limite
    ]);
}

function getStats($enlace) {
    // Estadísticas de logs
    $stats = [
        'total' => 0,
        'por_tipo' => []
    ];
    
    // Total
    $result = $enlace->query("SELECT COUNT(*) as total FROM logs_sistema");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Por tipo
    $result = $enlace->query("
        SELECT tipo, COUNT(*) as cantidad 
        FROM logs_sistema 
        GROUP BY tipo
    ");
    
    while ($row = $result->fetch_assoc()) {
        $stats['por_tipo'][$row['tipo']] = $row['cantidad'];
    }
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}