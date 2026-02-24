class CircuitosApp {
    static selectedCircuits = [];
    static maxSelections = 4;
    static minSelections = 2;
    static isLoggedIn = false;
    static userId = null;
    
    static init() {
        // Verificar si hay sesión
        this.checkSession();
        // Cargar circuitos
        this.loadCircuits();
        this.setupEventListeners();
        
        // Inicializar la ruleta
        if (window.RuletaApp && typeof window.RuletaApp.init === 'function') {
            window.RuletaApp.init();
        }
    }
    
    static checkSession() {
        // Verificar si hay sesión activa (esto debería venir del backend)
        this.isLoggedIn = false;
        this.userId = null;
    }
    
    static async loadCircuits() {
        try {
            const response = await fetch('../backend/api/get_circuitos.php');
            const data = await response.json();
            
            if (data.error) {
                console.error('Error cargando circuitos:', data.error);
            } else {
                this.renderCopas(data);
            }
        } catch (error) {
            console.error('Error de conexión:', error);
        }
    }
    
    static setupEventListeners() {
        console.log('Configurando event listeners generales');
        
        // Listener para el botón de reiniciar selección
        const btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', () => {
                this.clearSelectedCircuits();
            });
        }
        
        // Listener para el botón de girar ruleta
        const btnGirar = document.getElementById('btnGirar');
        if (btnGirar) {
            btnGirar.addEventListener('click', () => {
                if (window.RuletaApp && typeof window.RuletaApp.girarRuleta === 'function') {
                    window.RuletaApp.girarRuleta();
                }
            });
        }
        
        // Actualizar estado del botón girar
        this.updateGirarButtonState();
    }
    
    static setupCircuitosListeners() {
        console.log('Configurando listeners de circuitos');
        
        // Seleccionar todos los elementos de circuito
        document.querySelectorAll('.circuito-selector').forEach(selector => {
            selector.addEventListener('click', (e) => {
                // Evitar que el click se propague si se hace en el overlay
                if (e.target.closest('.circuito-overlay')) {
                    return;
                }
                
                const circuitId = parseInt(selector.dataset.circuitId);
                const circuitName = selector.dataset.circuitName;
                const circuitCopa = selector.dataset.circuitCopa;
                
                this.toggleCircuitSelection({
                    id: circuitId,
                    nombre: circuitName,
                    copa_nombre: circuitCopa
                }, selector);
            });
        });
    }
    
    static toggleCircuitSelection(circuito, elemento) {
        const index = this.selectedCircuits.findIndex(c => c.id === circuito.id);
        
        if (index === -1) {
            // Añadir circuito
            if (this.selectedCircuits.length < this.maxSelections) {
                this.selectedCircuits.push(circuito);
                elemento.classList.add('selected');
                this.showAlert(`"${this.formatCircuitoNombre(circuito.nombre)}" añadido a la ruleta`, 'success');
            } else {
                this.showAlert(`Máximo ${this.maxSelections} circuitos en la ruleta`, 'warning');
                return;
            }
        } else {
            // Quitar circuito
            this.selectedCircuits.splice(index, 1);
            elemento.classList.remove('selected');
            this.showAlert(`"${this.formatCircuitoNombre(circuito.nombre)}" eliminado de la ruleta`, 'info');
        }
        
        // Actualizar contador
        this.updateSelectedCounter();
        
        // Actualizar estado del botón girar
        this.updateGirarButtonState();
        
        // ACTUALIZAR LA RULETA CON LOS CIRCUITOS SELECCIONADOS
        if (window.RuletaApp && typeof window.RuletaApp.actualizarRuletaConCircuitos === 'function') {
            window.RuletaApp.actualizarRuletaConCircuitos(this.selectedCircuits);
        }
    }
    
    static updateSelectedCounter() {
        const contadorTexto = document.getElementById('contadorTexto');
        const progressBar = document.getElementById('progressBar');
        const selectedCount = document.getElementById('selectedCount');
        
        if (contadorTexto) {
            contadorTexto.textContent = `${this.selectedCircuits.length}/${this.maxSelections} circuitos en la ruleta`;
        }
        
        if (progressBar) {
            const porcentaje = (this.selectedCircuits.length / this.maxSelections) * 100;
            progressBar.style.width = `${porcentaje}%`;
        }
        
        if (selectedCount) {
            selectedCount.textContent = this.selectedCircuits.length;
        }
    }
    
    static updateGirarButtonState() {
        const btnGirar = document.getElementById('btnGirar');
        if (btnGirar) {
            const isValid = this.selectedCircuits.length >= this.minSelections && 
                           this.selectedCircuits.length <= this.maxSelections;
            btnGirar.disabled = !isValid;
            
            if (!isValid) {
                btnGirar.title = `Selecciona entre ${this.minSelections} y ${this.maxSelections} circuitos`;
            } else {
                btnGirar.title = '¡Girar ruleta!';
            }
        }
    }
    
    static clearSelectedCircuits() {
        // Quitar clase selected de todos los elementos
        document.querySelectorAll('.circuito-selector.selected').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Vaciar array
        this.selectedCircuits = [];
        
        // Actualizar contador
        this.updateSelectedCounter();
        this.updateGirarButtonState();
        
        // Limpiar la ruleta
        if (window.RuletaApp && typeof window.RuletaApp.actualizarRuletaConCircuitos === 'function') {
            window.RuletaApp.actualizarRuletaConCircuitos([]);
        }
        
        this.showAlert('Selección reiniciada', 'info');
    }
    
    static renderCopas(copasData) {
        const accordion = document.getElementById('copasAccordion');
        accordion.innerHTML = '';
        
        if (!copasData || copasData.length === 0) {
            accordion.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No se pudieron cargar los circuitos. Intenta recargar la página.
                </div>
            `;
            return;
        }
        
        copasData.forEach((copa, index) => {
            const copaId = `copa${copa.id}`;
            const isFirst = index === 0;
            
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
            
            accordion.innerHTML += copaHTML;
        });
        
        this.setupCircuitosListeners();
    }
    
    static renderCircuitos(circuitos) {
        return circuitos.map(circuito => {
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
    
    static formatCircuitoNombre(circuitoNombre) {
        if (circuitoNombre === 'CanionFerroviario') {
            return 'Cañon Ferroviario';
        }
        return circuitoNombre;
    }
    
    static async guardarEstadisticas(resultados) {
        // Solo guardar estadísticas si el usuario está logueado
        if (!this.isLoggedIn || !this.userId) {
            console.log('Usuario no logueado, no se guardan estadísticas');
            return;
        }
        
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
    
    static showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
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
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Hacer funciones disponibles globalmente
window.CircuitosApp = CircuitosApp;