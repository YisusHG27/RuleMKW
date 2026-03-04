<?php session_start();?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RuleMKW - Perfil de Usuario</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/perfil.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- ========== NAVBAR ========== -->
    <?php include 'backend/includes/generar_navbar.php'; ?>

    <main class="container py-5 mt-5 flex-grow-1">
        <!-- FILA 1: Avatar + Historial (dos columnas) -->
        <div class="row g-4 mb-4">
            <!-- Columna izquierda: Avatar y stats -->
            <div class="col-lg-4">
                <div class="profile-card p-4 rounded-4 shadow-lg">
                    <div class="text-center">
                        <div class="avatar-container mb-3 position-relative" id="avatarContainer">
                            <div class="avatar" id="avatarPreview">
                                <div class="avatar-inicial" id="avatarInicial" style="display: none;"></div>
                                <img src="media/perfil/default.png" 
                                     alt="Foto de perfil" 
                                     class="avatar-img"
                                     id="avatarImg">
                            </div>
                            <div class="avatar-badge" id="avatarBadge" style="display: none;">
                                <i class="fas fa-check text-success"></i>
                            </div>
                        </div>
                        
                        <h3 id="userName" class="fw-bold">Cargando...</h3>
                        <p class="text-muted" id="userEmail">cargando...</p>
                        
                        <div class="mt-2">
                            <span class="badge bg-primary" id="rolBadge">
                                <i class="fas fa-user me-1"></i> Usuario
                            </span>
                        </div>

                        <!-- Botones de foto -->
                        <div class="mt-3">
                            <input type="file" id="fotoPerfilInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                            <button class="btn btn-outline-primary btn-sm" id="btnSeleccionarFoto">
                                <i class="fas fa-camera me-1"></i> Cambiar foto
                            </button>
                            
                            <div id="fotoSeleccionadaInfo" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span id="nombreArchivo" class="text-truncate small" style="max-width: 150px;"></span>
                                    <button class="btn btn-success btn-sm" id="btnGuardarFoto">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="stats-mini-grid mt-4">
                        <div class="stat-mini-item">
                            <div class="stat-mini-value" id="vecesGirado">0</div>
                            <div class="stat-mini-label">Veces Girado</div>
                        </div>
                    </div>
                    
                    <!-- Info de fechas -->
                    <div class="user-info mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-calendar me-2 text-primary"></i> Miembro:</span>
                            <span id="memberSince">-</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span><i class="fas fa-clock me-2 text-primary"></i> Última actividad:</span>
                            <span id="lastActivity">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha: Historial con paginación -->
            <div class="col-lg-8">
                <div class="card h-100 rounded-4 shadow-lg border-0">
                    <div class="card-header bg-info text-white rounded-top-4 py-2 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial Reciente</h5>
                        <div class="pagination-controls">
                            <button class="btn btn-sm btn-light me-1" id="prevPage" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="text-white mx-2" id="pageInfo">Página 1</span>
                            <button class="btn btn-sm btn-light" id="nextPage" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">FECHA</th>
                                        <th>CIRCUITOS</th>
                                        <th class="pe-3">GANADOR</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTable">
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FILA 2: Top 3 Circuitos -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card rounded-4 shadow-lg border-0">
                    <div class="card-header bg-success text-white rounded-top-4 py-2">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 3 Circuitos Más Jugados</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="topCircuits">
                            <!-- Se llenará con JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FILA 3: Gráfico -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card rounded-4 shadow-lg border-0">
                    <div class="card-header bg-primary text-white rounded-top-4 py-2">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Frecuencia de Circuitos</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statsChart" height="300"></canvas>
                        <div id="chartNoData" class="text-center py-4" style="display: none;">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay datos suficientes para mostrar el gráfico</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- ========== FOOTER ========== -->
    <footer class="footer bg-dark text-white py-5 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h4 class="fw-bold mb-3">
                        <img src="media/iconos/logo.png" alt="RuleMKW" height="40" width="160" class="me-2">
                    </h4>
                    <p>Tu herramienta definitiva para seleccionar circuitos de Mario Kart World de forma aleatoria y divertida.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-discord fa-lg"></i></a>
                        <a href="https://github.com/YisusHG27" class="text-white"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-3">Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none">Inicio</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">Contacto</h5>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i> jahernandezg20@educarex.es</p>
                    <p><i class="fas fa-graduation-cap me-2"></i> Proyecto TFG - Jesús Antonio Hernández Gómez</p>
                    <div class="mt-4">
                        <small class="text-white-50">© 2026 RuleMKW. Todos los derechos reservados.</small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/circuitos.js"></script>
    <script src="js/perfil.js"></script>
</body>
</html>