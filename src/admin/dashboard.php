<?php
// admin/dashboard.php
require_once '../backend/includes/check_session.php';
require_once '../backend/includes/conexion.php';
require_once '../backend/includes/logger.php';

// Verificar que es admin
$session = requireRole('admin');

// Obtener estadísticas
$stats = [
    'usuarios' => 0,
    'admins' => 0,
    'usuarios_normales' => 0,
    'copas' => 0,
    'circuitos' => 0,
    'tiradas' => 0,
    'logs_hoy' => 0
];

// Estadísticas de usuarios
$result = $enlace->query("SELECT COUNT(*) as total FROM usuarios");
if ($result) $stats['usuarios'] = $result->fetch_assoc()['total'];

$result = $enlace->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='admin'");
if ($result) $stats['admins'] = $result->fetch_assoc()['total'];

$stats['usuarios_normales'] = $stats['usuarios'] - $stats['admins'];

// Verificar si existen las tablas
$tablas = [
    'copas' => "SELECT COUNT(*) as total FROM copas",
    'circuitos' => "SELECT COUNT(*) as total FROM circuitos"
];

foreach ($tablas as $key => $query) {
    $result = $enlace->query($query);
    if ($result) {
        $stats[$key] = $result->fetch_assoc()['total'];
    }
}

// Tiradas totales
$result = $enlace->query("SELECT COUNT(*) as total FROM historial_tiradas");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['tiradas'] = $row['total'] ?? 0;
} else {
    // Si la tabla no existe, mostrar 0
    $stats['tiradas'] = 0;
}

// Logs de hoy - AHORA LEE DEL ARCHIVO EN VEZ DE LA BD
$logsDir = __DIR__ . '/../logs/';
$fechaHoy = date('Y-m-d');
$archivoHoy = $logsDir . 'rulemkw-' . $fechaHoy . '.log';
$stats['logs_hoy'] = 0;

