<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RuleMKW - Perfil de Usuario</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- ========== NAVBAR ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.html">
                <img src="media/iconos/logo.png" alt="RuleMKW" height="45" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">
                            <i class="fas fa-home me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="perfil.html">
                            <i class="fas fa-user me-1"></i> Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="historial.html">
                            <i class="fas fa-history me-1"></i> Historial
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="nav-link text-light me-3">
                        <i class="fas fa-user-circle me-1"></i> Usuario
                    </span>
                    <a class="nav-link btn btn-outline-light btn-sm" href="backend/logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-5 mt-5">
        <div class="row">
            <!-- Panel de Perfil -->
            <div class="col-lg-4 mb-4">
                <div class="profile-card p-4 rounded-4 shadow-lg">
                    <div class="text-center mb-4">
                        <div class="avatar-container mb-3">
                            <div class="avatar">
                                <i class="fas fa-user-circle fa-6x text-primary"></i>
                            </div>
                            <div class="avatar-badge">
                                <i class="fas fa-crown text-warning"></i>
                            </div>
                        </div>
                        <h3 id="userName" class="fw-bold">Usuario Ejemplo</h3>
                        <p class="text-muted" id="userEmail">usuario@ejemplo.com</p>
                        
                        <div class="mt-4">
                            <span class="badge bg-primary">
                                <i class="fas fa-medal me-1"></i> Nivel 5
                            </span>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-trophy me-1"></i> Premium
                            </span>
                        </div>
                    </div>
                    
                    <div class="stats-grid mb-4">
                        <div class="stat-item text-center p-3">
                            <h2 class="fw-bold text-primary">24</h2>
                            <small>Partidas Jugadas</small>
                        </div>
                        <div class="stat-item text-center p-3">
                            <h2 class="fw-bold text-success">18</h2>
                            <small>Circuitos Únicos</small>
                        </div>
                        <div class="stat-item text-center p-3">
                            <h2 class="fw-bold text-warning">156</h2>
                            <small>Veces Girado</small>
                        </div>
                    </div>
                    
                    <div class="user-info">
                        <p><i class="fas fa-calendar me-2 text-muted"></i> Miembro desde: <span id="memberSince">Ene 2024</span></p>
                        <p><i class="fas fa-clock me-2 text-muted"></i> Última actividad: <span id="lastActivity">Hoy</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="col-lg-8">
                <!-- Gráfico de Estadísticas -->
                <div class="card mb-4 rounded-4 shadow-lg border-0">
                    <div class="card-header bg-primary text-white rounded-top-4">
                        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Mis Estadísticas</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statsChart" height="300"></canvas>
                    </div>
                </div>
                
                <!-- Top 3 Circuitos -->
                <div class="card mb-4 rounded-4 shadow-lg border-0">
                    <div class="card-header bg-success text-white rounded-top-4">
                        <h4 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 3 Circuitos Más Jugados</h4>
                    </div>
                    <div class="card-body">
                        <div class="row" id="topCircuits">
                            <!-- Se llenará con JavaScript -->
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Historial Reciente -->
                <div class="card rounded-4 shadow-lg border-0">
                    <div class="card-header bg-info text-white rounded-top-4">
                        <h4 class="mb-0"><i class="fas fa-history me-2"></i>Historial Reciente</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar me-1"></i> Fecha</th>
                                        <th><i class="fas fa-map me-1"></i> Circuitos</th>
                                        <th><i class="fas fa-star me-1"></i> Resultado</th>
                                        <th><i class="fas fa-cog me-1"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTable">
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No hay historial disponible</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- ========== FOOTER ========== -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h4 class="fw-bold mb-3">
                        <img src="media/iconos/logo.png" alt="RuleMKW" height="40" class="me-2">
                        RuleMKW
                    </h4>
                    <p>Tu herramienta definitiva para seleccionar circuitos de Mario Kart Wii de forma aleatoria y divertida.</p>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-3">Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.html" class="text-white-50 text-decoration-none">Inicio</a></li>
                        <li class="mb-2"><a href="perfil.html" class="text-white-50 text-decoration-none">Perfil</a></li>
                        <li class="mb-2"><a href="historial.html" class="text-white-50 text-decoration-none">Historial</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">Contacto</h5>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i> contacto@rulemkw.com</p>
                    <p><i class="fas fa-graduation-cap me-2"></i> Proyecto TFG - Universidad</p>
                    <div class="mt-4">
                        <small class="text-white-50">© 2024 RuleMKW. Todos los derechos reservados.</small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/perfil.js"></script>
</body>
</html>