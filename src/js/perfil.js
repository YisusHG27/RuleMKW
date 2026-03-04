/* ==========================================================================
   PERFIL APP - GESTIÓN DE PERFIL DE USUARIO
   ========================================================================== */

   class PerfilApp {
    /* ========== 1. PROPIEDADES ESTÁTICAS ========== */
    static userId = null;
    static userName = null;
    static userEmail = null;
    static userFoto = null;
    static userRol = null;
    static fechaRegistro = null;
    static ultimaActividad = null;
    static estadisticas = [];
    static chart = null;
    
    /* ========== 2. VARIABLES DE PAGINACIÓN ========== */
    static historialCompleto = [];
    static paginaActual = 1;
    static itemsPorPagina = 5;
    
    /* ========== 3. INICIALIZACIÓN ========== */
    static init() {
        this.cargarDatosUsuario();
        this.cargarEstadisticas();
        this.cargarHistorial();
        this.initFotoPerfil();
        this.setupEventListeners();
    }
    
    /* ========== 4. CARGAR DATOS DE USUARIO ========== */
    static cargarDatosUsuario() {
        fetch('../backend/api/get_usuario_actual.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.userId = data.usuario.id;
                    this.userName = data.usuario.nombre;
                    this.userEmail = data.usuario.email;
                    this.userFoto = data.usuario.foto;
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
    
    /* ========== 5. ACTUALIZAR INFO DE USUARIO EN HTML ========== */
    static actualizarInfoUsuario() {
        document.getElementById('userName').textContent = this.userName || 'Usuario';
        document.getElementById('userEmail').textContent = this.userEmail || 'email@ejemplo.com';
        
        // Actualizar foto de perfil o mostrar inicial
        const avatarImg = document.getElementById('avatarImg');
        const avatarInicial = document.getElementById('avatarInicial');
        const avatar = document.querySelector('.avatar');
        
        if (this.userFoto && this.userFoto !== 'default.png' && this.userFoto !== 'null') {
            avatarImg.src = 'media/perfil/' + this.userFoto + '?t=' + new Date().getTime();
            avatarImg.style.display = 'block';
            if (avatarInicial) {
                avatarInicial.style.display = 'none';
            }
            if (avatar) {
                avatar.style.background = 'transparent';
            }
        } else {
            const inicial = this.userName ? this.userName.charAt(0).toUpperCase() : '?';
            if (avatarInicial) {
                avatarInicial.textContent = inicial;
                avatarInicial.style.display = 'flex';
            }
            avatarImg.style.display = 'none';
            if (avatar) {
                avatar.style.background = 'transparent';
            }
        }
        
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
    
    /* ========== 6. CARGAR ESTADÍSTICAS ========== */
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
    
    /* ========== 7. CREAR GRÁFICO DE ESTADÍSTICAS ========== */
    static crearGrafico() {
        const ctx = document.getElementById('statsChart').getContext('2d');
        const noDataDiv = document.getElementById('chartNoData');
        
        const circuitosGanadores = this.estadisticas.filter(stat => stat.veces_ganador > 0);
        
        if (circuitosGanadores.length === 0) {
            document.getElementById('statsChart').style.display = 'none';
            if (noDataDiv) noDataDiv.style.display = 'block';
            return;
        }
        
        document.getElementById('statsChart').style.display = 'block';
        if (noDataDiv) noDataDiv.style.display = 'none';
        
        const labels = circuitosGanadores.map(stat => 
            stat.circuito_nombre // Usar nombre completo sin abreviar
        );
        
        const data = circuitosGanadores.map(stat => stat.veces_ganador);
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(255, 107, 107, 0.8)');
        gradient.addColorStop(1, 'rgba(78, 205, 196, 0.8)');
        
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
                    borderColor: 'rgba(255, 107, 107, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 10,
                        bottom: 10
                    }
                },
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
                                size: 11,
                                weight: '500'
                            },
                            autoSkip: false,
                            maxRotation: 0,
                            minRotation: 0
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
                            title: (context) => context[0].label,
                            label: (context) => `Victorias: ${context.raw}`
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
        
        return abreviaturas[nombre] || nombre.substring(0, 12) + '…';
    }
    
    /* ========== 9. MOSTRAR TOP 3 CIRCUITOS ========== */
    static mostrarTopCircuitos() {
        const container = document.getElementById('topCircuits');
        
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
        
        const top3 = [...circuitosGanadores]
            .sort((a, b) => b.veces_ganador - a.veces_ganador)
            .slice(0, 3);
        
        const titulos = ['1º Más Jugado', '2º Más Jugado', '3º Más Jugado'];
        const coloresFondo = ['#FFD700', '#C0C0C0', '#CD7F32'];
        
        container.innerHTML = top3.map((circuito, index) => {
            const imageName = this.getCircuitoImageName(circuito.circuito_nombre);
            
            return `
                <div class="col-md-4 mb-3">
                    <div class="circuito-top-card" style="border-top: 4px solid ${coloresFondo[index]};">
                        <div class="top-badge" style="background: ${coloresFondo[index]};">
                            ${titulos[index]}
                        </div>
                        <div class="top-imagen-container">
                            <img src="media/circuitos/${imageName}.jpg" 
                                 alt="${circuito.circuito_nombre}"
                                 class="top-imagen"
                                 onerror="this.src='media/circuitos/default.jpg'">
                        </div>
                        <div class="top-info">
                            <h5 class="top-nombre">${this.formatearNombreCircuito(circuito.circuito_nombre)}</h5>
                            <p class="top-copa">${circuito.copa_nombre || 'Copa'}</p>
                            <div class="top-victorias">
                                <span class="badge-victorias">${circuito.veces_ganador} victorias</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    /* ========== 10. CARGAR HISTORIAL RECIENTE CON PAGINACIÓN ========== */
    static cargarHistorial() {
        fetch('../backend/api/get_historial.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.historial.length > 0) {
                    this.historialCompleto = data.historial;
                    this.paginaActual = 1;
                    this.mostrarPaginaHistorial();
                    this.actualizarBotonesPaginacion();
                } else {
                    this.mostrarHistorialVacio();
                }
            })
            .catch(error => {
                console.error('Error cargando historial:', error);
                this.mostrarHistorialVacio();
            });
    }
    
    /* ========== 10.1. MOSTRAR PÁGINA DE HISTORIAL ========== */
    static mostrarPaginaHistorial() {
        const tbody = document.getElementById('historyTable');
        const inicio = (this.paginaActual - 1) * this.itemsPorPagina;
        const fin = inicio + this.itemsPorPagina;
        const paginacion = this.historialCompleto.slice(inicio, fin);
        
        const filasRestantes = this.itemsPorPagina - paginacion.length;
        
        let html = paginacion.map(item => {
            // Tomar solo la fecha (primera parte) sin la hora
            const fechaSola = item.fecha.split(' ')[0]; // Esto toma solo "03/03/2026"
            
            return `
                <tr>
                    <td class="ps-3">${fechaSola}</td>
                    <td>
                        <div class="circuitos-lista">
                            ${item.circuitos.map(circuito => `
                                <span class="badge" style="background-color: #4ECDC4;" title="${circuito}">${circuito}</span>
                            `).join('')}
                        </div>
                    </td>
                    <td class="pe-3">
                        <span class="badge" style="background-color: #FFD166; color: #2D3047;" title="${item.ganador}">
                            <i class="fas fa-trophy me-1"></i> ${item.ganador}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Añadir filas vacías exactamente hasta completar 5 filas
        for (let i = 0; i < filasRestantes; i++) {
            html += `
                <tr class="empty-row">
                    <td class="ps-3">-</td>
                    <td>-</td>
                    <td class="pe-3">-</td>
                </tr>
            `;
        }
        
        tbody.innerHTML = html;
        
        const totalPaginas = Math.ceil(this.historialCompleto.length / this.itemsPorPagina);
        document.getElementById('pageInfo').textContent = `Página ${this.paginaActual} de ${totalPaginas}`;
    }
    
    /* ========== 10.2. MOSTRAR HISTORIAL VACÍO ========== */
    static mostrarHistorialVacio() {
        const tbody = document.getElementById('historyTable');
        
        let html = `
            <tr>
                <td colspan="3" class="text-center py-3">
                    <i class="fas fa-history fa-2x text-muted mb-1"></i>
                    <p class="text-muted small mb-0">No hay historial disponible</p>
                </td>
            </tr>
        `;
        
        for (let i = 0; i < 4; i++) {
            html += `
                <tr class="empty-row">
                    <td class="ps-3">-</td>
                    <td>-</td>
                    <td class="pe-3">-</td>
                </tr>
            `;
        }
        
        tbody.innerHTML = html;
        
        document.getElementById('pageInfo').textContent = 'Página 1';
        document.getElementById('prevPage').disabled = true;
        document.getElementById('nextPage').disabled = true;
    }
    
    /* ========== 10.3. ACTUALIZAR BOTONES DE PAGINACIÓN ========== */
    static actualizarBotonesPaginacion() {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const totalPaginas = Math.ceil(this.historialCompleto.length / this.itemsPorPagina);
        
        prevBtn.disabled = this.paginaActual === 1;
        nextBtn.disabled = this.paginaActual === totalPaginas || totalPaginas === 0;
    }
    
    /* ========== 10.4. IR A PÁGINA ANTERIOR ========== */
    static paginaAnterior() {
        if (this.paginaActual > 1) {
            this.paginaActual--;
            this.mostrarPaginaHistorial();
            this.actualizarBotonesPaginacion();
        }
    }
    
    /* ========== 10.5. IR A PÁGINA SIGUIENTE ========== */
    static paginaSiguiente() {
        const totalPaginas = Math.ceil(this.historialCompleto.length / this.itemsPorPagina);
        if (this.paginaActual < totalPaginas) {
            this.paginaActual++;
            this.mostrarPaginaHistorial();
            this.actualizarBotonesPaginacion();
        }
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
            'Puerto Espacial DK': 'Puerto Espacial DK',
            'Desierto Sol-Sol': 'Desierto Sol-Sol',
            'Bazar Shy Guy': 'Bazar Shy Guy',
            'Estadio Wario': 'Estadio Wario',
            'Fortaleza Aérea': 'Fortaleza Aérea',
            'DK Alpino': 'DK Alpino',
            'Mirador Estelar': 'Mirador Estelar',
            'Cielos Helados': 'Cielos Helados',
            'Galeón de Wario': 'Galeón de Wario',
            'Playa Koopa': 'Playa Koopa',
            'Sabana Salpicante': 'Sabana Salpicante',
            'Playa Peach': 'Playa Peach',
            'Ciudad Salina': 'Ciudad Salina',
            'Jungla Dino Dino': 'Jungla Dino Dino',
            'Cascadas Cheep Cheep': 'Cascadas Cheep Cheep',
            'Gruta Diente de León': 'Gruta Diente de León',
            'Cine Boo': 'Cine Boo',
            'Caverna Ósea': 'Caverna Ósea',
            'Pradera Mu-Mu': 'Pradera Mu-Mu',
            'Monte Chocolate': 'Monte Chocolate',
            'Fábrica de Toad': 'Fábrica de Toad',
            'Castillo de Bowser': 'Castillo de Bowser',
            'Aldea Arbórea': 'Aldea Arbórea',
            'Circuito Mario': 'Circuito Mario',
            'Senda Arco Iris': 'Senda Arco Iris'
        };
        return cambios[nombre] || nombre;
    }
    
    /* ========== 12. GESTIÓN DE FOTO DE PERFIL ========== */
    static initFotoPerfil() {
        const btnSeleccionar = document.getElementById('btnSeleccionarFoto');
        const inputFile = document.getElementById('fotoPerfilInput');
        const fotoInfo = document.getElementById('fotoSeleccionadaInfo');
        const nombreArchivo = document.getElementById('nombreArchivo');
        const btnGuardar = document.getElementById('btnGuardarFoto');
        const avatarImg = document.getElementById('avatarImg');
        const avatarInicial = document.getElementById('avatarInicial');
        const avatarBadge = document.getElementById('avatarBadge');

        if (!btnSeleccionar) return;

        btnSeleccionar.addEventListener('click', () => {
            inputFile.click();
        });

        inputFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                nombreArchivo.textContent = file.name;
                fotoInfo.style.display = 'block';
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    avatarImg.src = e.target.result;
                    avatarImg.style.display = 'block';
                    if (avatarInicial) avatarInicial.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        btnGuardar.addEventListener('click', () => {
            this.subirFotoPerfil();
        });
        
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.paginaAnterior());
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.paginaSiguiente());
        }
    }

    /* ========== 13. SUBIR FOTO DE PERFIL ========== */
    static async subirFotoPerfil() {
        const inputFile = document.getElementById('fotoPerfilInput');
        const btnGuardar = document.getElementById('btnGuardarFoto');
        const fotoInfo = document.getElementById('fotoSeleccionadaInfo');
        const avatarBadge = document.getElementById('avatarBadge');
        
        if (!inputFile.files.length) {
            this.showAlert('Selecciona una imagen primero', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('foto', inputFile.files[0]);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Subiendo...';

        try {
            const response = await fetch('../backend/api/subir_foto_perfil.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Foto actualizada correctamente', 'success');
                fotoInfo.style.display = 'none';
                inputFile.value = '';
                
                avatarBadge.style.display = 'flex';
                setTimeout(() => {
                    avatarBadge.style.display = 'none';
                }, 3000);
                
                const avatarImg = document.getElementById('avatarImg');
                const avatarInicial = document.getElementById('avatarInicial');
                
                avatarImg.src = 'media/perfil/' + data.foto + '?t=' + new Date().getTime();
                avatarImg.style.display = 'block';
                if (avatarInicial) avatarInicial.style.display = 'none';
                
                if (window.actualizarFotoNavbar) {
                    window.actualizarFotoNavbar(data.foto);
                }
            } else {
                this.showAlert(data.message || 'Error al subir la foto', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión', 'error');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i> Guardar';
        }
    }

    /* ========== 14. MOSTRAR ALERTAS ========== */
    static showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer') || this.crearAlertContainer();
        
        const alertId = 'alert-' + Date.now();
        const icons = {
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'error': 'times-circle',
            'info': 'info-circle'
        };
        
        const alertHTML = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icons[type] || 'info-circle'} me-3"></i>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHTML;
        
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) alert.remove();
        }, 5000);
    }

    static crearAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
    
    /* ========== 15. OBTENER NOMBRE DE IMAGEN DE CIRCUITO ========== */
    static getCircuitoImageName(nombre) {
        if (!nombre) return 'default';
        
        const mapping = {
            'Circuito Mario Bros.': 'CircuitoMarioBros',
            'Ciudad Corona (1)': 'CiudadCorona1',
            'Ciudad Corona (2)': 'CiudadCorona2',
            'Cañón Ferroviario': 'CanFerroviario',
            'Puerto Espacial DK': 'PuertoEspacialDK',
            'Desierto Sol-Sol': 'DesiertoSolSol',
            'Bazar Shy Guy': 'BazarShyGuy',
            'Estadio Wario': 'EstadioWario',
            'Fortaleza Aérea': 'FortalezaArea',
            'DK Alpino': 'DKAlpino',
            'Mirador Estelar': 'MiradorEstelar',
            'Cielos Helados': 'CielosHelados',
            'Galeón de Wario': 'GaleondeWario',
            'Playa Koopa': 'PlayaKoopa',
            'Sabana Salpicante': 'SabanaSalpicante',
            'Estadio Peach (1)': 'EstadioPeach1',
            'Estadio Peach (2)': 'EstadioPeach2',
            'Playa Peach': 'PlayaPeach',
            'Ciudad Salina': 'CiudadSalina',
            'Jungla Dino Dino': 'JunglaDinoDino',
            'Templo del Bloque ?': 'TemplodelBloque',
            'Cascadas Cheep Cheep': 'CascadasCheepCheep',
            'Gruta Diente de León': 'GrutaDientedeLeon',
            'Cine Boo': 'CineBoo',
            'Caverna Ósea': 'CavernaOsea',
            'Pradera Mu-Mu': 'PraderaMuMu',
            'Monte Chocolate': 'MonteChocolate',
            'Fábrica de Toad': 'FabricadeToad',
            'Castillo de Bowser': 'CastillodeBowser',
            'Aldea Arbórea': 'AldeaArbrea',
            'Circuito Mario': 'CircuitoMario',
            'Senda Arco Iris': 'SendaArcoIris'
        };
        
        return mapping[nombre] || nombre.replace(/\s+/g, '');
    }
    
    /* ========== 16. SETUP EVENT LISTENERS ========== */
    static setupEventListeners() {
        // Listeners ya configurados en initFotoPerfil
    }
}

/* ========== 17. INICIALIZAR AL CARGAR LA PÁGINA ========== */
document.addEventListener('DOMContentLoaded', function() {
    PerfilApp.init();
});