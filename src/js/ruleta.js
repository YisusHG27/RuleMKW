class RuletaApp {
    static resultados = [];
    static isSpinning = false;
    static currentSlot = 0;
    static animationInterval = null;
    static winners = [];
    static spinCount = 0; // Contador de veces girada
    
    static init() {
        this.resetSlots();
        this.setupEventListeners();
        this.spinCount = 0;
        this.updateSpinCount();
    }
    
    static setupEventListeners() {
        document.getElementById('btnGirar').addEventListener('click', () => this.girarRuleta());
        document.getElementById('btnReset').addEventListener('click', () => this.resetRuleta());
        document.getElementById('btnNuevoIntento')?.addEventListener('click', () => this.resetRuleta());
    }
    
    static updateSpinCount() {
        const countElement = document.getElementById('resultadosCount');
        if (countElement) {
            countElement.textContent = this.spinCount;
        }
    }
    
    // Actualizar la ruleta con los circuitos seleccionados
    static actualizarRuletaConCircuitos(circuitos) {
        const slots = document.querySelectorAll('.ruleta-slot');
        
        // Limpiar todos los slots primero
        slots.forEach(slot => {
            const content = slot.querySelector('.slot-content');
            
            content.innerHTML = `
                <div class="slot-placeholder">
                    <i class="fas fa-flag-checkered fa-2x"></i>
                    <p class="mt-2">Esperando...</p>
                </div>
            `;
            content.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            slot.style.border = '3px solid transparent';
            slot.classList.remove('winner');
        });
        
        // Llenar los slots con los circuitos seleccionados
        circuitos.forEach((circuito, index) => {
            if (index < slots.length) {
                const slot = slots[index];
                const content = slot.querySelector('.slot-content');
                const displayName = CircuitosApp.formatCircuitoNombre(circuito.nombre);
                const imageName = CircuitosApp.getCircuitoImageName(circuito.nombre);
                
                content.innerHTML = `
                    <img src="media/circuitos/${imageName}.jpg" 
                         alt="${displayName}"
                         class="slot-circuit-image"
                         onerror="this.src='media/circuitos/default.jpg'">
                    <div class="slot-circuit-info">
                        <h6 class="fw-bold mb-1">${displayName}</h6>
                        <small>${circuito.copa_nombre || ''}</small>
                    </div>
                    <button class="btn-remove-ruleta" data-id="${circuito.id}" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                content.style.background = 'transparent';
            }
        });
        
        // Añadir event listeners a los botones de eliminar
        document.querySelectorAll('.btn-remove-ruleta').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                
                const id = parseInt(btn.dataset.id);
                
                const elementoSelector = document.querySelector(`.circuito-selector[data-circuit-id="${id}"]`);
                if (elementoSelector) {
                    elementoSelector.click();
                }
            });
        });
    }
    
    static async girarRuleta() {
        if (this.isSpinning) return;
        
        const circuitos = CircuitosApp?.selectedCircuits || [];
        if (circuitos.length < 2) {
            this.showAlert('Selecciona al menos 2 circuitos en la ruleta', 'warning');
            return;
        }
        
        this.isSpinning = true;
        this.winners = [];
        
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = true;
        btnGirar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> GIRANDO...';
        
        // Quitar clase winner de todos los slots
        document.querySelectorAll('.ruleta-slot').forEach(slot => {
            slot.classList.remove('winner');
        });
        
        // Ocultar resultados anteriores
        document.getElementById('resultsSection').style.display = 'none';
        
        // Iniciar animación
        this.startSlotAnimation();
        
        // Simular proceso de selección
        setTimeout(() => {
            // SELECCIONAR SOLO 1 GANADOR ALEATORIO
            this.selectSingleWinner(circuitos);
            this.stopSlotAnimation();
            
            // Mostrar el único ganador en el panel de resultados
            this.mostrarResultadoUnico();
            
            // Animar el slot ganador
            this.animateWinner();
            
            // Incrementar contador de veces girada
            this.spinCount++;
            this.updateSpinCount();
            
            setTimeout(() => {
                this.isSpinning = false;
                btnGirar.disabled = false;
                btnGirar.innerHTML = '<i class="fas fa-play me-2"></i> GIRAR RULETA';
                
                if (CircuitosApp.isLoggedIn) {
                    this.guardarEstadisticas();
                }
            }, 1500);
        }, 3000);
    }
    
    static selectSingleWinner(circuitos) {
        // Seleccionar SOLO 1 circuito aleatorio
        const randomIndex = Math.floor(Math.random() * circuitos.length);
        this.winners = [circuitos[randomIndex]]; // Solo un ganador
    }
    
    static animateWinner() {
        if (this.winners.length === 0) return;
        
        const slots = document.querySelectorAll('.ruleta-slot');
        const winner = this.winners[0];
        
        // Buscar qué slot contiene al ganador
        slots.forEach((slot, index) => {
            const btn = slot.querySelector('.btn-remove-ruleta');
            if (btn) {
                const circuitoId = parseInt(btn.dataset.id);
                if (circuitoId === winner.id) {
                    // Este slot contiene al ganador
                    setTimeout(() => {
                        slot.classList.add('winner');
                        
                        // Actualizar el contenido para mostrar que es el ganador
                        const content = slot.querySelector('.slot-content');
                        const displayName = CircuitosApp.formatCircuitoNombre(winner.nombre);
                        const imageName = CircuitosApp.getCircuitoImageName(winner.nombre);
                        
                        content.innerHTML = `
                            <img src="media/circuitos/${imageName}.jpg" 
                                 alt="${displayName}"
                                 class="slot-circuit-image"
                                 onerror="this.src='media/circuitos/default.jpg'">
                            <div class="slot-circuit-info">
                                <h6 class="fw-bold mb-1">${displayName}</h6>
                                <small>${winner.copa_nombre || ''}</small>
                                <span class="badge bg-warning text-dark mt-1">¡GANADOR!</span>
                            </div>
                        `;
                    }, 500);
                }
            }
        });
    }
    
    static startSlotAnimation() {
        const slots = document.querySelectorAll('.ruleta-slot');
        let slotIndex = 0;
        
        this.animationInterval = setInterval(() => {
            slots.forEach(slot => slot.classList.remove('active'));
            slots[slotIndex].classList.add('active');
            slotIndex = (slotIndex + 1) % slots.length;
        }, 150);
    }
    
    static stopSlotAnimation() {
        if (this.animationInterval) {
            clearInterval(this.animationInterval);
            this.animationInterval = null;
        }
        
        document.querySelectorAll('.ruleta-slot').forEach(slot => {
            slot.classList.remove('active');
        });
    }
    
    static mostrarResultadoUnico() {
        const container = document.getElementById('resultadosGrid');
        
        if (!container) return;
        
        if (this.winners.length === 0) {
            container.innerHTML = `
                <div class="empty-state text-center py-5">
                    <i class="fas fa-history fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Sin resultados</h4>
                    <p class="text-muted">Gira la ruleta para ver el circuito ganador</p>
                </div>
            `;
            return;
        }
        
        const winner = this.winners[0];
        const displayName = CircuitosApp.formatCircuitoNombre(winner.nombre);
        const imageName = CircuitosApp.getCircuitoImageName(winner.nombre);
        
        container.innerHTML = `
            <div class="selected-circuito-item resultado-item winner-card">
                <div class="winner-badge">
                    <i class="fas fa-crown"></i> GANADOR
                </div>
                <div class="selected-circuito-image">
                    <img src="media/circuitos/${imageName}.jpg" 
                         alt="${displayName}"
                         onerror="this.src='media/circuitos/default.jpg'">
                </div>
                <div class="selected-circuito-info">
                    <h6>${displayName}</h6>
                    <small>${winner.copa_nombre || ''}</small>
                </div>
            </div>
        `;
    }
    
    static resetSlots() {
        const slots = document.querySelectorAll('.ruleta-slot');
        
        slots.forEach(slot => {
            const content = slot.querySelector('.slot-content');
            content.innerHTML = `
                <div class="slot-placeholder">
                    <i class="fas fa-flag-checkered fa-2x"></i>
                    <p class="mt-2">Esperando...</p>
                </div>
            `;
            content.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            slot.style.border = '3px solid transparent';
            slot.classList.remove('active', 'winner');
        });
    }
    
    static resetRuleta() {
        this.stopSlotAnimation();
        
        // Restaurar los circuitos seleccionados en la ruleta
        const circuitos = CircuitosApp?.selectedCircuits || [];
        this.actualizarRuletaConCircuitos(circuitos);
        
        this.isSpinning = false;
        this.winners = [];
        
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = circuitos.length < 2;
        btnGirar.innerHTML = '<i class="fas fa-play me-2"></i> GIRAR RULETA';
        
        document.getElementById('resultsSection').style.display = 'none';
        
        // Limpiar panel de resultados
        const container = document.getElementById('resultadosGrid');
        if (container) {
            container.innerHTML = `
                <div class="empty-state text-center py-5">
                    <i class="fas fa-history fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Sin resultados</h4>
                    <p class="text-muted">Gira la ruleta para ver el circuito ganador</p>
                </div>
            `;
        }
        
        this.showAlert('Ruleta reiniciada', 'info');
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
    
    static async guardarEstadisticas() {
        if (!CircuitosApp.isLoggedIn) {
            return;
        }
        
        try {
            await CircuitosApp.guardarEstadisticas(this.winners);
        } catch (error) {
            console.error('Error guardando estadísticas:', error);
        }
    }
}

window.RuletaApp = RuletaApp;