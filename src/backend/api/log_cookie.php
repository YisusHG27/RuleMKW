<?php
// backend/api/log_cookie.php
header('Content-Type: application/json');

// Incluir el logger
require_once __DIR__ . '/../includes/Logger.php';

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'No data received']);
    exit;
}

// Obtener IP del usuario
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Preparar contexto para el log
$context = [
    'accion' => $input['accion'] ?? 'desconocida',
    'ip' => $ip_address,
    'user_agent' => $input['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido',
    'screen_resolution' => $input['screen_resolution'] ?? 'desconocida',
    'language' => $input['language'] ?? 'desconocido',
    'timestamp' => $input['timestamp'] ?? date('Y-m-d H:i:s')
];

// Registrar el log
AppLogger::cookie($context['accion'], $context);

// Respuesta exitosa
echo json_encode(['success' => true, 'message' => 'Cookie consent logged']);