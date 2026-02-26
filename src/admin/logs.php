<?php
// admin/logs.php
require_once 'layout/sidebar.php';
require_once '../backend/includes/conexion.php';
require_once '../backend/includes/Logger.php';

// Verificar que es admin
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Obtener estadísticas de logs directamente
$log_stats = [
    'total' => 0,
    'por_tipo' => []
];

// Total de logs
$result = $enlace->query("SELECT COUNT(*) as total FROM logs_sistema");
if ($result) {
    $log_stats['total'] = $result->fetch_assoc()['total'];
}

// Logs por tipo
$result = $enlace->query("
    SELECT tipo, COUNT(*) as cantidad 
    FROM logs_sistema 
    GROUP BY tipo
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $log_stats['por_tipo'][$row['tipo']] = $row['cantidad'];
    }
}

// Obtener lista de usuarios para el filtro
$usuarios = $enlace->query("SELECT id, usuario FROM usuarios ORDER BY usuario");
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
        <div class="stat-title">ACCIONES</div>
        <div class="stat-number"><?php echo $log_stats['por_tipo']['ACCION'] ?? 0; ?></div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-bar">
    <select id="filtro-tipo">
        <option value="">Todos los tipos</option>
        <option value="INFO">INFO</option>
        <option value="WARNING">WARNING</option>
        <option value="ERROR">ERROR</option>
        <option value="ACCION">ACCIÓN</option>
    </select>
    
    <select id="filtro-usuario">
        <option value="">Todos los usuarios</option>
        <?php while($user = $usuarios->fetch_assoc()): ?>
        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['usuario']); ?></option>
        <?php endwhile; ?>
    </select>
    
    <input type="text" id="filtro-busqueda" placeholder="Buscar en descripción...">
    
    <input type="date" id="filtro-fecha-desde">
    
    <input type="date" id="filtro-fecha-hasta">
    
    <button onclick="cargarLogs()" class="btn btn-primary">Filtrar</button>
    <button onclick="limpiarFiltros()" class="btn btn-warning">Limpiar</button>
</div>

<!-- Tabla de logs -->
<div class="table-container">
    <div style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <h3>Registros del sistema</h3>
        <span id="log-count" style="color: #888;"></span>
    </div>
    
    <div id="logs-loading" style="text-align: center; padding: 50px; display: none;">
        Cargando logs...
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
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px;">
                    Usa los filtros para cargar los logs
                </td>
            </tr>
        </tbody>
    </table>
    
    <div style="padding: 20px; text-align: center;">
        <button onclick="cargarMas()" id="btn-cargar-mas" class="btn btn-primary" style="display: none;">
            Cargar más
        </button>
    </div>
</div>

<script>
let offset = 0;
const limite = 50;
let cargando = false;
let totalLogs = 0;

function cargarLogs(reset = true) {
    if (cargando) return;
    
    if (reset) {
        offset = 0;
        document.getElementById('logs-tbody').innerHTML = '';
    }
    
    cargando = true;
    document.getElementById('logs-loading').style.display = 'block';
    
    const params = new URLSearchParams({
        tipo: document.getElementById('filtro-tipo').value,
        usuario_id: document.getElementById('filtro-usuario').value,
        busqueda: document.getElementById('filtro-busqueda').value,
        fecha_desde: document.getElementById('filtro-fecha-desde').value,
        fecha_hasta: document.getElementById('filtro-fecha-hasta').value,
        limite: limite,
        offset: offset
    });
    
    fetch('../backend/api/get_logs.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            cargando = false;
            document.getElementById('logs-loading').style.display = 'none';
            
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    mostrarLogs(data.data);
                    offset += data.data.length;
                    totalLogs = data.total || 0;
                    
                    document.getElementById('log-count').textContent = `Mostrando ${offset} de ${totalLogs} logs`;
                    
                    if (data.data.length === limite) {
                        document.getElementById('btn-cargar-mas').style.display = 'inline-block';
                    } else {
                        document.getElementById('btn-cargar-mas').style.display = 'none';
                    }
                } else if (reset) {
                    document.getElementById('logs-tbody').innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">
                                No hay logs para mostrar
                            </td>
                        </tr>
                    `;
                    document.getElementById('log-count').textContent = 'Total: 0 logs';
                    document.getElementById('btn-cargar-mas').style.display = 'none';
                }
            }
        })
        .catch(error => {
            cargando = false;
            document.getElementById('logs-loading').style.display = 'none';
            console.error('Error:', error);
            document.getElementById('logs-tbody').innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #f48771;">
                        Error al cargar los logs: ${error.message}
                    </td>
                </tr>
            `;
        });
}

function mostrarLogs(logs) {
    const tbody = document.getElementById('logs-tbody');
    
    logs.forEach(log => {
        const fecha = new Date(log.fecha).toLocaleString('es-ES');
        const tipoClass = 'log-' + (log.tipo || 'info').toLowerCase();
        
        // Detectar dispositivo por user agent
        let dispositivo = 'Desconocido';
        if (log.user_agent) {
            if (log.user_agent.includes('Mobile')) dispositivo = '📱 Móvil';
            else if (log.user_agent.includes('Tablet')) dispositivo = '📱 Tablet';
            else dispositivo = '💻 Escritorio';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${fecha}</td>
            <td>${log.usuario_nombre || 'Sistema'}</td>
            <td><span class="log-badge ${tipoClass}">${log.tipo || 'INFO'}</span></td>
            <td>${log.accion || '-'}</td>
            <td>${log.descripcion || ''}</td>
            <td><span class="log-ip">${log.ip_address || '0.0.0.0'}</span></td>
            <td>${dispositivo}</td>
        `;
        tbody.appendChild(row);
    });
}

function cargarMas() {
    cargarLogs(false);
}

function limpiarFiltros() {
    document.getElementById('filtro-tipo').value = '';
    document.getElementById('filtro-usuario').value = '';
    document.getElementById('filtro-busqueda').value = '';
    document.getElementById('filtro-fecha-desde').value = '';
    document.getElementById('filtro-fecha-hasta').value = '';
    cargarLogs();
}

// Cargar logs iniciales
cargarLogs();

setInterval(() => cargarLogs(true), 30000);
</script>

<?php
echo '</main></div>';
?>
</body>
</html>