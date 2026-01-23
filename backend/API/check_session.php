<?php
/**
 * Verifica si el usuario tiene sesión iniciada
 */
session_start();
header('Content-Type: application/json');

if(isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'rol' => $_SESSION['usuario_rol'] ?? 'usuario'
        ]
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>