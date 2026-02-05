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
                this.useFallbackData();
            } else {
                this.renderCopas(data);
            }
        } catch (error) {
            console.error('Error de conexión:', error);
            this.useFallbackData();
        }
    }
    
    static useFallbackData() {
        // Datos de ejemplo como fallback
        const copasEjemplo = [
            {
                id: 1,
                nombre: "Copa Champiñón",
                circuitos: [
                    {id: 1, nombre: "Circuito Mario Bros.", copa_nombre: "Copa Champiñón"},
                    {id: 2, nombre: "Ciudad Corona (1)", copa_nombre: "Copa Champiñón"},
                    {id: 3, nombre: "Cañon Ferroviario", copa_nombre: "Copa Champiñón"},
                    {id: 4, nombre: "Puerto Espacial DK", copa_nombre: "Copa Champiñón"}
                ]
            },
            {
                id: 2,
                nombre: "Copa Flor",
                circuitos: [
                    {id: 5, nombre: "Desierto Sol-Sol", copa_nombre: "Copa Flor"},
                    {id: 6, nombre: "Bazar Shy Guy", copa_nombre: "Copa Flor"},
                    {id: 7, nombre: "Estadio Wario", copa_nombre: "Copa Flor"},
                    {id: 8, nombre: "Fortaleza Aerea", copa_nombre: "Copa Flor"}
                ]
            }
        ];
        
        this.renderCopas(copasEjemplo);
        this.showAlert('Usando datos de ejemplo. Los circuitos reales no están disponibles.', 'warning');
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
                                <img src="../frontend/media/copas/${this.getCopaImageName(copa.nombre)}.png" 
                                     alt="${copa.nombre}" 
                                     class="me-3"
                                     style="width: 50px; height: 50px; object-fit: contain;">
                                <div class="flex-grow-1">
                                    <h5 class="mb-0">${copa.nombre}</h5>
                                    <small class="text-white-80">${copa.circuitos.length} circuitos</small>
                                </div>
                                <i class="fas fa-chevron-down ms-2"></i>
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
            
            return `
                <div class="circuito-selector ${isSelected ? 'selected' : ''}" 
                     data-circuit-id="${circuito.id}"
                     data-circuit-name="${circuito.nombre}"
                     data-circuit-copa="${circuito.copa_nombre}">
                    <div class="circuito-image">
                        <img src="../frontend/media/circuitos/${this.getCircuitoImageName(circuito.nombre)}.jpg" 
                             alt="${circuito.nombre}"
                             class="img-fluid rounded"
                             onerror="this.src='../frontend/media/circuitos/default.jpg'">
                        <div class="circuito-overlay">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="circuito-info mt-2">
                        <h6 class="mb-1">${circuito.nombre}</h6>
                        <small class="text-muted">${circuito.copa_nombre}</small>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // ... (el resto del código se mantiene igual hasta el final)
    
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
            'Copa Rayo': 'rayo',
            'Copa Especial': 'especial'
        };
        return mapping[copaNombre] || copaNombre.toLowerCase().replace(/[^a-z0-9]/g, '');
    }
    
    static getCircuitoImageName(circuitoNombre) {
        return circuitoNombre
            .replace(/[^\w\s]/gi, '')
            .replace(/\s+/g, '')
            .replace(/[()]/g, '');
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