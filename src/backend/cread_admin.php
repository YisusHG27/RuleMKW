<?php
/* ==========================================================================
   CREAD_ADMIN.PHP - CREAR USUARIO ADMINISTRADOR INICIAL
   ========================================================================== */

/* ========== 1. INCLUIR DEPENDENCIAS ========== */
// ===== 1.1. INCLUIR CONEXIÓN A LA BD =====
include 'includes/conexion.php';

/* ========== 2. CONFIGURAR DATOS DEL ADMINISTRADOR ========== */
// ===== 2.1. DEFINIR CREDENCIALES =====
// IMPORTANTE: Solo ejecutar una vez manualmente
$admin_usuario = "admin";
$admin_email = "admin@rulemkw.com";
// ===== 2.2. HASHEAR LA CONTRASEÑA =====
$admin_pass = password_hash("Admin123", PASSWORD_DEFAULT);

/* ========== 3. INSERTAR ADMINISTRADOR EN BD ========== */
// ===== 3.1. PREPARAR CONSULTA =====
$stmt = $enlace->prepare("INSERT INTO usuarios (usuario, email, pass, rol) VALUES (?, ?, ?, 'admin')");
// ===== 3.2. VINCULAR PARÁMETROS =====
$stmt->bind_param("sss", $admin_usuario, $admin_email, $admin_pass);

/* ========== 4. EJECUTAR E INFORMAR RESULTADO ========== */
// ===== 4.1. VERIFICAR ÉXITO DE LA INSERCIÓN =====
if($stmt->execute()) {
    // ===== 4.2. MENSAJE DE ÉXITO =====
    echo "Administrador creado exitosamente";
} else {
    // ===== 4.3. MOSTRAR ERROR SI FALLa =====
    echo "Error: " . $enlace->error;
}
?>