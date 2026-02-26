<?php
require_once 'layout/sidebar.php';
require_once '../backend/includes/conexion.php';
require_once '../backend/includes/Logger.php'; // Añadido para logs

$mensaje = '';
$tipo_mensaje = 'success'; // success, error, warning

// Verificar que es admin (seguridad adicional)
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'borrar_usuario':
                $id_borrar = intval($_POST['usuario_id']);
                
                if ($id_borrar != $_SESSION['usuario_id']) {
                    // Obtener info del usuario antes de borrar
                    $stmt = $enlace->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
                    $stmt->bind_param("i", $id_borrar);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $usuario_info = $result->fetch_assoc();
                        
                        // Borrar usuario
                        $stmt = $enlace->prepare("DELETE FROM usuarios WHERE id = ?");
                        $stmt->bind_param("i", $id_borrar);
                        
                        if ($stmt->execute() && $stmt->affected_rows > 0) {
                            $mensaje = "Usuario '{$usuario_info['usuario']}' borrado correctamente";
                            $tipo_mensaje = 'success';
                            
                            // LOG: Usuario eliminado
                            AppLogger::info("Usuario eliminado por administrador", [
                                'admin_id' => $_SESSION['usuario_id'],
                                'admin' => $_SESSION['usuario_nombre'],
                                'usuario_eliminado' => $usuario_info['usuario'],
                                'email_eliminado' => $usuario_info['email'],
                                'usuario_id_eliminado' => $id_borrar,
                                'ip' => $_SERVER['REMOTE_ADDR']
                            ]);
                        } else {
                            $mensaje = "Error al borrar usuario";
                            $tipo_mensaje = 'error';
                            
                            // LOG: Error al borrar usuario
                            AppLogger::error("Error al intentar borrar usuario", [
                                'admin_id' => $_SESSION['usuario_id'],
                                'admin' => $_SESSION['usuario_nombre'],
                                'usuario_id_intentado' => $id_borrar,
                                'error' => $enlace->error,
                                'ip' => $_SERVER['REMOTE_ADDR']
                            ]);
                        }
                    } else {
                        $mensaje = "Usuario no encontrado";
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = "No puedes borrar tu propia cuenta";
                    $tipo_mensaje = 'warning';
                    
                    // LOG: Intento de auto-eliminación
                    AppLogger::warning("Intento de auto-eliminación por administrador", [
                        'admin_id' => $_SESSION['usuario_id'],
                        'admin' => $_SESSION['usuario_nombre'],
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                }
                break;
                
            case 'cambiar_rol':
                $id_usuario = intval($_POST['usuario_id']);
                $nuevo_rol = $_POST['nuevo_rol'];
                
                // Validar que el rol sea válido
                if (!in_array($nuevo_rol, ['admin', 'usuario'])) {
                    $mensaje = "Rol no válido";
                    $tipo_mensaje = 'error';
                    break;
                }
                
                if ($id_usuario != $_SESSION['usuario_id']) {
                    // Obtener información del usuario antes del cambio
                    $stmt = $enlace->prepare("SELECT usuario, rol FROM usuarios WHERE id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $usuario_info = $result->fetch_assoc();
                    $rol_anterior = $usuario_info['rol'];
                    
                    // Actualizar rol
                    $stmt = $enlace->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                    $stmt->bind_param("si", $nuevo_rol, $id_usuario);
                    
                    if ($stmt->execute()) {
                        $mensaje = "Rol de '{$usuario_info['usuario']}' actualizado de '$rol_anterior' a '$nuevo_rol'";
                        $tipo_mensaje = 'success';
                        
                        // LOG: Cambio de rol
                        AppLogger::info("Cambio de rol de usuario", [
                            'admin_id' => $_SESSION['usuario_id'],
                            'admin' => $_SESSION['usuario_nombre'],
                            'usuario_afectado' => $usuario_info['usuario'],
                            'usuario_id' => $id_usuario,
                            'rol_anterior' => $rol_anterior,
                            'rol_nuevo' => $nuevo_rol,
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    } else {
                        $mensaje = "Error al actualizar rol";
                        $tipo_mensaje = 'error';
                        
                        // LOG: Error al cambiar rol
                        AppLogger::error("Error al cambiar rol de usuario", [
                            'admin_id' => $_SESSION['usuario_id'],
                            'admin' => $_SESSION['usuario_nombre'],
                            'usuario_id' => $id_usuario,
                            'rol_intentado' => $nuevo_rol,
                            'error' => $enlace->error,
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    }
                } else {
                    $mensaje = "No puedes cambiar tu propio rol";
                    $tipo_mensaje = 'warning';
                    
                    // LOG: Intento de auto-cambio de rol
                    AppLogger::warning("Intento de auto-cambio de rol por administrador", [
                        'admin_id' => $_SESSION['usuario_id'],
                        'admin' => $_SESSION['usuario_nombre'],
                        'rol_intentado' => $nuevo_rol,
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                }
                break;
        }
    }
}

// Obtener todos los usuarios
$usuarios = $enlace->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM estadisticas_usuario WHERE usuario_id = u.id) as total_tiradas 
    FROM usuarios u 
    ORDER BY u.fecha_registro DESC
");
?>

<div class="content-header">
    <h1>Gestión de Usuarios</h1>
    <div class="breadcrumb">Administración / Usuarios</div>
</div>

<?php if ($mensaje): ?>
    <div style="
        padding: 15px; 
        border-radius: 5px; 
        margin-bottom: 20px;
        <?php 
        if ($tipo_mensaje == 'success') echo 'background: rgba(15, 204, 69, 0.2); border: 1px solid #0fcc45; color: #0fcc45;';
        if ($tipo_mensaje == 'error') echo 'background: rgba(233, 69, 96, 0.2); border: 1px solid #e94560; color: #e94560;';
        if ($tipo_mensaje == 'warning') echo 'background: rgba(255, 193, 7, 0.2); border: 1px solid #ffc107; color: #ffc107;';
        ?>
    ">
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <div style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <h3>Lista de usuarios registrados</h3>
        <span style="color: #888;">Total: <?php echo $usuarios->num_rows; ?> usuarios</span>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Tiradas</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($usuario = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?php echo $usuario['id']; ?></td>
                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td>
                    <?php if ($usuario['id'] == $_SESSION['usuario_id']): ?>
                        <span class="badge badge-admin" style="background: #e94560; color: white; padding: 5px 10px; border-radius: 4px;">
                            <?php echo $usuario['rol']; ?> (tú)
                        </span>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="cambiar_rol">
                            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                            <select name="nuevo_rol" onchange="this.form.submit()" style="padding: 5px; background: #16213e; color: white; border: 1px solid #e94560; border-radius: 4px;">
                                <option value="usuario" <?php echo $usuario['rol'] == 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                                <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </form>
                    <?php endif; ?>
                </td>
                <td><?php echo $usuario['total_tiradas']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                <td>
                    <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que quieres borrar al usuario <?php echo htmlspecialchars($usuario['usuario']); ?>?\nEsta acción no se puede deshacer.');">
                        <input type="hidden" name="action" value="borrar_usuario">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                        <button type="submit" class="btn btn-danger" style="background: #e94560; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                            🗑️ Borrar
                        </button>
                    </form>
                    <?php else: ?>
                    <span style="color: #666; font-style: italic;">(tu cuenta)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
echo '</main></div>';
?>
</body>
</html>