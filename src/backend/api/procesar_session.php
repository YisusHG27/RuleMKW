<?php
/* ==========================================================================
   PROCESAR_SESSION.PHP - OBTENER ESTADO DE SESIÓN ACTUAL
   ========================================================================== */

/* ========== 1. CONFIGURACIÓN ========== */
// ===== 1.1. ESTABLECER TIPO DE RESPUESTA JSON =====
header('Content-Type: application/json');

/* ========== 2. INCLUIR DEPENDENCIAS ========== */
// ===== 2.1. INCLUIR FUNCIONES DE SESIÓN =====
require_once __DIR__ . '/../includes/check_session.php';

/* ========== 3. OBTENER DATOS DE SESIÓN ========== */
// ===== 3.1. VERIFICAR Y OBTENER SESIÓN =====
$session = checkSession();

/* ========== 4. RETORNAR INFORMACIÓN DE SESIÓN ========== */
// ===== 4.1. DEVOLVER DATOS EN FORMATO JSON =====
echo json_encode([
    'logged_in' => $session['logged_in'],
    'user_id' => $session['user_id'],
    'user_name' => $session['user_name'],
    'user_role' => $session['user_role']
]);
?>