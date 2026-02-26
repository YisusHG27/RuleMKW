<?php
session_start();
require_once 'includes/Logger.php'; // Añadido para logging

// Guardar información del usuario ANTES de destruir la sesión
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Desconocido';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'sin_rol';

// LOG: Registrar cierre de sesión
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
    // Caso raro: alguien llama a logout.php sin sesión activa
    AppLogger::warning("Intento de cerrar sesión sin sesión activa", [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Pequeña pausa para asegurar que el log se guarde (opcional)
usleep(100000); // 0.1 segundos

// Redirigir al login
header("Location: login.php");
exit();
?>