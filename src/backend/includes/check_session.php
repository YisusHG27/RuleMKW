<?php
// includes/check_session.php
require_once 'Logger.php'; // Añadido para logging

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
        
        // LOG: Registrar acceso a página (solo para páginas importantes, para no saturar)
        // Esto es opcional y puedes activarlo solo para ciertas páginas
        if (isset($GLOBALS['log_page_access']) && $GLOBALS['log_page_access'] === true) {
            AppLogger::debug("Acceso a página", [
                'usuario_id' => $_SESSION['usuario_id'],
                'usuario' => $_SESSION['usuario_nombre'],
                'pagina' => $_SERVER['REQUEST_URI'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
        }
    }
    
    return $session_data;
}

/**
 * Función para verificar si el usuario tiene un rol específico
 * Uso: requireRole('admin');
 */
function requireRole($required_role) {
    $session = checkSession();
    
    if (!$session['logged_in']) {
        // LOG: Intento de acceso sin sesión
        AppLogger::warning("Intento de acceso sin sesión", [
            'pagina' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        
        header("Location: /login.php");
        exit;
    }
    
    if ($session['user_role'] !== $required_role) {
        // LOG: Intento de acceso sin permisos
        AppLogger::warning("Intento de acceso sin permisos", [
            'usuario_id' => $session['user_id'],
            'usuario' => $session['user_name'],
            'rol_usuario' => $session['user_role'],
            'rol_requerido' => $required_role,
            'pagina' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        header("Location: /index.php?error=acceso_denegado");
        exit;
    }
    
    return $session;
}
?>