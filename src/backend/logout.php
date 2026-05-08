<?php
/* ==========================================================================
   LOGOUT.PHP - CIERRE DE SESIÓN DE USUARIO
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. INICIAR SESIÓN PARA ACCEDER A VARIABLES =====
session_start();

// ===== 1.2. INCLUIR DEPENDENCIAS =====
require_once 'includes/Logger.php';

/* ========== 2. GUARDAR DATOS DE USUARIO ANTES DE DESTRUIR SESIÓN ========== */
// ===== 2.1. OBTENER INFORMACIÓN DEL USUARIO ACTUAL =====
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Desconocido';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'sin_rol';

/* ========== 3. REGISTRAR CIERRE DE SESIÓN EN LOG ========== */
// ===== 3.1. SI HAY USUARIO LOGUEADO =====
if ($usuario_id) {
    AppLogger::info("Usuario cerró sesión", [
        'usuario_id' => $usuario_id,
        'usuario' => $usuario_nombre,
        'rol' => $usuario_rol,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'metodo' => 'logout_manual'
    ]);
} else {
    // ===== 3.2. CASO RARO: INTENTO DE LOGOUT SIN SESIÓN =====
    AppLogger::warning("Intento de cerrar sesión sin sesión activa", [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
}

/* ========== 4. DESTRUIR LA SESIÓN ========== */
// ===== 4.1. VACIAR TODAS LAS VARIABLES DE SESIÓN =====
$_SESSION = array();

// ===== 4.2. DESTRUIR COOKIE DE SESIÓN =====
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ===== 4.3. DESTRUIR LA SESIÓN COMPLETAMENTE =====
session_destroy();

/* ========== 5. PAUSAR Y REDIRIGIR ========== */
// Pequeña pausa para asegurar que el log se guarde
usleep(100000); // 0.1 segundos

// ===== 5.1. REDIRIGIR AL LOGIN =====
header("Location: login.php");
exit();
?>