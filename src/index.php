<?php
// Iniciar sesión solo si existe
session_start();

// ========== GESTIÓN DE COOKIES CON PHP ==========
$mostrar_banner_cookies = false;
$cookie_consent = $_COOKIE['cookie_consent'] ?? null;

// Si no existe la cookie, mostrar el banner
if (!$cookie_consent) {
    $mostrar_banner_cookies = true;
}

// Procesar si un usuario a aceptado o rechazado las cookies
if (isset($_GET['cookie_action'])) {
    $action = $_GET['cookie_action'];
    $expires = time() + (365 * 24 * 60 * 60);
    
    if ($action === 'accept') {
        setcookie('cookie_consent', 'accepted', $expires, '/');
        
        // Registrar log de cookie aceptada
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
        
        // Para guardar en un archivo de log
        $log_entry = date('Y-m-d H:i:s') . " - Cookie aceptada - IP: $ip - User Agent: $user_agent\n";
        file_put_contents(__DIR__ . '/logs/cookies.log', $log_entry, FILE_APPEND);
        
        // Redirigir para quitar el parámetro de la URL
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } elseif ($action === 'reject') {
        setcookie('cookie_consent', 'rejected', $expires, '/');
        
        // Registrar log de cookie rechazada
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
        
        $log_entry = date('Y-m-d H:i:s') . " - Cookie rechazada - IP: $ip - User Agent: $user_agent\n";
        file_put_contents(__DIR__ . '/logs/cookies.log', $log_entry, FILE_APPEND);
        
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}
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
    <!-- COOKIES CSS -->
    <link rel="stylesheet" href="css/cookies.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Incluir navbar desde PHP -->
    <?php include 'backend/includes/generar_navbar.php'; ?>
    
    <!-- ========== BANNER DE COOKIES (PHP) ========== -->
    <?php if ($mostrar_banner_cookies): ?>
    <div id="cookieBanner" class="cookie-banner">
        <div class="cookie-content">
            <div class="cookie-icon">
                <i class="fas fa-cookie-bite"></i>
            </div>
            <div class="cookie-text">
                <h4>Uso de cookies</h4>
                <p>Utilizamos cookies propias para mejorar tu experiencia en RuleMKW. 
                   Al hacer clic en "Aceptar", consientes el uso de todas las cookies. 
                   Puedes obtener más información en nuestras 
                   <a href="media/PoliticasRuleMKW.pdf" download class="cookie-link">políticas de privacidad</a>.</p>
            </div>
            <div class="cookie-buttons">
                <a href="?cookie_action=accept" class="btn-cookie-accept">
                    <i class="fas fa-check"></i> Aceptar
                </a>
                <a href="?cookie_action=reject" class="btn-cookie-reject">
                    <i class="fas fa-times"></i> Rechazar
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <main class="container py-5 mt-5 flex-grow-1">
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
                    Ruleta de Circuitos
                </h1>
                <p class="lead text-muted">Selecciona tus circuitos favoritos y deja que la suerte decida</p>
                <?php if(!isset($_SESSION['usuario_id'])): ?>
                <div class="alert alert-info d-inline-block">
                    <i class="fas fa-info-circle me-2"></i>
                    Puedes usar la ruleta sin iniciar sesión. <a href="backend/login.php" class="alert-link">Inicia sesión</a> para guardar tus estadísticas.
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
                
                <!-- ========== PANEL DE RESULTADOS ========== -->
                <div class="col-lg-5">
                    <div class="selected-container p-4 rounded-4 shadow-lg h-100">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-trophy me-2 text-warning"></i>
                            Último Resultado
                        </h3>
                        
                        <div id="resultadosGrid" class="selected-grid">
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-history fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">Sin resultados</h4>
                                <p class="text-muted">Gira la ruleta para ver el circuito ganador</p>
                            </div>
                        </div>
                        
                        <div class="selected-info mt-4 p-3 bg-light rounded">
                            <div class="row text-center">
                                <div class="col-12">
                                    <h5 id="resultadosCount" class="fw-bold">0</h5>
                                    <small class="text-muted">Veces girada</small>
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
                        <li class="mb-2"><a href="backend/login.php" class="text-white-50 text-decoration-none">Iniciar Sesión</a></li>
                        <li><a href="backend/registro.php" class="text-white-50 text-decoration-none">Registro</a></li>
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
    <script src="js/ruleta.js"></script>
    <!-- COOKIES JS (opcional, para funcionalidades extra) -->
    <script src="js/cookies.js"></script>
    
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