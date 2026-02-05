<?php
// Iniciar sesión solo si existe
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RuleMKW - Ruleta de Circuitos</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body>
    <!-- Incluir navbar desde PHP -->
    <?php include '/backend/includes/generar_navbar.php'; ?>
    
    <main class="container py-5 mt-5">
        <!-- Alertas Dinámicas -->
        <div id="alertContainer" class="mb-4"></div>
        
        <!-- Contador de Seleccionados -->
        <div id="contadorSeleccionados" class="contador-seleccionados animate__animated animate__fadeInRight">
            <i class="fas fa-check-circle me-2"></i>
            <span id="contadorTexto">0/4 circuitos seleccionados</span>
            <div class="progress mt-1" style="height: 3px;">
                <div id="progressBar" class="progress-bar bg-success" style="width: 0%"></div>
            </div>
        </div>
        
        <!-- ========== SECCIÓN RULETA ========== -->
        <section class="ruleta-section mb-5">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-danger mb-3">
                    <i class="fas fa-dice me-3"></i>Ruleta de Circuitos
                </h1>
                <p class="lead text-muted">Selecciona tus circuitos favoritos y deja que la suerte decida</p>
                <?php if(!isset($_SESSION['usuario_id'])): ?>
                <div class="alert alert-info d-inline-block">
                    <i class="fas fa-info-circle me-2"></i>
                    Puedes usar la ruleta sin iniciar sesión. <a href="../backend/login.php" class="alert-link">Inicia sesión</a> para guardar tus estadísticas.
                </div>
                <?php endif; ?>
            </div>
            
            <div class="row g-4">
                <!-- Panel de Ruleta -->
                <div class="col-lg-7">
                    <div class="ruleta-container p-4 rounded-4 shadow-lg">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-sync-alt me-2 text-warning"></i>
                            <span class="ruleta-title">Ruleta Activa</span>
                        </h3>
                        
                        <!-- Animación de Ruleta -->
                        <div class="ruleta-animacion">
                            <div class="ruleta-stage">
                                <!-- Slot 1 -->
                                <div class="ruleta-slot slot-1" id="slot1">
                                    <div class="slot-content">
                                        <div class="slot-placeholder">
                                            <i class="fas fa-flag-checkered fa-2x"></i>
                                            <p class="mt-2">Esperando...</p>
                                        </div>
                                    </div>
                                    <div class="slot-number">1</div>
                                </div>
                                
                                <!-- Slot 2 -->
                                <div class="ruleta-slot slot-2" id="slot2">
                                    <div class="slot-content">
                                        <div class="slot-placeholder">
                                            <i class="fas fa-flag-checkered fa-2x"></i>
                                            <p class="mt-2">Esperando...</p>
                                        </div>
                                    </div>
                                    <div class="slot-number">2</div>
                                </div>
                                
                                <!-- Slot 3 -->
                                <div class="ruleta-slot slot-3" id="slot3">
                                    <div class="slot-content">
                                        <div class="slot-placeholder">
                                            <i class="fas fa-flag-checkered fa-2x"></i>
                                            <p class="mt-2">Esperando...</p>
                                        </div>
                                    </div>
                                    <div class="slot-number">3</div>
                                </div>
                                
                                <!-- Slot 4 -->
                                <div class="ruleta-slot slot-4" id="slot4">
                                    <div class="slot-content">
                                        <div class="slot-placeholder">
                                            <i class="fas fa-flag-checkered fa-2x"></i>
                                            <p class="mt-2">Esperando...</p>
                                        </div>
                                    </div>
                                    <div class="slot-number">4</div>
                                </div>
                                
                                <!-- Efecto de brillo -->
                                <div class="ruleta-glow"></div>
                                
                                <!-- Indicador de selección -->
                                <div class="ruleta-indicator animate__animated">
                                    <i class="fas fa-chevron-down fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Controles de Ruleta -->
                        <div class="ruleta-controls text-center mt-5">
                            <button id="btnGirar" class="btn btn-danger btn-lg px-5 py-3 me-3" disabled>
                                <i class="fas fa-play me-2"></i>
                                <span class="fw-bold">GIRAR RULETA</span>
                            </button>
                            <button id="btnReset" class="btn btn-outline-secondary btn-lg px-5 py-3">
                                <i class="fas fa-redo me-2"></i>
                                REINICIAR
                            </button>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <div class="alert alert-info d-inline-block">
                                <i class="fas fa-info-circle me-2"></i>
                                Selecciona entre 2 y 4 circuitos para comenzar
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Panel de Circuitos Seleccionados -->
                <div class="col-lg-5">
                    <div class="selected-container p-4 rounded-4 shadow-lg h-100">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-list-check me-2 text-primary"></i>
                            Circuitos Seleccionados
                        </h3>
                        
                        <div id="circuitosSeleccionados" class="selected-grid">
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-map-marked-alt fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">Sin circuitos seleccionados</h4>
                                <p class="text-muted">Selecciona circuitos de las copas para comenzar</p>
                            </div>
                        </div>
                        
                        <div class="selected-info mt-4 p-3 bg-light rounded">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 id="selectedCount" class="fw-bold">0</h5>
                                    <small class="text-muted">Seleccionados</small>
                                </div>
                                <div class="col-6">
                                    <h5 id="maxCount" class="fw-bold">4</h5>
                                    <small class="text-muted">Máximo</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- ========== SECCIÓN RESULTADOS ========== -->
        <section class="results-section mb-5" id="resultsSection" style="display: none;">
            <div class="results-container p-4 rounded-4 shadow-lg">
                <h3 class="text-center mb-4">
                    <i class="fas fa-trophy me-2 text-warning"></i>
                    Resultados de la Ruleta
                </h3>
                
                <div class="row g-4" id="resultadoRuleta">
                    <!-- Los resultados se mostrarán aquí -->
                </div>
                
                <div class="text-center mt-4">
                    <button id="btnNuevoIntento" class="btn btn-outline-primary">
                        <i class="fas fa-plus-circle me-2"></i> Nuevo Intento
                    </button>
                    <?php if(!isset($_SESSION['usuario_id'])): ?>
                    <div class="mt-3">
                        <a href="../backend/login.php" class="btn btn-sm btn-success">
                            <i class="fas fa-sign-in-alt me-1"></i> Inicia sesión para guardar estadísticas
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- ========== SECCIÓN SELECCIÓN DE CIRCUITOS ========== -->
        <section class="selection-section">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">
                    <i class="fas fa-map-marked-alt me-3 text-success"></i>
                    Selecciona tus Circuitos
                </h2>
                <p class="lead text-muted">Elige entre 2 y 4 circuitos para la ruleta</p>
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge bg-danger me-2">Mínimo: 2</span>
                    <span class="badge bg-success">Máximo: 4</span>
                </div>
            </div>
            
            <!-- Acordeón de Copas -->
            <div class="accordion" id="copasAccordion">
                <!-- Las copas se cargarán con JavaScript -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando circuitos...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando circuitos...</p>
                </div>
            </div>
        </section>
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
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-discord fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-3">Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none">Inicio</a></li>
                        <li class="mb-2"><a href="perfil.html" class="text-white-50 text-decoration-none">Perfil</a></li>
                        <li class="mb-2"><a href="historial.html" class="text-white-50 text-decoration-none">Historial</a></li>
                        <li class="mb-2"><a href="../backend/login.php" class="text-white-50 text-decoration-none">Iniciar Sesión</a></li>
                        <li><a href="../backend/registro.php" class="text-white-50 text-decoration-none">Registro</a></li>
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
    <script src="js/circuitos.js"></script>
    <script src="js/ruleta.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar circuitos desde la base de datos
            CircuitosApp.init();
            
            // Botón nuevo intento
            document.getElementById('btnNuevoIntento')?.addEventListener('click', function() {
                document.getElementById('resultsSection').style.display = 'none';
            });
        });
    </script>
</body>
</html>