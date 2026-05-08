/* ==========================================================================
   CIRCUITOSAPP - GESTIÓN DE CIRCUITOS Y SELECCIÓN
   ========================================================================== */

   class CircuitosApp {
    /* ========== 1. PROPIEDADES ESTÁTICAS ========== */
    static selectedCircuits = [];      // Circuitos seleccionados actualmente
    static maxSelections = 4;          // Máximo de circuitos permitidos
    static minSelections = 2;          // Mínimo de circuitos requeridos
    static isLoggedIn = false;          // Estado de autenticación
    static userId = null;               // ID del usuario logueado
    
    /* ========== 2. INICIALIZACIÓN ========== */
    static init() {
        // ===== 2.1. VERIFICAR AUTENTICACIÓN DEL USUARIO =====
        this.checkSession();
        
        // ===== 2.2. CARGAR LISTADO DE CIRCUITOS =====
        this.loadCircuits();
        
        // ===== 2.3. CONFIGURAR EVENT LISTENERS =====
        this.setupEventListeners();
        
        // ===== 2.4. INICIALIZAR RULETA SI EXISTE =====
        if (window.RuletaApp && typeof window.RuletaApp.init === 'function') {
            window.RuletaApp.init();
        }
    }
    
    /* ========== 3. GESTIÓN DE SESIÓN ========== */
    /* ===== 3.1. VERIFICAR SESIÓN LOCAL =====*/
    static checkSession() {
        // Por defecto, asumimos que no está logueado
        this.isLoggedIn = false;
        this.userId = null;
        
        // ===== 3.1.1. BUSCAR COOKIE DE SESIÓN PHP =====
        const cookies = document.cookie.split(';');
        const hasPHPSession = cookies.some(cookie => 
            cookie.trim().startsWith('PHPSESSID=')
        );
        
        // ===== 3.1.2. VERIFICAR EN BACKEND SI COOKIE EXISTE =====
        if (hasPHPSession) {
            // Si hay cookie, verificamos con el backend para confirmar sesión válida
            this.verificarSesionBackend();
        } else {
            console.log('No hay cookie de sesión');
        }
    }

    /* ===== 3.2. VERIFICAR SESIÓN EN EL BACKEND ===== */
    static async verificarSesionBackend() {
        // ===== 3.2.1. ENVIAR PETICIÓN AL BACKEND =====
        try {
            const response = await fetch('../backend/api/procesar_session.php');
            const data = await response.json();
            
            // ===== 3.2.2. PROCESAR RESPUESTA =====
            if (data.logged_in) {
                // Usuario está logueado
                this.isLoggedIn = true;
                this.userId = data.user_id;
                console.log('Usuario logueado:', data.user_name);
            } else {
                // Usuario no está logueado
                this.isLoggedIn = false;
                this.userId = null;
                console.log('Usuario no logueado');
            }
        } catch (error) {
            // Manejo de errores de conexión
            console.error('Error verificando sesión:', error);
            this.isLoggedIn = false;
            this.userId = null;
        }
    }
    
    /* ========== 4. CARGA DE DATOS ========== */
    static async loadCircuits() {
        // ===== 4.1. ENVIAR PETICIÓN AL BACKEND =====
        try {
            const response = await fetch('../backend/api/get_circuitos.php');
            const data = await response.json();
            
            // ===== 4.2. PROCESAR RESPUESTA =====
            if (data.error) {
                console.error('Error cargando circuitos:', data.error);
            } else {
                // Renderizar copas con sus circuitos
                this.renderCopas(data);
            }
        } catch (error) {
            console.error('Error de conexión:', error);
        }
    }
    
    /* ========== 5. EVENT LISTENERS PRINCIPALES ========== */
    static setupEventListeners() {
        // ===== 5.1. BOTÓN REINICIAR SELECCIÓN =====
        console.log('Configurando event listeners generales');
        
        const btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', () => {
                this.clearSelectedCircuits();
            });
        }
        
        // ===== 5.2. BOTÓN GIRAR RULETA =====
        const btnGirar = document.getElementById('btnGirar');
        if (btnGirar) {
            btnGirar.addEventListener('click', () => {
                if (window.RuletaApp && typeof window.RuletaApp.girarRuleta === 'function') {
                    window.RuletaApp.girarRuleta();
                }
            });
        }
        
        // ===== 5.3. ACTUALIZAR ESTADO INICIAL DEL BOTÓN GIRAR =====
        this.updateGirarButtonState();
    }
    
    /* ========== 6. LISTENERS DE CIRCUITOS ========== */
    static setupCircuitosListeners() {
        // ===== 6.1. LOGUEAR CONFIGURACIÓN =====
        console.log('Configurando listeners de circuitos');
        
        // ===== 6.2. ITERAR SOBRE TODOS LOS SELECTORES DE CIRCUITOS =====
        document.querySelectorAll('.circuito-selector').forEach(selector => {
            selector.addEventListener('click', (e) => {
                // ===== 6.2.1. EVITAR PROPAGACIÓN SI SE CLICKEA EL OVERLAY =====
                if (e.target.closest('.circuito-overlay')) {
                    return;
                }
                
                // ===== 6.2.2. OBTENER DATOS DEL CIRCUITO DESDE ATRIBUTOS =====
                const circuitId = parseInt(selector.dataset.circuitId);
                const circuitName = selector.dataset.circuitName;
                const circuitCopa = selector.dataset.circuitCopa;
                
                // ===== 6.2.3. INVOCAR FUNCIÓN DE SELECCIÓN/DESELECCIÓN =====
                this.toggleCircuitSelection({
                    id: circuitId,
                    nombre: circuitName,
                    copa_nombre: circuitCopa
                }, selector);
            });
        });
    }
    
    /* ========== 7. GESTIÓN DE SELECCIÓN ========== */
    static toggleCircuitSelection(circuito, elemento) {
        // ===== 7.1. BUSCAR SI EL CIRCUITO YA ESTÁ SELECCIONADO =====
        const index = this.selectedCircuits.findIndex(c => c.id === circuito.id);
        
        if (index === -1) {
            // ===== 7.2. AGREGAR CIRCUITO SI NO ESTÁ SELECCIONADO =====
            if (this.selectedCircuits.length < this.maxSelections) {
                // Añadir circuito al array
                this.selectedCircuits.push(circuito);
                // Marcar elemento como seleccionado visualmente
                elemento.classList.add('selected');
                this.showAlert(`"${this.formatCircuitoNombre(circuito.nombre)}" añadido a la ruleta`, 'success');
            } else {
                // Mostrar alerta de límite máximo
                this.showAlert(`Máximo ${this.maxSelections} circuitos en la ruleta`, 'warning');
                return;
            }
        } else {
            // ===== 7.3. REMOVER CIRCUITO SI YA ESTÁ SELECCIONADO =====
            this.selectedCircuits.splice(index, 1);
            elemento.classList.remove('selected');
            this.showAlert(`"${this.formatCircuitoNombre(circuito.nombre)}" eliminado de la ruleta`, 'info');
        }
        
        // ===== 7.4. ACTUALIZAR INTERFAZ =====
        this.updateSelectedCounter();
        this.updateGirarButtonState();
        
        // ===== 7.5. ACTUALIZAR LA RULETA CON LOS CIRCUITOS SELECCIONADOS =====
        if (window.RuletaApp && typeof window.RuletaApp.actualizarRuletaConCircuitos === 'function') {
            window.RuletaApp.actualizarRuletaConCircuitos(this.selectedCircuits);
        }
    }
    
    /* ========== 8. ACTUALIZACIÓN DE UI ========== */
    /* ===== 8.1. ACTUALIZAR CONTADOR DE SELECCIONADOS ===== */
    static updateSelectedCounter() {
        // ===== 8.1.1. OBTENER ELEMENTOS DE LA INTERFAZ =====
        const contadorTexto = document.getElementById('contadorTexto');
        const progressBar = document.getElementById('progressBar');
        const selectedCount = document.getElementById('selectedCount');
        
        // ===== 8.1.2. ACTUALIZAR TEXTO DEL CONTADOR =====
        if (contadorTexto) {
            contadorTexto.textContent = `${this.selectedCircuits.length}/${this.maxSelections} circuitos en la ruleta`;
        }
        
        // ===== 8.1.3. ACTUALIZAR BARRA DE PROGRESO =====
        if (progressBar) {
            const porcentaje = (this.selectedCircuits.length / this.maxSelections) * 100;
            progressBar.style.width = `${porcentaje}%`;
        }
        
        // ===== 8.1.4. ACTUALIZAR NÚMERO DE SELECCIONADOS =====
        if (selectedCount) {
            selectedCount.textContent = this.selectedCircuits.length;
        }
    }
    
    /* ===== 8.2. ACTUALIZAR ESTADO DEL BOTÓN GIRAR ===== */
    static updateGirarButtonState() {
        const btnGirar = document.getElementById('btnGirar');
        if (btnGirar) {
            // ===== 8.2.1. VALIDAR CANTIDAD DE CIRCUITOS SELECCIONADOS =====
            const isValid = this.selectedCircuits.length >= this.minSelections && 
                           this.selectedCircuits.length <= this.maxSelections;
            
            // ===== 8.2.2. HABILITAR/DESHABILITAR BOTÓN =====
            btnGirar.disabled = !isValid;
            
            // ===== 8.2.3. ESTABLECER TOOLTIP CON MENSAJE APROPIADO =====
            if (!isValid) {
                btnGirar.title = `Selecciona entre ${this.minSelections} y ${this.maxSelections} circuitos`;
            } else {
                btnGirar.title = '¡Girar ruleta!';
            }
        }
    }
    
    /* ===== 8.3. LIMPIAR SELECCIONES =====*/
    static clearSelectedCircuits() {
        // ===== 8.3.1. REMOVER CLASE SELECTED DE TODOS LOS ELEMENTOS =====
        document.querySelectorAll('.circuito-selector.selected').forEach(el => {
            el.classList.remove('selected');
        });
        
        // ===== 8.3.2. VACIAR ARRAY DE SELECCIONADOS =====
        this.selectedCircuits = [];
        
        // ===== 8.3.3. ACTUALIZAR INTERFAZ =====
        this.updateSelectedCounter();
        this.updateGirarButtonState();
        
        // ===== 8.3.4. LIMPIAR LA RULETA =====
        if (window.RuletaApp && typeof window.RuletaApp.actualizarRuletaConCircuitos === 'function') {
            window.RuletaApp.actualizarRuletaConCircuitos([]);
        }
        
        this.showAlert('Selección reiniciada', 'info');
    }
    
    /* ========== 9. RENDERIZADO DE COPAS Y CIRCUITOS ========== */
    /* ===== 9.1. RENDERIZAR TODAS LAS COPAS ===== */
    static renderCopas(copasData) {
        // ===== 9.1.1. OBTENER ACORDEÓN Y LIMPIARLO =====
        const accordion = document.getElementById('copasAccordion');
        accordion.innerHTML = '';
        
        // ===== 9.1.2. VALIDAR QUE HAYA DATOS =====
        if (!copasData || copasData.length === 0) {
            accordion.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No se pudieron cargar los circuitos. Intenta recargar la página.
                </div>
            `;
            return;
        }
        
        // ===== 9.1.3. ITERAR SOBRE CADA COPA =====
        copasData.forEach((copa, index) => {
            const copaId = `copa${copa.id}`;
            const isFirst = index === 0; // Primera copa comienza expandida
            
            // ===== 9.1.4. CONSTRUIR HTML DE LA COPA =====
            const copaHTML = `
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button ${!isFirst ? 'collapsed' : ''}" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#${copaId}"
                                aria-expanded="${isFirst ? 'true' : 'false'}">
                            <div class="d-flex align-items-center w-100">
                                <img src="media/copas/${this.getCopaImageName(copa.nombre)}.png" 
                                     alt="${copa.nombre}" 
                                     class="me-3"
                                     style="width: 50px; height: 50px; object-fit: contain;">
                                <div class="flex-grow-1">
                                    <h5 class="mb-0">${copa.nombre}</h5>
                                    <small class="text-white-80">${copa.circuitos.length} circuitos</small>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="${copaId}" 
                         class="accordion-collapse collapse ${isFirst ? 'show' : ''}"
                         data-bs-parent="#copasAccordion">
                        <div class="accordion-body">
                            <div class="circuitos-grid">
                                ${this.renderCircuitos(copa.circuitos)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // ===== 9.1.5. AÑADIR HTML AL ACORDEÓN =====
            accordion.innerHTML += copaHTML;
        });
        
        // ===== 9.1.6. CONFIGURAR LISTENERS PARA LOS CIRCUITOS =====
        this.setupCircuitosListeners();
    }
    
    /* ===== 9.2. RENDERIZAR CIRCUITOS DE UNA COPA ===== */
    static renderCircuitos(circuitos) {
        // ===== 9.2.1. MAPEAR CIRCUITOS A HTML =====
        return circuitos.map(circuito => {
            // Verificar si circuito ya está seleccionado
            const isSelected = this.selectedCircuits.some(c => c.id === circuito.id);
            const displayName = this.formatCircuitoNombre(circuito.nombre);

            return `
                <div class="circuito-selector ${isSelected ? 'selected' : ''}" 
                    data-circuit-id="${circuito.id}"
                    data-circuit-name="${circuito.nombre}"
                    data-circuit-copa="${circuito.copa_nombre}">
                    <div class="circuito-image">
                        <img src="media/circuitos/${this.getCircuitoImageName(circuito.nombre)}.jpg" 
                            alt="${displayName}"
                            class="img-fluid rounded"
                            onerror="this.src='media/circuitos/default.jpg'">
                        <div class="circuito-overlay">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="circuito-info mt-2">
                        <h6 class="mb-1">${displayName}</h6>
                        <small class="text-muted">${circuito.copa_nombre}</small>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    /* ========== 10. UTILIDADES DE FORMATEO ========== */
    static formatCircuitoNombre(circuitoNombre) {
        if (circuitoNombre === 'CanionFerroviario') {
            return 'Cañon Ferroviario';
        }
        return circuitoNombre;
    }
    
    static getCopaImageName(copaNombre) {
        const mapping = {
            'Copa Champiñón': 'champ',
            'Copa Flor': 'flor',
            'Copa Estrella': 'estrella',
            'Copa Caparazón': 'caparazon',
            'Copa Plátano': 'platano',
            'Copa Hoja': 'hoja',
            'Copa Centella': 'centella',
            'Copa Especial': 'especial'
        };
        return mapping[copaNombre] || copaNombre.toLowerCase().replace(/[^a-z0-9]/g, '');
    }
    
    static getCircuitoImageName(circuitoNombre) {
        // Mapeo de nombres de circuitos a nombres de archivo
        const mapping = {
            // Copa Champiñón
            'Circuito Mario Bros.': 'CircuitoMarioBros',
            'Ciudad Corona (1)': 'CiudadCorona1',
            'Cañón Ferroviario': 'CanFerroviario',
            'Puerto Espacial DK': 'PuertoEspacialDK',
            
            // Copa Flor
            'Desierto Sol-Sol': 'DesiertoSolSol',
            'Bazar Shy Guy': 'BazarShyGuy',
            'Estadio Wario': 'EstadioWario',
            'Fortaleza Aérea': 'FortalezaArea',
            
            // Copa Estrella
            'DK Alpino': 'DKAlpino',
            'Mirador Estelar': 'MiradorEstelar',
            'Cielos Helados': 'CielosHelados',
            'Galeón de Wario': 'GaleondeWario',
            
            // Copa Caparazón
            'Playa Koopa': 'PlayaKoopa',
            'Sabana Salpicante': 'SabanaSalpicante',
            'Ciudad Corona (2)': 'CiudadCorona2',
            'Estadio Peach (1)': 'EstadioPeach1',
            
            // Copa Plátano
            'Playa Peach': 'PlayaPeach',
            'Ciudad Salina': 'CiudadSalina',
            'Jungla Dino Dino': 'JunglaDinoDino',
            'Templo del Bloque ?': 'TemplodelBloque',
            
            // Copa Hoja
            'Cascadas Cheep Cheep': 'CascadasCheepCheep',
            'Gruta Diente de León': 'GrutaDientedeLeon',
            'Cine Boo': 'CineBoo',
            'Caverna Ósea': 'CavernaOsea',
            
            // Copa Centella
            'Pradera Mu-Mu': 'PraderaMuMu',
            'Monte Chocolate': 'MonteChocolate',
            'Fábrica de Toad': 'FabricadeToad',
            'Castillo de Bowser': 'CastillodeBowser',
            
            // Copa Especial
            'Aldea Arbórea': 'AldeaArbrea',
            'Circuito Mario': 'CircuitoMario',
            'Estadio Peach (2)': 'EstadioPeach2',
            'Senda Arco Iris': 'SendaArcoIris'
        };
        
        return mapping[circuitoNombre] || circuitoNombre
            .replace(/[^\w\s]/gi, '')
            .replace(/\s+/g, '')
            .replace(/[()]/g, '')
            .replace(/[áéíóúÁÉÍÓÚ]/g, function(match) {
                const tildes = {
                    'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u',
                    'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U'
                };
                return tildes[match] || match;
            });
    }
    
    /* ========== 11. GUARDAR ESTADÍSTICAS ========== */
    static async guardarEstadisticas(resultados) {
        // ===== 11.1. VALIDAR AUTENTICACIÓN =====
        // Solo guardar estadísticas si el usuario está logueado
        if (!this.isLoggedIn || !this.userId) {
            console.log('Usuario no logueado, no se guardan estadísticas');
            return;
        }
        
        // ===== 11.2. ENVIAR PETICIÓN AL BACKEND =====
        try {
            const response = await fetch('../backend/api/guardar_estadisticas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    usuario_id: this.userId,
                    resultados: resultados
                })
            });
            
            // ===== 11.3. PROCESAR RESPUESTA =====
            const data = await response.json();
            
            if (data.success) {
                console.log('Estadísticas guardadas exitosamente');
            } else {
                console.error('Error guardando estadísticas:', data.message);
            }
        } catch (error) {
            console.error('Error de conexión al guardar estadísticas:', error);
        }
    }
    
    /* ========== 12. SISTEMA DE ALERTAS ========== */
    static showAlert(message, type = 'info') {
        // ===== 12.1. OBTENER CONTENEDOR Y GENERAR ID ÚNICO =====
        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now();
        
        // ===== 12.2. MAPEAR TIPOS DE ALERTA A ICONOS =====
        const icons = {
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'error': 'times-circle',
            'info': 'info-circle'
        };
        
        // ===== 12.3. CONSTRUIR HTML DE LA ALERTA =====
        const alertHTML = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icons[type] || 'info-circle'} me-3"></i>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // ===== 12.4. INYECTAR ALERTA EN EL DOM =====
        alertContainer.innerHTML = alertHTML;
        
        // ===== 12.5. REMOVER ALERTA AUTOMÁTICAMENTE DESPUÉS DE 5000MS =====
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Hacer funciones disponibles globalmente
window.CircuitosApp = CircuitosApp;