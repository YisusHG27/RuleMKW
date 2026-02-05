<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- ========== NAVBAR DINÁMICA ========== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../frontend/index.php">
            <!-- Logo más grande (60px) y sin texto -->
            <img src="../frontend/media/iconos/logo.png" alt="RuleMKW" height="60" class="me-2">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="../frontend/index.php">
                        <i class="fas fa-home me-1"></i> Inicio
                    </a>
                </li>
                <?php if(isset($_SESSION['usuario_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../frontend/perfil.php">
                        <i class="fas fa-user me-1"></i> Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../frontend/historial.php">
                        <i class="fas fa-history me-1"></i> Historial
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="navbar-nav">
                <?php if(isset($_SESSION['usuario_id'])): ?>
                    <!-- Usuario logueado -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            <span><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../frontend/perfil.php">
                                <i class="fas fa-user me-2"></i> Mi Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="../frontend/historial.php">
                                <i class="fas fa-history me-2"></i> Mi Historial
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../backend/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <a class="nav-link btn btn-outline-light btn-sm mx-1" href="../login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                    </a>
                    <a class="nav-link btn btn-danger btn-sm mx-1" href="../backend/registro.php">
                        <i class="fas fa-user-plus me-1"></i> Registro
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>