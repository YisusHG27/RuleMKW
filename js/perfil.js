class CircuitosApp {
    static selectedCircuits = [];
    static maxSelections = 4;
    static minSelections = 2;
    
    static init(copasData = null) {
        if (copasData) {
            this.renderCopas(copasData);
        } else {
            this.loadCopas();
        }
        
        this.setupEventListeners();
        this.updateUI();
    }
    
    static loadCopas() {
        // En desarrollo, usar datos de ejemplo
        const copasEjemplo = [
            {
                id: 1,
                nombre: "Copa Champiñón",
                circuitos: [
                    {id: 1, nombre: "Circuito Mario Bros.", copa_nombre: "Copa Champiñón"},
                    {id: 2, nombre: "Ciudad Corona (1)", copa_nombre: "Copa Champiñón"},
                    {id: 3, nombre: "Cañón Ferroviario", copa_nombre: "Copa Champiñón"},
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
                    {id: 8, nombre: "Fortaleza Aérea", copa_nombre: "Copa Flor"}
                ]
            },
            {
                id: 3,
                nombre: "Copa Estrella",
                circuitos: [
                    {id: 9, nombre: "DK Alpino", copa_nombre: "Copa Estrella"},
                    {id: 10, nombre: "Mirador Estelar", copa_nombre: "Copa Estrella"},
                    {id: 11, nombre: "Cielos Helados", copa_nombre: "Copa Estrella"},
                    {id: 12, nombre: "Galeón de Wario", copa_nombre: "Copa Estrella"}
                ]
            }
        ];
        
        this.renderCopas(copasEjemplo);
    }
    
    static renderCopas(copasData) {
        const accordion = document.getElementById('copasAccordion');
        accordion.innerHTML = '';
        
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
            const displayName = this.formatCircuitoNombre(circuito.nombre);

            return `
                <div class="circuito-selector ${isSelected ? 'selected' : ''}" 
                    data-circuit-id="${circuito.id}"
                    data-circuit-name="${circuito.nombre}"
                    data-circuit-copa="${circuito.copa_nombre}">
                    <div class="circuito-image">
                        <img src="media/circuitos/${this.getCircuitoImageName(circuito.nombre)}.jpg" 
                            alt="${displayName}"
                            class="img-fluid rounded">
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
    
    static setupCircuitosListeners() {
        document.querySelectorAll('.circuito-selector').forEach(circuito => {
            circuito.addEventListener('click', (e) => {
                const circuitId = parseInt(circuito.dataset.circuitId);
                const circuitName = circuito.dataset.circuitName;
                const circuitCopa = circuito.dataset.circuitCopa;
                
                this.toggleCircuitoSeleccion({
                    id: circuitId,
                    nombre: circuitName,
                    copa_nombre: circuitCopa
                }, circuito);
            });
        });
    }
    
    static toggleCircuitoSeleccion(circuitoData, element) {
        const index = this.selectedCircuits.findIndex(c => c.id === circuitoData.id);
        
        if (index === -1) {
            // Agregar
            if (this.selectedCircuits.length >= this.maxSelections) {
                this.showAlert(`Máximo ${this.maxSelections} circuitos permitidos`, 'warning');
                return;
            }
            
            this.selectedCircuits.push(circuitoData);
            element.classList.add('selected');
            
            // Efecto visual
            element.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                element.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
            
        } else {
            // Remover
            this.selectedCircuits.splice(index, 1);
            element.classList.remove('selected');
        }
        
        this.updateUI();
    }
    
    static updateUI() {
        this.updateContador();
        this.updateSelectedCircuitsDisplay();
        this.updateGirarButton();
        this.updateProgressBar();
    }
    
    static updateContador() {
        const contador = document.getElementById('contadorSeleccionados');
        const texto = document.getElementById('contadorTexto');
        const countElement = document.getElementById('selectedCount');
        
        const count = this.selectedCircuits.length;
        texto.textContent = `${count}/${this.maxSelections} circuitos seleccionados`;
        
        if (countElement) {
            countElement.textContent = count;
        }
        
        if (count > 0) {
            contador.classList.remove('d-none');
            
            // Cambiar color según cantidad
            if (count >= this.minSelections) {
                contador.style.borderLeftColor = '#06D6A0';
            } else {
                contador.style.borderLeftColor = '#FF6B6B';
            }
        } else {
            contador.classList.add('d-none');
        }
    }
    
    static updateProgressBar() {
        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            const progress = (this.selectedCircuits.length / this.maxSelections) * 100;
            progressBar.style.width = `${progress}%`;
            progressBar.style.backgroundColor = this.selectedCircuits.length >= this.minSelections ? '#06D6A0' : '#FF6B6B';
        }
    }
    
    static updateGirarButton() {
        const btn = document.getElementById('btnGirar');
        if (!btn) return;
        
        const canSpin = this.selectedCircuits.length >= this.minSelections;
        btn.disabled = !canSpin;
        
        if (canSpin) {
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-danger');
        } else {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-secondary');
        }
    }
    
    static updateSelectedCircuitsDisplay() {
        const container = document.getElementById('circuitosSeleccionados');
        
        if (this.selectedCircuits.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Sin circuitos seleccionados</h4>
                    <p class="text-muted">Selecciona circuitos de las copas para comenzar</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.selectedCircuits.map((circuito, index) => `
            <div class="circuito-card animate__animated animate__fadeIn" style="animation-delay: ${index * 0.1}s">
                <button class="remove-btn" onclick="CircuitosApp.removeCircuito(${circuito.id})">
                    <i class="fas fa-times"></i>
                </button>
                <img src="media/circuitos/${this.getCircuitoImageName(circuito.nombre)}.jpg" 
                     alt="${circuito.nombre}">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">${circuito.nombre}</h6>
                    <small class="text-muted d-block mb-2">${circuito.copa_nombre}</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">#${circuito.id}</span>
                        <span class="text-muted"><small>${index + 1}º</small></span>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    static removeCircuito(circuitId) {
        const index = this.selectedCircuits.findIndex(c => c.id === circuitId);
        if (index !== -1) {
            this.selectedCircuits.splice(index, 1);
            
            // Actualizar UI del circuito en el acordeón
            const circuitoElement = document.querySelector(`[data-circuit-id="${circuitId}"]`);
            if (circuitoElement) {
                circuitoElement.classList.remove('selected');
            }
            
            this.updateUI();
            this.showAlert('Circuito removido', 'info');
        }
    }
    
    static resetSeleccion() {
        this.selectedCircuits = [];
        
        // Limpiar selecciones en UI
        document.querySelectorAll('.circuito-selector.selected').forEach(el => {
            el.classList.remove('selected');
        });
        
        this.updateUI();
        RuletaApp.resetRuleta();
        
        this.showAlert('Selección reiniciada', 'info');
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
        return mapping[copaNombre] || 'champ';
    }
    
    static getCircuitoImageName(circuitoNombre) {
        if (circuitoNombre === 'Cañon Ferroviario' || circuitoNombre === 'CanionFerroviario') {
            return 'CanionFerroviario';
        }
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
window.RuletaApp = RuletaApp;