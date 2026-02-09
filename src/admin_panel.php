<?php
// Iniciamos sesión
session_start();

// Incluimos conexión correcta
include 'backend/includes/conexion.php';

// Comprobamos que solo el admin pueda acceder
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Si no es admin, redirigimos al login
    header("Location: login.php");
    exit;
}

// Borrar usuario si se solicita
if (isset($_GET['borrar']) && is_numeric($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    
    // No permitir borrarse a sí mismo
    if ($id_borrar != $_SESSION['usuario_id']) {
        $stmt = $enlace->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_borrar);
        $stmt->execute();
        
        $mensaje = $stmt->affected_rows > 0 ? 
                   "Usuario borrado correctamente" : 
                   "Error al borrar usuario";
    } else {
        $mensaje = "No puedes borrar tu propia cuenta";
    }
}

// Obtener todos los usuarios
$sql = "SELECT id, usuario, email, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC";
$resultado = $enlace->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - RuleMKW</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .usuarios-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .usuarios-table th {
            background: #16213e;
            padding: 15px;
            text-align: left;
            border-bottom: 3px solid #e94560;
        }
        
        .usuarios-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .usuarios-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        
        .btn-borrar {
            background: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-borrar:hover {
            background: #cc0000;
        }
        
        .btn-volver {
            background: #0fcc45;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        
        .admin-mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: rgba(15, 204, 69, 0.2);
            border: 1px solid #0fcc45;
        }
        
        .role-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .role-admin {
            background: #e94560;
            color: white;
        }
        
        .role-user {
            background: #0fcc45;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Panel de Administración</h1>
            <div>
                <a href="index.php" class="btn-volver">Volver al inicio</a>
                <a href="logout.php" style="margin-left: 10px; color: #ff4444;">Salir</a>
            </div>
        </div>
        
        <?php if (isset($mensaje)): ?>
            <div class="admin-mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <h2>Gestión de Usuarios</h2>
        <p>Total de usuarios: <?php echo $resultado->num_rows; ?></p>
        
        <table class="usuarios-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($usuario = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $usuario['id']; ?></td>
                    <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td>
                        <span class="role-badge <?php echo $usuario['rol'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                            <?php echo $usuario['rol']; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                    <td>
                        <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                        <button class="btn-borrar" 
                                onclick="if(confirm('¿Borrar usuario <?php echo htmlspecialchars($usuario['usuario']); ?>?')) 
                                location.href='?borrar=<?php echo $usuario['id']; ?>'">
                            🗑️ Borrar
                        </button>
                        <?php else: ?>
                        <em>Tú</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 40px;">
            <h3>Resumen del Sistema</h3>
            <?php
            // Obtener estadísticas generales
            $stats = [
                'total_usuarios' => $resultado->num_rows,
                'total_copas' => $enlace->query("SELECT COUNT(*) as total FROM copas")->fetch_assoc()['total'],
                'total_circuitos' => $enlace->query("SELECT COUNT(*) as total FROM circuitos")->fetch_assoc()['total'],
                'total_tiradas' => $enlace->query("SELECT SUM(veces_seleccionado) as total FROM estadisticas_usuario")->fetch_assoc()['total'] ?: 0
            ];
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                <div style="background: rgba(233, 69, 96, 0.1); padding: 15px; border-radius: 10px; border-left: 4px solid #e94560;">
                    <strong>Usuarios</strong>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['total_usuarios']; ?></div>
                </div>
                <div style="background: rgba(15, 204, 69, 0.1); padding: 15px; border-radius: 10px; border-left: 4px solid #0fcc45;">
                    <strong>Copas</strong>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['total_copas']; ?></div>
                </div>
                <div style="background: rgba(66, 135, 245, 0.1); padding: 15px; border-radius: 10px; border-left: 4px solid #4287f5;">
                    <strong>Circuitos</strong>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['total_circuitos']; ?></div>
                </div>
                <div style="background: rgba(255, 215, 0, 0.1); padding: 15px; border-radius: 10px; border-left: 4px solid gold;">
                    <strong>Tiradas</strong>
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['total_tiradas']; ?></div>
                </div>
            </div>
        </div>
        
        <a href="index.html" class="btn-volver">Volver al inicio</a>
    </div>
</body>
</html>