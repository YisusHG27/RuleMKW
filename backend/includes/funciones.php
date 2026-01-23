<?php
// Este archivo tiene funciones que usaremos en muchas páginas

// 1. Ver si un usuario está logueado
function usuarioLogueado() {
    // Si existe la variable de sesión 'usuario_id', está logueado
    return isset($_SESSION['usuario_id']);
}

// 2. Ver si es administrador
function esAdministrador() {
    // Está logueado Y su rol es 'admin'
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'admin';
}

// 3. Obtener datos del usuario actual
function obtenerUsuarioActual() {
    if (usuarioLogueado()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'rol' => $_SESSION['usuario_rol']
        ];
    }
    return null; // Si no está logueado
}
?>