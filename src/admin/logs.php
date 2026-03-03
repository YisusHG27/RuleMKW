<?php
// admin/logs.php
require_once 'layout/sidebar.php';
require_once '../backend/includes/conexion.php';

// Verificar que es admin
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Configuración - RUTA CORREGIDA
$logsDir = __DIR__ . '/../logs/';
$limite = 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$filtroTipo = $_GET['tipo'] ?? '';
$filtroUsuario = $_GET['usuario_id'] ?? '';
$filtroBusqueda = $_GET['busqueda'] ?? '';
$filtroFecha = $_GET['fecha'] ?? ''; // NUEVO: filtro de fecha única

// Obtener lista de usuarios para el filtro
$usuarios = $enlace->query("SELECT id, usuario FROM usuarios ORDER BY usuario");

// Función para parsear una línea de log
function parsearLogLinea($linea) {
    // Formato: [2026-02-26 01:13:48] rulemkw.INFO: Login exitoso {"usuario_id":1,...}
    $patron = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*?)(\s+\{.*\})?$/';
    
    if (preg_match($patron, $linea, $matches)) {
        $fecha = $matches[1];
        $tipo = $matches[2];
        $mensajeCompleto = $matches[3];
        $contextoJson = isset($matches[4]) ? trim($matches[4]) : '';
        $contexto = !empty($contextoJson) ? json_decode($contextoJson, true) : [];
        
        // Determinar acción
        $accion = 'OTRO';
        if (stripos($mensajeCompleto, 'login') !== false) $accion = 'LOGIN';
        else if (stripos($mensajeCompleto, 'sesión') !== false || stripos($mensajeCompleto, 'logout') !== false) $accion = 'LOGOUT';
        else if (stripos($mensajeCompleto, 'registro') !== false) $accion = 'REGISTRO';
        else if (stripos($mensajeCompleto, 'usuario') !== false) $accion = 'USER';
        else if (stripos($mensajeCompleto, 'dashboard') !== false) $accion = 'DASHBOARD';
        else if (stripos($mensajeCompleto, 'estadísticas') !== false) $accion = 'ESTADISTICAS';
        else if (stripos($mensajeCompleto, 'error') !== false) $accion = 'ERROR';
        else if (stripos($mensajeCompleto, 'cookie') !== false) $accion = 'COOKIES';
        
        // Obtener nombre de usuario
        $usuario_nombre = 'Sistema';
        if (isset($contexto['usuario'])) {
            $usuario_nombre = $contexto['usuario'];
        } elseif (isset($contexto['user'])) {
            $usuario_nombre = $contexto['user'];
        } elseif ($accion === 'COOKIES') {
            $usuario_nombre = 'Visitante';
        }
        
        return [
            'fecha' => $fecha,
            'tipo' => $tipo,
            'accion' => $accion,
            'descripcion' => $mensajeCompleto,
            'usuario_nombre' => $usuario_nombre,
            'usuario_id' => $contexto['usuario_id'] ?? null,
            'ip_address' => $contexto['ip'] ?? '0.0.0.0',
            'user_agent' => $contexto['user_agent'] ?? '',
            'screen_resolution' => $contexto['screen_resolution'] ?? '',
            'language' => $contexto['language'] ?? '',
            'contexto' => $contexto
        ];
    }
    return null;
}

