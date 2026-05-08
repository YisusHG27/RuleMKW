<?php
/* ==========================================================================
   SUBIR_FOTO_PERFIL.PHP - GESTIONAR CARGA DE FOTO DE PERFIL DEL USUARIO
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. INICIAR SESIÓN Y CONFIGURAR RESPUESTA =====
session_start();
header('Content-Type: application/json');

// ===== 1.2. INCLUIR DEPENDENCIAS =====
require_once '../includes/conexion.php';
require_once '../includes/check_session.php';
require_once '../includes/Logger.php';

/* ========== 2. VALIDAR AUTORIZACIÓN ========== */
// ===== 2.1. VERIFICAR SESIÓN DEL USUARIO =====
$session = checkSession();
if (!$session['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// ===== 2.2. OBTENER ID DEL USUARIO =====
$usuario_id = $session['user_id'];
$upload_dir = '../../media/perfil/';

/* ========== 3. PREPARAR DIRECTORIO ========== */
// ===== 3.1. CREAR DIRECTORIO DE SUBIDA SI NO EXISTE =====
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* ========== 4. VALIDAR ARCHIVO SUBIDO ========== */
// ===== 4.1. VERIFICAR SI SE ENVIÓ UN ARCHIVO =====
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    exit;
}

// ===== 4.2. EXTRAER DATOS DEL ARCHIVO =====
$archivo = $_FILES['foto'];
$nombre_original = $archivo['name'];
$extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
$tamano = $archivo['size'];

/* ========== 5. VALIDAR EXTENSIÓN Y TAM AÑO ========== */
// ===== 5.1. DEFINIR EXTENSIONES Y TAM AÑO MÁXIMO PERMITIDOS =====
$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$tamano_maximo = 5 * 1024 * 1024; // 5MB

// ===== 5.2. VALIDAR EXTENSION =====
if (!in_array($extension, $extensiones_permitidas)) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido. Usa JPG, PNG, GIF o WEBP']);
    exit;
}

// ===== 5.3. VALIDAR TAM AÑO =====
if ($tamano > $tamano_maximo) {
    echo json_encode(['success' => false, 'message' => 'La imagen no puede superar los 5MB']);
    exit;
}

/* ========== 6. GUARDAR ARCHIVO ========== */
// ===== 6.1. GENERAR NOMBRE Único Y RUTA COMPLETA =====
$nombre_archivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extension;
$ruta_completa = $upload_dir . $nombre_archivo;

// ===== 6.2. MOVER ARCHIVO A DIRECTORIO FINAL =====
if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    
    /* ========== 7. ACTUALIZAR BASE DE DATOS ========== */
    // ===== 7.1. OBTENER FOTO ANTERIOR =====
    $query = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $foto_anterior = $result->fetch_assoc()['foto_perfil'];
    
    // ===== 7.2. ACTUALIZAR FOTO EN LA BD =====
    $update = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
    $stmt = $enlace->prepare($update);
    $stmt->bind_param("si", $nombre_archivo, $usuario_id);
    
    if ($stmt->execute()) {
        /* ========== 8. LIMPIAR FOTO ANTERIOR ========== */
        // ===== 8.1. ELIMINAR FOTO ANTERIOR SI NO ES DEFAULT =====
        if ($foto_anterior && $foto_anterior !== 'default.png') {
            $ruta_anterior = $upload_dir . $foto_anterior;
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
        }
        
        /* ========== 9. REGISTRAR EVENTO =====*/
        // ===== 9.1. LOGUEAR CAMBIO DE FOTO =====
        AppLogger::info("Foto de perfil actualizada", [
            'usuario_id' => $usuario_id,
            'usuario' => $session['user_name'],
            'foto' => $nombre_archivo
        ]);
        
        /* ========== 10. RETORNAR EXITO ========== */
        // ===== 10.1. RESPUESTA POSITIVA =====
        echo json_encode([
            'success' => true,
            'message' => 'Foto actualizada correctamente',
            'foto' => $nombre_archivo
        ]);
    } else {
        /* ========== 11. MANEJO DE ERROR EN BD ========== */
        // ===== 11.1. RESPUESTA DE ERROR =====
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
    }
} else {
    /* ========== 12. MANEJO DE ERROR EN UPLOAD ========== */
    // ===== 12.1. ERROR AL GUARDAR ARCHIVO =====
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
}
?>