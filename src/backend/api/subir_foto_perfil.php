<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/conexion.php';
require_once '../includes/check_session.php';
require_once '../includes/Logger.php';

$session = checkSession();
if (!$session['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $session['user_id'];
$upload_dir = '../../media/perfil/';

// Crear directorio si no existe
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Verificar si se envió un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    exit;
}

$archivo = $_FILES['foto'];
$nombre_original = $archivo['name'];
$extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
$tamano = $archivo['size'];

// Extensiones permitidas
$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$tamano_maximo = 5 * 1024 * 1024; // 5MB

// Validar extensión
if (!in_array($extension, $extensiones_permitidas)) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido. Usa JPG, PNG, GIF o WEBP']);
    exit;
}

// Validar tamaño
if ($tamano > $tamano_maximo) {
    echo json_encode(['success' => false, 'message' => 'La imagen no puede superar los 5MB']);
    exit;
}

// Generar nombre único
$nombre_archivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extension;
$ruta_completa = $upload_dir . $nombre_archivo;

// Mover el archivo
if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    // Obtener foto anterior para borrarla (excepto default.png)
    $query = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $foto_anterior = $result->fetch_assoc()['foto_perfil'];
    
    // Actualizar BD
    $update = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
    $stmt = $enlace->prepare($update);
    $stmt->bind_param("si", $nombre_archivo, $usuario_id);
    
    if ($stmt->execute()) {
        // Borrar foto anterior si no es la default
        if ($foto_anterior && $foto_anterior !== 'default.png') {
            $ruta_anterior = $upload_dir . $foto_anterior;
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
        }
        
        AppLogger::info("Foto de perfil actualizada", [
            'usuario_id' => $usuario_id,
            'usuario' => $session['user_name'],
            'foto' => $nombre_archivo
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Foto actualizada correctamente',
            'foto' => $nombre_archivo
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
}