// Función para leer logs del archivo
function leerLogsDeArchivo($logsDir, $filtroFecha, $filtroTipo, $filtroBusqueda, $filtroUsuario, $offset, $limite) {
    $logs = [];
    $todasLasLineas = [];
    
    // Determinar qué archivo(s) cargar
    $archivosACargar = [];
    
    if (!empty($filtroFecha)) {
        // Si hay filtro de fecha, cargar solo ese archivo
        $archivoFecha = $logsDir . 'rulemkw-' . $filtroFecha . '.log';
        if (file_exists($archivoFecha)) {
            $archivosACargar[] = $archivoFecha;
        }
    } else {
        // Si no hay filtro, cargar todos los archivos
        $archivosACargar = glob($logsDir . 'rulemkw-*.log');
    }
    
    // Si no hay archivos, retornar vacío
    if (empty($archivosACargar)) {
        return ['logs' => [], 'total' => 0];
    }
    
    // Procesar cada archivo
    foreach ($archivosACargar as $archivo) {
        if (!file_exists($archivo)) continue;
        
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lineas as $linea) {
            $log = parsearLogLinea($linea);
            if (!$log) continue;
            
            // Aplicar filtros
            if (!empty($filtroTipo) && $log['tipo'] !== $filtroTipo) continue;
            
            if (!empty($filtroBusqueda)) {
                $textoBusqueda = strtolower($log['descripcion'] . ' ' . $log['accion']);
                if (strpos($textoBusqueda, strtolower($filtroBusqueda)) === false) continue;
            }
            
            // Filtro por usuario
            if (!empty($filtroUsuario) && isset($log['contexto']['usuario_id']) && $log['contexto']['usuario_id'] != $filtroUsuario) continue;
            
            $todasLasLineas[] = $log;
        }
    }
    
    // Ordenar por fecha (más reciente primero)
    usort($todasLasLineas, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    
    $total = count($todasLasLineas);
    $logs = array_slice($todasLasLineas, $offset, $limite);
    
    return [
        'logs' => $logs,
        'total' => $total
    ];
}

// Función para obtener estadísticas
function obtenerEstadisticasLogs($logsDir, $filtroFecha = '') {
    $stats = [
        'total' => 0,
        'por_tipo' => ['INFO' => 0, 'WARNING' => 0, 'ERROR' => 0],
        'por_accion' => ['LOGIN' => 0, 'LOGOUT' => 0, 'REGISTRO' => 0, 'USER' => 0, 'DASHBOARD' => 0, 'ESTADISTICAS' => 0, 'ERROR' => 0, 'COOKIES' => 0, 'OTRO' => 0]
    ];
    
    // Determinar qué archivos procesar
    $archivosAProcesar = [];
    
    if (!empty($filtroFecha)) {
        $archivoFecha = $logsDir . 'rulemkw-' . $filtroFecha . '.log';
        if (file_exists($archivoFecha)) {
            $archivosAProcesar[] = $archivoFecha;
        }
    } else {
        $archivosAProcesar = glob($logsDir . 'rulemkw-*.log');
    }
    
    foreach ($archivosAProcesar as $archivo) {
        if (!file_exists($archivo)) continue;
        
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            $log = parsearLogLinea($linea);
            if ($log) {
                $stats['total']++;
                if (isset($stats['por_tipo'][$log['tipo']])) {
                    $stats['por_tipo'][$log['tipo']]++;
                }
                if (isset($stats['por_accion'][$log['accion']])) {
                    $stats['por_accion'][$log['accion']]++;
                }
            }
        }
    }
    
    return $stats;
}

// Cargar logs
$logsData = leerLogsDeArchivo($logsDir, $filtroFecha, $filtroTipo, $filtroBusqueda, $filtroUsuario, $offset, $limite);

// Obtener estadísticas
$log_stats = obtenerEstadisticasLogs($logsDir, $filtroFecha);
?>

