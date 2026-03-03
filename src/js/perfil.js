/* ==========================================================================
   PERFIL APP - GESTIÓN DE PERFIL DE USUARIO
   ========================================================================== */

class PerfilApp {
    /* ========== 1. PROPIEDADES ESTÁTICAS ========== */
    static userId = null;
    static userName = null;
    static userEmail = null;
    static userRol = null;
    static fechaRegistro = null;
    static ultimaActividad = null;
    static estadisticas = [];
    static chart = null;
    
    /* ========== 2. INICIALIZACIÓN ========== */
    static init() {
        this.cargarDatosUsuario();
        this.cargarEstadisticas();
        this.cargarHistorial();
    }
    
    /* ========== 3. CARGAR DATOS DE USUARIO ========== */
    static cargarDatosUsuario() {
        fetch('../backend/api/get_usuario_actual.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.userId = data.usuario.id;
                    this.userName = data.usuario.nombre;
                    this.userEmail = data.usuario.email;
                    this.userRol = data.usuario.rol;
                    this.fechaRegistro = data.usuario.fecha_registro;
                    this.ultimaActividad = data.usuario.ultima_actividad;
                    
                    this.actualizarInfoUsuario();
                } else {
                    console.error('Error cargando usuario:', data.message);
                }
            })
            .catch(error => {
                console.error('Error cargando usuario:', error);
            });
    }
    
    /* ========== 4. ACTUALIZAR INFO DE USUARIO EN HTML ========== */
    static actualizarInfoUsuario() {
        document.getElementById('userName').textContent = this.userName || 'Usuario';
        document.getElementById('userEmail').textContent = this.userEmail || 'email@ejemplo.com';
        
        // Actualizar badge de rol
        const rolBadge = document.getElementById('rolBadge');
        if (rolBadge) {
            if (this.userRol === 'admin') {
                rolBadge.innerHTML = '<i class="fas fa-shield-alt me-1"></i> Administrador';
                rolBadge.className = 'badge bg-danger ms-2';
            } else {
                rolBadge.innerHTML = '<i class="fas fa-user me-1"></i> Usuario';
                rolBadge.className = 'badge bg-primary ms-2';
            }
        }
        
        // Actualizar fechas
        if (this.fechaRegistro) {
            const fecha = new Date(this.fechaRegistro);
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            document.getElementById('memberSince').textContent = 
                `${meses[fecha.getMonth()]} ${fecha.getFullYear()}`;
        }
        
        if (this.ultimaActividad) {
            const fecha = new Date(this.ultimaActividad);
            const hoy = new Date();
            if (fecha.toDateString() === hoy.toDateString()) {
                document.getElementById('lastActivity').textContent = 'Hoy';
            } else {
                document.getElementById('lastActivity').textContent = 
                    fecha.toLocaleDateString('es-ES');
            }
        }
    }
    
    /* ========== 5. CARGAR ESTADÍSTICAS ========== */
    static cargarEstadisticas() {
        fetch('../backend/api/get_estadisticas.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.estadisticas = data.estadisticas;
                    document.getElementById('vecesGirado').textContent = data.totales.veces_girado || 0;
                    this.crearGrafico();
                    this.mostrarTopCircuitos();
                } else {
                    console.error('Error cargando estadísticas:', data.message);
                }
            })
            .catch(error => {
                console.error('Error cargando estadísticas:', error);
            });
    }
    
    /* ========== 6. ACTUALIZAR ESTADÍSTICAS GENERALES ========== */
    static actualizarEstadisticas(totales) {
        // Actualizar solo "Veces Girado"
        document.querySelectorAll('.stat-item')[0].innerHTML = `
            <h2 class="fw-bold text-primary">${totales.veces_girado || 0}</h2>
            <small>Veces Girado</small>
        `;
    }
    
    /* ========== 7. CREAR GRÁFICO DE ESTADÍSTICAS ========== */
    static crearGrafico() {
        const ctx = document.getElementById('statsChart').getContext('2d');
        const noDataDiv = document.getElementById('chartNoData');
        
        // Filtrar solo circuitos con veces_ganador > 0
        const circuitosGanadores = this.estadisticas.filter(stat => stat.veces_ganador > 0);
        
        if (circuitosGanadores.length === 0) {
            document.getElementById('statsChart').style.display = 'none';
            if (noDataDiv) noDataDiv.style.display = 'block';
            return;
        }
        
        document.getElementById('statsChart').style.display = 'block';
        if (noDataDiv) noDataDiv.style.display = 'none';
        
        // Preparar datos para el gráfico (usando veces_ganador)
        const labels = circuitosGanadores.map(stat => 
            this.abreviarNombreCircuito(stat.circuito_nombre)
        );
        
        const data = circuitosGanadores.map(stat => stat.veces_ganador);
        
        // Colores degradados
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(233, 69, 96, 0.8)');
        gradient.addColorStop(1, 'rgba(78, 205, 196, 0.8)');
        
        // Destruir gráfico anterior si existe
        if (this.chart) {
            this.chart.destroy();
        }
        
        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Veces ganador',
                    data: data,
                    backgroundColor: gradient,
                    borderColor: 'rgba(233, 69, 96, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#fff'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#fff',
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return `Victorias: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    /* ========== 8. ABREVIAR NOMBRES DE CIRCUITOS ========== */
    static abreviarNombreCircuito(nombre) {
        if (!nombre) return '';
        
        const abreviaturas = {
            'Circuito Mario Bros.': 'Mario Bros.',
            'Ciudad Corona (1)': 'C. Corona 1',
            'Ciudad Corona (2)': 'C. Corona 2',
            'Cañón Ferroviario': 'Cañón Ferr.',
            'Puerto Espacial DK': 'Pto. DK',
            'Desierto Sol-Sol': 'Desierto',
            'Bazar Shy Guy': 'Bazar',
            'Estadio Wario': 'Est. Wario',
            'Fortaleza Aérea': 'Fortaleza',
            'DK Alpino': 'DK Alpino',
            'Mirador Estelar': 'Mirador',
            'Cielos Helados': 'Cielos',
            'Galeón de Wario': 'Galeón',
            'Playa Koopa': 'P. Koopa',
            'Sabana Salpicante': 'Sabana',
            'Estadio Peach (1)': 'Est. Peach 1',
            'Estadio Peach (2)': 'Est. Peach 2',
            'Playa Peach': 'P. Peach',
            'Ciudad Salina': 'C. Salina',
            'Jungla Dino Dino': 'Jungla',
            'Templo del Bloque ?': 'Templo ?',
            'Cascadas Cheep Cheep': 'Cascadas',
            'Gruta Diente de León': 'Gruta',
            'Cine Boo': 'Cine Boo',
            'Caverna Ósea': 'Caverna',
            'Pradera Mu-Mu': 'Pradera',
            'Monte Chocolate': 'M. Chocolate',
            'Fábrica de Toad': 'Fáb. Toad',
            'Castillo de Bowser': 'Castillo',
            'Aldea Arbórea': 'Aldea',
            'Circuito Mario': 'C. Mario',
            'Senda Arco Iris': 'Arco Iris'
        };
        
        return abreviaturas[nombre] || nombre.substring(0, 12) + '...';
    }
    
    /* ========== 9. MOSTRAR TOP 3 CIRCUITOS ========== */
    static mostrarTopCircuitos() {
        const container = document.getElementById('topCircuits');
        
        // Filtrar solo circuitos con veces_ganador > 0
        const circuitosGanadores = this.estadisticas.filter(stat => stat.veces_ganador > 0);
        
        if (circuitosGanadores.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay circuitos ganadores todavía</p>
                </div>
            `;
            return;
        }
        
        // Ordenar por veces_ganador y tomar top 3
        const top3 = [...circuitosGanadores]
            .sort((a, b) => b.veces_ganador - a.veces_ganador)
            .slice(0, 3);
        
        const medallas = ['🥇', '🥈', '🥉'];
        const colores = ['#FFD700', '#C0C0C0', '#CD7F32'];
        
        container.innerHTML = top3.map((circuito, index) => `
            <div class="col-md-4 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid ${colores[index]};">
                    <div class="card-body text-center">
                        <div class="display-4 mb-2" style="color: ${colores[index]};">${medallas[index]}</div>
                        <h5 class="fw-bold">${this.formatearNombreCircuito(circuito.circuito_nombre)}</h5>
                        <p class="text-muted mb-2">${circuito.copa_nombre || 'Copa'}</p>
                        <div class="mt-3">
                            <span class="badge bg-primary">🏆 ${circuito.veces_ganador} victorias</span>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    /* ========== 10. CARGAR HISTORIAL RECIENTE ========== */
    static cargarHistorial() {
        const tbody = document.getElementById('historyTable');
        
        fetch('../backend/api/get_historial.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.historial.length > 0) {
                    tbody.innerHTML = data.historial.map(item => `
                        <tr>
                            <td>${item.fecha}</td>
                            <td>
                                <div class="circuitos-lista">
                                    ${item.circuitos.map(circuito => `
                                        <span class="badge bg-secondary me-1">${circuito}</span>
                                    `).join('')}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-trophy me-1"></i> ${item.ganador}
                                </span>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay historial disponible</p>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error cargando historial:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <p class="text-muted">Error cargando historial</p>
                        </td>
                    </tr>
                `;
            });
    }
    
    /* ========== 11. FORMATEAR NOMBRES ========== */
    static formatearNombreCircuito(nombre) {
        if (!nombre) return '';
        const cambios = {
            'CanionFerroviario': 'Cañón Ferroviario',
            'Circuito Mario Bros.': 'Circuito Mario Bros.',
            'Ciudad Corona (1)': 'Ciudad Corona',
            'Ciudad Corona (2)': 'Ciudad Corona',
            'Estadio Peach (1)': 'Estadio Peach',
            'Estadio Peach (2)': 'Estadio Peach',
            'Templo del Bloque ?': 'Templo del Bloque ?',
            'Senda Arco Iris': 'Senda Arco Iris',
            'Puerto Espacial DK': 'Puerto Espacial DK'
        };
        return cambios[nombre] || nombre;
    }
}

/* ========== 12. INICIALIZAR AL CARGAR LA PÁGINA ========== */
document.addEventListener('DOMContentLoaded', function() {
    PerfilApp.init();
});