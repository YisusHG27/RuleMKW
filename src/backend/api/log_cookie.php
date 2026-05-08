<?php
/* ==========================================================================
   LOG_COOKIE.PHP - REGISTRAR EVENTOS DE CONSENTIMIENTO DE COOKIES
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. CONFIGURAR RESPUESTA JSON =====
header('Content-Type: application/json');

// ===== 1.2. INCLUIR LOGGER =====
require_once __DIR__ . '/../includes/Logger.php';

/* ========== 2. OBTENER DATOS DEL CLIENTE ========== */
// ===== 2.1. DECODIFICAR JSON RECIBIDO =====
$input = json_decode(file_get_contents('php://input'), true);

// ===== 2.2. VALIDAR QUE HAY DATOS =====
if (!$input) {
    // ===== 2.2.1. ERROR: NO HAY DATOS =====
    http_response_code(400);
    echo json_encode(['error' => 'No data received']);
    exit;
}

/* ========== 3. EXTRAER INFORMACIÓN DEL CLIENTE ========== */
// ===== 3.1. OBTENER IP DEL USUARIO =====
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// ===== 3.2. PREPARAR CONTEXTO PARA EL LOG =====
$context = [
    'accion' => $input['accion'] ?? 'desconocida',
    'ip' => $ip_address,
    'user_agent' => $input['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido',
    'screen_resolution' => $input['screen_resolution'] ?? 'desconocida',
    'language' => $input['language'] ?? 'desconocido',
    'timestamp' => $input['timestamp'] ?? date('Y-m-d H:i:s')
];

/* ========== 4. REGISTRAR EVENTO =====*/
// ===== 4.1. LOGUEAR CONSENTIMIENTO DE COOKIES =====
AppLogger::cookie($context['accion'], $context);

/* ========== 5. RETORNAR CONFIRMACIÓN ========== */
// ===== 5.1. RESPUESTA EXITOSA =====
echo json_encode(['success' => true, 'message' => 'Cookie consent logged']);
?>