if (file_exists($archivoHoy)) {
    // Contar líneas del archivo de hoy
    $lineas = file($archivoHoy, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $stats['logs_hoy'] = count($lineas);
}

// Obtener últimos usuarios registrados
$ultimos_usuarios = $enlace->query("
    SELECT usuario, email, fecha_registro 
    FROM usuarios 
    ORDER BY fecha_registro DESC 
    LIMIT 5
");

// Obtener últimas acciones - AHORA LEE DEL ARCHIVO MÁS RECIENTE
$ultimos_logs = [];
$archivosLog = glob($logsDir . 'rulemkw-*.log');
if (!empty($archivosLog)) {
    rsort($archivosLog); // Ordenar por fecha (más reciente primero)
    $archivoReciente = $archivosLog[0];
    
    if (file_exists($archivoReciente)) {
        $lineas = file($archivoReciente, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // Tomar las últimas 10 líneas (más recientes)
        $ultimasLineas = array_slice($lineas, -10);
        
        foreach ($ultimasLineas as $linea) {
            $log = parsearLogLineaSimple($linea);
            if ($log) {
                $ultimos_logs[] = $log;
            }
        }
        // Revertir para mostrar las más recientes primero
        $ultimos_logs = array_reverse($ultimos_logs);
    }
}

// Función simple para parsear logs (para el dashboard)
function parsearLogLineaSimple($linea) {
    $patron = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*?)(\s+\{.*\})?$/';
    
    if (preg_match($patron, $linea, $matches)) {
        $fecha = $matches[1];
        $tipo = $matches[2];
        $mensaje = $matches[3];
        $contextoJson = isset($matches[4]) ? trim($matches[4]) : '';
        $contexto = !empty($contextoJson) ? json_decode($contextoJson, true) : [];
        
        // Determinar acción
        $accion = 'OTRO';
        if (stripos($mensaje, 'login') !== false) $accion = 'LOGIN';
        else if (stripos($mensaje, 'sesión') !== false || stripos($mensaje, 'logout') !== false) $accion = 'LOGOUT';
        else if (stripos($mensaje, 'registro') !== false) $accion = 'REGISTRO';
        else if (stripos($mensaje, 'dashboard') !== false) $accion = 'DASHBOARD';
        
        // Obtener usuario
        $usuario = 'Sistema';
        if (isset($contexto['usuario'])) {
            $usuario = $contexto['usuario'];
        }
        
        return [
            'fecha' => $fecha,
            'tipo' => $tipo,
            'accion' => $accion,
            'usuario_nombre' => $usuario
        ];
    }
    return null;
}

// LOG: Acceso al dashboard
if (!isset($_SESSION['dashboard_logged'])) {
    AppLogger::info("Acceso al dashboard de administración", [
        'usuario_id' => $session['user_id'],
        'usuario' => $session['user_name']
    ]);
    $_SESSION['dashboard_logged'] = true;
}

require_once 'layout/sidebar.php';
?>

<div class="content-header">
    <h1>Dashboard</h1>
    <div class="breadcrumb">Panel principal / Resumen del sistema</div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-title">Total Usuarios</div>
        <div class="stat-number"><?php echo $stats['usuarios']; ?></div>
        <div class="stat-desc">
            👑 <?php echo $stats['admins']; ?> admins · 
            👤 <?php echo $stats['usuarios_normales']; ?> usuarios
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-title">Circuitos</div>
        <div class="stat-number"><?php echo $stats['circuitos']; ?></div>
        <div class="stat-desc">En <?php echo $stats['copas']; ?> copas diferentes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-title">Tiradas Totales</div>
        <div class="stat-number"><?php echo number_format($stats['tiradas']); ?></div>
        <div class="stat-desc">Veces que se ha girado la ruleta</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-title">Logs Hoy</div>
        <div class="stat-number"><?php echo $stats['logs_hoy']; ?></div>
        <div class="stat-desc">Eventos registrados hoy</div>
    </div>
</div>

<!-- Últimos registros y actividad -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 30px;">
    <!-- Últimos usuarios -->
    <div style="background: rgba(255,255,255,0.05); border-radius: 10px; padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #e94560;">📝 Últimos usuarios registrados</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ultimos_usuarios && $ultimos_usuarios->num_rows > 0): ?>
                    <?php while($user = $ultimos_usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($user['fecha_registro'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #888;">No hay usuarios registrados</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Últimos logs -->
    <div style="background: rgba(255,255,255,0.05); border-radius: 10px; padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #e94560;">🔄 Últimas actividades</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Tipo</th>      <!-- Tipo antes que Acción -->
                    <th>Acción</th>     <!-- Acción después de Tipo -->
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ultimos_logs)): ?>
                    <?php foreach($ultimos_logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['usuario_nombre'] ?? 'Sistema'); ?></td>
                        <td>
                            <span class="log-badge log-<?php echo strtolower($log['tipo'] ?? 'info'); ?>">
                                <?php echo $log['tipo'] ?? 'INFO'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="log-accion log-accion-<?php echo strtolower($log['accion'] ?? 'otro'); ?>">
                                <?php echo htmlspecialchars($log['accion'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td><?php echo date('H:i', strtotime($log['fecha'] ?? 'now')); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #888;">No hay actividades recientes</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Últimas estadísticas adicionales -->
<div style="margin-top: 30px; background: rgba(255,255,255,0.05); border-radius: 10px; padding: 20px;">
    <h3 style="margin-bottom: 15px; color: #e94560;">📊 Estadísticas rápidas</h3>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; text-align: center;">
        <div>
            <div style="font-size: 24px; color: #e94560;"><?php echo $stats['logs_hoy']; ?></div>
            <div style="color: #888;">Logs hoy</div>
        </div>
        <div>
            <div style="font-size: 24px; color: #e94560;"><?php echo $stats['tiradas']; ?></div>
            <div style="color: #888;">Tiradas totales</div>
        </div>
        <div>
            <div style="font-size: 24px; color: #e94560;"><?php echo $stats['circuitos']; ?></div>
            <div style="color: #888;">Circuitos</div>
        </div>
        <div>
            <div style="font-size: 24px; color: #e94560;"><?php echo $stats['copas']; ?></div>
            <div style="color: #888;">Copas</div>
        </div>
    </div>
</div>

<?php
echo '</main></div>';
?>
</body>
</html>