<?php
// Archivo para verificar sesión
function checkSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $session_data = [
        'logged_in' => false,
        'user_id' => null,
        'user_name' => '',
        'user_role' => ''
    ];
    
    if (isset($_SESSION['usuario_id'])) {
        $session_data = [
            'logged_in' => true,
            'user_id' => $_SESSION['usuario_id'],
            'user_name' => $_SESSION['usuario_nombre'],
            'user_role' => $_SESSION['usuario_rol']
        ];
    }
    
    return $session_data;
}
?>