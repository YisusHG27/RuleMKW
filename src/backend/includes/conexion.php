<?php
/* ==========================================================================
   CONEXION.PHP - CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
   ========================================================================== */

/* ========== 1. CARGAR DEPENDENCIAS ========== */
// ===== 1.1. CARGAR AUTOLOAD DE COMPOSER =====
// Verificar si existe el archivo autoload de Composer
$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    require_once 'Logger.php';
} else {
    // Fallback a Logger simple si no existe Composer
    require_once 'LoggerSimple.php';
}

/* ========== 2. CONFIGURACIÓN DE BD ========== */
// ===== 2.1. CREDENCIALES DE CONEXIÓN =====
$host = 'mysql';
$usuario = 'rulemkw_user';
$password = 'password_segura';
$base_datos = 'rulemkw';

/* ========== 3. CONECTAR A LA BASE DE DATOS ========== */
// ===== 3.1. CREAR INSTANCIA DE MYSQLI =====
$enlace = new mysqli($host, $usuario, $password, $base_datos);

/* ========== 4. VALIDAR CONEXIÓN ========== */
// ===== 4.1. VERIFICAR ERROR DE CONEXIÓN =====
if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}

/* ========== 5. CONFIGURAR CHARSET ========== */
// ===== 5.1. ESTABLECER CHARSET UTF-8 =====
$enlace->set_charset("utf8mb4");
?>