<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario está logueado, obtener su foto de perfil y rol
$foto_perfil = null;
$inicial_nombre = '';
$es_admin = false;

if (isset($_SESSION['usuario_id'])) {
    // Conectar a la base de datos para obtener la foto
    require_once __DIR__ . '/conexion.php';
    $usuario_id = $_SESSION['usuario_id'];
    $nombre_usuario = $_SESSION['usuario_nombre'];
    $es_admin = ($_SESSION['usuario_rol'] === 'admin');
    
    // Obtener primera letra del nombre para el avatar de texto
    $inicial_nombre = strtoupper(substr($nombre_usuario, 0, 1));
    
    $query = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $foto_perfil = $row['foto_perfil'] ?: null;
    }
    $stmt->close();
}
?>
<!-- ========== NAVBAR DINÁMICA ========== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center p-0" href="index.php">
            <img src="media/iconos/logo.png" alt="RuleMKW" height="40" width="160" class="me-2 p-0">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home me-1"></i> Inicio
                    </a>
                </li>
                <?php if(isset($_SESSION['usuario_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="perfil.php">
                        <i class="fas fa-user me-1"></i> Perfil
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- BOTÓN PARA ADMINISTRADORES -->
                <?php if($es_admin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin/dashboard.php">
                        <i class="fas fa-cog me-1"></i> Panel de Administración
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="navbar-nav">
                <!-- Esto lo pondrá en la barra de navegación si el usuario está logueado -->
                <?php if(isset($_SESSION['usuario_id'])): ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            
                            <!-- MOSTRAR FOTO O INICIAL -->
                            <?php if ($foto_perfil && file_exists(__DIR__ . '/../../media/perfil/' . $foto_perfil)): ?>
                                <!-- Si tiene foto, mostrarla -->
                                <div class="navbar-avatar-container me-2">
                                    <img src="media/perfil/<?php echo $foto_perfil; ?>" 
                                         alt="Foto de perfil" 
                                         class="navbar-avatar"
                                         onerror="this.style.display='none'; this.parentElement.classList.add('avatar-inicial'); this.parentElement.innerHTML='<?php echo $inicial_nombre; ?>';">
                                </div>
                            <?php else: ?>
                                <!-- Si no tiene foto, mostrar inicial -->
                                <div class="navbar-avatar-container avatar-inicial me-2">
                                    <?php echo $inicial_nombre; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- NOMBRE - MÁS GRANDE -->
                            <span class="navbar-user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil.php">
                                <i class="fas fa-user me-2"></i> Mi Perfil
                            </a></li>
                            
                            <!-- OPCIÓN DE ADMIN EN DROPDOWN (también) -->
                            <?php if($es_admin): ?>
                            <li><a class="dropdown-item" href="admin/dashboard.php">
                                <i class="fas fa-cog me-2"></i> Panel Admin
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="backend/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Esto lo pondrá en la barra de navegación si el usuario no está logueado -->
                    <a class="nav-link btn btn-outline-light btn-sm mx-1" href="backend/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                    </a>
                    <a class="nav-link btn btn-danger btn-sm mx-1" href="backend/registro.php">
                        <i class="fas fa-user-plus me-1"></i> Registro
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Estilos adicionales para la navbar -->
<style>
    /* Contenedor de la foto de perfil en navbar */
    .navbar-avatar-container {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #0d6efd;
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .navbar-avatar-container:hover {
        transform: scale(1.05);
        border-color: #0a58ca;
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.4);
    }
    
    /* Estilo para avatar con inicial */
    .avatar-inicial {
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: white;
        font-size: 1.3rem;
        font-weight: bold;
        text-transform: uppercase;
        border: 2px solid white;
    }
    
    .navbar-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Nombre de usuario más grande */
    .navbar-user-name {
        font-size: 1.1rem;
        font-weight: 500;
        color: white;
    }
    
    /* Estilo especial para el botón de admin */
    .nav-link[href*="admin"] {
        background: rgba(13, 110, 253, 0.1);
        border-radius: 5px;
        margin-left: 5px;
    }
    
    .nav-link[href*="admin"]:hover {
        background: #0d6efd !important;
        color: white !important;
    }
    
    .nav-link[href*="admin"] i {
        color: #ffc107;
    }
    
    /* Ajustes para el dropdown */
    .navbar-nav .dropdown-menu {
        margin-top: 10px;
        border-radius: 10px;
        border: 1px solid rgba(13, 110, 253, 0.2);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .navbar-nav .dropdown-item {
        padding: 10px 20px;
        transition: all 0.2s;
    }
    
    .navbar-nav .dropdown-item:hover {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .navbar-nav .dropdown-item.text-danger:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545 !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .navbar-avatar-container {
            width: 35px;
            height: 35px;
        }
        
        .avatar-inicial {
            font-size: 1.1rem;
        }
        
        .navbar-user-name {
            font-size: 1rem;
        }
        
        .nav-link[href*="admin"] {
            margin-left: 0;
            margin-top: 5px;
        }
    }
</style>