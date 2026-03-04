<?php
// Verificar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener la página actual
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - RuleMKW</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
            <img src="../media/iconos/logo.png" alt="RuleMKW" height="40" width="160" class="me-2 p-0">
                <p>Panel de Administración</p>
            </div>
            
            <div class="sidebar-user">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin'); ?></div>
                <div class="user-role">Administrador</div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                            <span class="icon">📊</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="../index.php" target="_blank">
                            <span class="icon">🎮</span>
                            <span>Página Principal</span>
                        </a>
                    </li>
                    <li>
                        <a href="usuarios.php" class="<?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
                            <span class="icon">👥</span>
                            <span>Gestión de Usuarios</span>
                        </a>
                    </li>
                    <li>
                        <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                            <span class="icon">📋</span>
                            <span>Logs del Sistema</span>
                        </a>
                    </li>
                    <li class="logout">
                        <a href="../backend/logout.php">
                            <span class="icon">🚪</span>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Contenido principal -->
        <main class="admin-content">