<?php
require_once 'layout/sidebar.php';
require_once '../backend/includes/conexion.php';
require_once '../backend/logs/Monlog.php';

$monlog = new Monlog($enlace);
$mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'borrar_usuario':
                $id_borrar = $_POST['usuario_id'];
                if ($id_borrar != $_SESSION['usuario_id']) {
                    // Obtener info del usuario antes de borrar
                    $stmt = $enlace->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
                    $stmt->bind_param("i", $id_borrar);
                    $stmt->execute();
                    $usuario_info = $stmt->get_result()->fetch_assoc();
                    
                    // Borrar usuario
                    $stmt = $enlace->prepare("DELETE FROM usuarios WHERE id = ?");
                    $stmt->bind_param("i", $id_borrar);
                    
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $mensaje = "Usuario borrado correctamente";
                        $monlog->registrar(
                            'USER_DELETE',
                            "Usuario eliminado: {$usuario_info['usuario']} ({$usuario_info['email']})",
                            'ACCION'
                        );
                    } else {
                        $mensaje = "Error al borrar usuario";
                    }
                } else {
                    $mensaje = "No puedes borrar tu propia cuenta";
                    $monlog->registrar('USER_DELETE_ERROR', "Intento de auto-eliminación", 'WARNING');
                }
                break;
                
            case 'cambiar_rol':
                $id_usuario = $_POST['usuario_id'];
                $nuevo_rol = $_POST['nuevo_rol'];
                
                if ($id_usuario != $_SESSION['usuario_id']) {
                    $stmt = $enlace->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                    $stmt->bind_param("si", $nuevo_rol, $id_usuario);
                    
                    if ($stmt->execute()) {
                        $mensaje = "Rol actualizado correctamente";
                        $monlog->registrar(
                            'ROLE_CHANGE',
                            "Cambio de rol para usuario ID $id_usuario a $nuevo_rol",
                            'ACCION'
                        );
                    }
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
    <div style="background: rgba(15, 204, 69, 0.2); border: 1px solid #0fcc45; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<div class="table-container">
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
                        <span class="badge badge-admin"><?php echo $usuario['rol']; ?></span>
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
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Borrar usuario <?php echo htmlspecialchars($usuario['usuario']); ?>?');">
                        <input type="hidden" name="action" value="borrar_usuario">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                        <button type="submit" class="btn btn-danger">🗑️ Borrar</button>
                    </form>
                    <?php else: ?>
                    <span style="color: #666;">(Tú)</span>
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