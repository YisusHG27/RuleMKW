<?php
/* ==========================================================================
   GET_USUARIO_ACTUAL.PHP - OBTENER DATOS DEL USUARIO LOGUEADO
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. ESTABLECER TIPO DE RESPUESTA JSON =====
header('Content-Type: application/json');

// ===== 1.2. INCLUIR DEPENDENCIAS =====
require_once '../includes/conexion.php';
require_once '../includes/check_session.php';

/* ========== 2. VALIDAR SESIÓN DEL USUARIO ========== */
// ===== 2.1. OBTENER DATOS DE SESIÓN =====
$session = checkSession();

// ===== 2.2. VERIFICAR SI EL USUARIO ESTÁ LOGUEADO =====
if (!$session['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// ===== 2.3. OBTENER ID DEL USUARIO =====
$usuario_id = $session['user_id'];

/* ========== 3. OBTENER DATOS DEL USUARIO ========== */
try {
    // ===== 3.1. CONSULTAR DATOS DEL USUARIO EN LA BD =====
    $query = "SELECT id, usuario as nombre, email, foto_perfil, rol, fecha_registro FROM usuarios WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // ===== 3.2. VERIFICAR SI EL USUARIO EXISTE =====
    if ($row = $result->fetch_assoc()) {
        // ===== 3.3. OBTENER ÚTIMA ACTIVIDAD DEL USUARIO =====
        $query_actividad = "SELECT MAX(fecha) as ultima_actividad FROM historial_tiradas WHERE usuario_id = ?";
        $stmt_act = $enlace->prepare($query_actividad);
        $stmt_act->bind_param("i", $usuario_id);
        $stmt_act->execute();
        $result_act = $stmt_act->get_result();
        $ultima_actividad = $result_act->fetch_assoc();
        
        // ===== 3.4. FORMATEAR FECHAS =====
        $fecha_registro = date('Y-m-d H:i:s', strtotime($row['fecha_registro']));
        $ultima_act = $ultima_actividad['ultima_actividad'] ? date('Y-m-d H:i:s', strtotime($ultima_actividad['ultima_actividad'])) : $fecha_registro;
        
        // ===== 3.5. RETORNAR DATOS DEL USUARIO =====
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id' => (int)$row['id'],
                'nombre' => $row['nombre'],
                'email' => $row['email'],
                'foto' => $row['foto_perfil'] ?: 'default.png',
                'rol' => $row['rol'],
                'fecha_registro' => $fecha_registro,
                'ultima_actividad' => $ultima_act
            ]
        ]);
    } else {
        // ===== 3.6. USUARIO NO ENCONTRADO =====
        echo json_encode([
            'success' => false, 
            'message' => 'Usuario no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

$enlace->close();
?>