<?php
/* ==========================================================================
   CHECK_SESSION.PHP - GESTIÓN Y VALIDACIÓN DE SESIONES
   ========================================================================== */

/* ========== 1. INCLUIR DEPENDENCIAS ========== */
// ===== 1.1. INCLUIR LOGGER =====
require_once 'Logger.php';

/* ========== 2. FUNCIÓN DE VERIFICACIÓN DE SESIÓN ========== */
/**
 * ===== 2.1. checkSession() =====
 * Verifica si el usuario tiene una sesión activa
 * @return array Array con estado de sesión y datos del usuario
 */
function checkSession() {
    // ===== 2.1.1. INICIAR SESIÓN SI NO ESTÁ INICIADA =====
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // ===== 2.1.2. ESTRUCTURA DE DATOS DE SESIÓN =====
    $session_data = [
        'logged_in' => false,
        'user_id' => null,
        'user_name' => '',
        'user_role' => ''
    ];
    
    // ===== 2.1.3. VERIFICAR SI HAY VARIABLES DE SESIÓN =====
    if (isset($_SESSION['usuario_id'])) {
        $session_data = [
            'logged_in' => true,
            'user_id' => $_SESSION['usuario_id'],
            'user_name' => $_SESSION['usuario_nombre'],
            'user_role' => $_SESSION['usuario_rol']
        ];
        
        // ===== 2.1.4. LOGUEAR ACCESO A PÁGINA (OPCIONAL) =====
        // Esto es opcional y puede activarse solo para ciertas páginas
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

/* ========== 3. FUNCIÓN DE VERIFICACIÓN DE ROL ========== */
/**
 * ===== 3.1. requireRole() =====
 * Verifica si el usuario tiene un rol específico
 * Redirige a login si no está autenticado
 * @param string $required_role Rol requerido
 * @return array Datos de sesión si autorizado
 * Uso: requireRole('admin');
 */
function requireRole($required_role) {
    // ===== 3.1.1. OBTENER DATOS DE SESIÓN =====
    $session = checkSession();
    
    // ===== 3.1.2. VERIFICAR SI EL USUARIO ESTÁ LOGUEADO =====
    if (!$session['logged_in']) {
        // Registrar intento de acceso sin sesión
        AppLogger::warning("Intento de acceso sin sesión", [
            'pagina' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        
        header("Location: /login.php");
        exit;
    }
    
    // ===== 3.1.3. VERIFICAR SI EL ROL COINCIDE =====
    if ($session['user_role'] !== $required_role) {
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