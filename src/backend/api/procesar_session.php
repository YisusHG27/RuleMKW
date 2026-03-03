<?php
// backend/api/procesar_session.php
header('Content-Type: application/json');

// Incluir el archivo check_session.php desde includes
require_once __DIR__ . '/../includes/check_session.php';

// Obtener datos de sesión usando tu función
$session = checkSession();

// Devolver los datos en formato JSON
echo json_encode([
    'logged_in' => $session['logged_in'],
    'user_id' => $session['user_id'],
    'user_name' => $session['user_name'],
    'user_role' => $session['user_role']
]);
?>