<div class="content-header">
    <h1>Logs del Sistema</h1>
    <div class="breadcrumb">Administración / Logs</div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-title">Total Logs</div>
        <div class="stat-number"><?php echo $log_stats['total'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">INFO</div>
        <div class="stat-number"><?php echo $log_stats['por_tipo']['INFO'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">WARNING</div>
        <div class="stat-number"><?php echo $log_stats['por_tipo']['WARNING'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">ERROR</div>
        <div class="stat-number"><?php echo $log_stats['por_tipo']['ERROR'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">COOKIES</div>
        <div class="stat-number"><?php echo $log_stats['por_accion']['COOKIES'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">ACCIONES</div>
        <div class="stat-number"><?php echo $logsData['total']; ?></div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-bar">
    <form method="GET" id="filtros-form" style="display: flex; flex-wrap: wrap; gap: 10px; width: 100%;">
        <select name="tipo" style="padding: 8px; background: #16213e; color: white; border: 1px solid #e94560; border-radius: 4px;">
            <option value="">Todos los tipos</option>
            <option value="INFO" <?php echo $filtroTipo == 'INFO' ? 'selected' : ''; ?>>INFO</option>
            <option value="WARNING" <?php echo $filtroTipo == 'WARNING' ? 'selected' : ''; ?>>WARNING</option>
            <option value="ERROR" <?php echo $filtroTipo == 'ERROR' ? 'selected' : ''; ?>>ERROR</option>
        </select>
        
        <select name="usuario_id" style="padding: 8px; background: #16213e; color: white; border: 1px solid #e94560; border-radius: 4px;">
            <option value="">Todos los usuarios</option>
            <?php 
            if ($usuarios) {
                while($user = $usuarios->fetch_assoc()): 
            ?>
            <option value="<?php echo $user['id']; ?>" <?php echo $filtroUsuario == $user['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($user['usuario']); ?>
            </option>
            <?php 
                endwhile;
            } 
            ?>
        </select>
        
        <input type="text" name="busqueda" value="<?php echo htmlspecialchars($filtroBusqueda); ?>" 
               placeholder="Buscar en descripción..." 
               style="padding: 8px; background: #16213e; color: white; border: 1px solid #e94560; border-radius: 4px; flex: 1;">
        
        <!-- FILTRO DE FECHA ÚNICA -->
        <input type="date" name="fecha" value="<?php echo htmlspecialchars($filtroFecha); ?>" 
               style="padding: 8px; background: #16213e; color: white; border: 1px solid #e94560; border-radius: 4px;">
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="logs.php" class="btn btn-warning">Limpiar</a>
    </form>
</div>

<!-- Tabla de logs -->
<div class="table-container">
    <div style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <h3>Registros del sistema</h3>
        <span style="color: #888;">
            Mostrando <?php echo count($logsData['logs']); ?> de <?php echo $logsData['total']; ?> logs
            <?php if (!empty($filtroFecha)): ?>
            | Fecha: <?php echo $filtroFecha; ?>
            <?php endif; ?>
        </span>
    </div>
    
    <table class="data-table" id="logs-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>IP</th>
                <th>Dispositivo</th>
            </tr>
        </thead>
        <tbody id="logs-tbody">
            <?php if (empty($logsData['logs'])): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #888;">
                    No hay logs para mostrar con los filtros actuales
                </td>
            </tr>
            <?php else: ?>
                <?php foreach($logsData['logs'] as $log): ?>
                <tr>
                    <td><?php echo $log['fecha']; ?></td>
                    <td><?php echo htmlspecialchars($log['usuario_nombre']); ?></td>
                    <td>
                        <?php 
                        $badgeClass = 'log-' . strtolower($log['tipo']);
                        if ($log['accion'] === 'COOKIES') {
                            $badgeClass = 'log-cookie';
                        }
                        ?>
                        <span class="log-badge <?php echo $badgeClass; ?>">
                            <?php echo $log['tipo']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="log-accion log-accion-<?php echo strtolower($log['accion']); ?>">
                            <?php echo $log['accion']; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($log['descripcion']); ?></td>
                    <td><span class="log-ip"><?php echo $log['ip_address']; ?></span></td>
                    <td>
                        <?php
                        $userAgent = $log['user_agent'] ?? '';
                        if (strpos($userAgent, 'Mobile') !== false) echo '📱 Móvil';
                        elseif (strpos($userAgent, 'Tablet') !== false) echo '📱 Tablet';
                        elseif (!empty($userAgent)) echo '💻 PC / Laptop';
                        else echo '❓ Desconocido';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($logsData['total'] > $limite): ?>
    <div style="padding: 20px; text-align: center;">
        <?php if ($offset > 0): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['offset' => max(0, $offset - $limite)])); ?>" 
           class="btn btn-primary" style="margin-right: 10px;">◀ Anterior</a>
        <?php endif; ?>
        
        <span style="margin: 0 15px; color: #888;">
            Página <?php echo floor($offset / $limite) + 1; ?> de <?php echo ceil($logsData['total'] / $limite); ?>
        </span>
        
        <?php if ($offset + $limite < $logsData['total']): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['offset' => $offset + $limite])); ?>" 
           class="btn btn-primary" style="margin-left: 10px;">Siguiente ▶</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
echo '</main></div>';
?>
</body>
</html>