/* ==========================================================================
   RULETAAPP - GESTIÓN DE LA RULETA Y ANIMACIONES
   ========================================================================== */

class RuletaApp {
    /* ========== 1. PROPIEDADES ESTÁTICAS ========== */
    static resultados = [];              // Historial de resultados
    static isSpinning = false;            // Estado de giro
    static currentSlot = 0;               // Slot actual en animación
    static animationInterval = null;       // Intervalo de animación
    static winners = [];                   // Ganadores actuales (solo el ganador para mostrar)
    static ultimaTirada = [];              // Tirada completa (todos los circuitos)
    static spinCount = 0;                  // Contador de veces girada
    
    /* ========== 2. INICIALIZACIÓN ========== */
    static init() {
        this.resetSlots();
        this.setupEventListeners();
        this.spinCount = 0;
        this.updateSpinCount();
    }
    
    /* ========== 3. EVENT LISTENERS ========== */
    static setupEventListeners() {
        document.getElementById('btnGirar').addEventListener('click', () => this.girarRuleta());
        document.getElementById('btnReset').addEventListener('click', () => this.resetRuleta());
        document.getElementById('btnNuevoIntento')?.addEventListener('click', () => this.resetRuleta());
    }
    
    /* ========== 4. ACTUALIZACIÓN DE UI ========== */
    static updateSpinCount() {
        const countElement = document.getElementById('resultadosCount');
        if (countElement) {
            countElement.textContent = this.spinCount;
        }
    }
    
    /* ========== 5. GESTIÓN DE SLOTS ========== */
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
        
        // Llenar los slots con los circuitos seleccionados (SIN badge de ganador)
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
        this.addRemoveListeners();
    }
    
    /* ========== 6. GESTIÓN DE BOTONES ELIMINAR ========== */
    static addRemoveListeners() {
        document.querySelectorAll('.btn-remove-ruleta').forEach(btn => {
            // Eliminar listener anterior para evitar duplicados
            btn.replaceWith(btn.cloneNode(true));
        });
        
        // Añadir nuevos listeners
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
    
    /* ========== 7. FUNCIÓN PRINCIPAL DE GIRAR ========== */
    static async girarRuleta() {
        if (this.isSpinning) return;
        
        const circuitos = CircuitosApp?.selectedCircuits || [];
        if (circuitos.length < 2) {
            this.showAlert('Selecciona al menos 2 circuitos en la ruleta', 'warning');
            return;
        }
        
        this.isSpinning = true;
        this.winners = [];
        this.ultimaTirada = []; // Reiniciar la última tirada
        
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = true;
        btnGirar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> GIRANDO...';
        
        // ===== REINICIAR LOS SLOTS A SU ESTADO ORIGINAL =====
        // Quitar clase winner de todos los slots
        document.querySelectorAll('.ruleta-slot').forEach(slot => {
            slot.classList.remove('winner');
        });
        
        // Restaurar los slots con los circuitos seleccionados (sin badge de ganador)
        this.actualizarRuletaConCircuitos(circuitos);
        
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
    
    /* ========== 8. SELECCIÓN DE GANADOR ========== */
    static selectSingleWinner(circuitos) {
        // Guardar la tirada completa para el historial (EN EL MISMO ORDEN)
        this.ultimaTirada = [...circuitos]; // Copia del array original
        console.log('📋 Tirada completa guardada:', this.ultimaTirada);
        
        // Seleccionar SOLO 1 circuito aleatorio para mostrar como ganador
        const randomIndex = Math.floor(Math.random() * circuitos.length);
        this.winners = [circuitos[randomIndex]];
        console.log('🏆 Ganador seleccionado:', this.winners[0]);
        console.log('🏆 ID del ganador:', this.winners[0].id);
    }
    
    /* ========== 9. ANIMACIONES ========== */
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
                        
                        // Actualizar el contenido para mostrar que es el ganador (CON badge)
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
                            <button class="btn-remove-ruleta" data-id="${winner.id}" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        
                        // Reasignar el event listener al nuevo botón
                        const newBtn = content.querySelector('.btn-remove-ruleta');
                        newBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            
                            const id = parseInt(newBtn.dataset.id);
                            const elementoSelector = document.querySelector(`.circuito-selector[data-circuit-id="${id}"]`);
                            if (elementoSelector) {
                                elementoSelector.click();
                            }
                        });
                    }, 500);
                }
            }
        });
    }
    
    /* ========== 10. RESULTADOS ========== */
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
            <div class="winner-card">
                <div class="winner-header">
                    <span class="winner-crown">👑</span>
                    <span class="winner-title">GANADOR</span>
                </div>
                <div class="winner-image-container">
                    <img src="media/circuitos/${imageName}.jpg" 
                        alt="${displayName}"
                        class="winner-image"
                        onerror="this.src='media/circuitos/default.jpg'">
                </div>
                <div class="winner-info">
                    <h3 class="winner-name">${displayName}</h3>
                    <p class="winner-copa">${winner.copa_nombre || ''}</p>
                </div>
            </div>
        `;
    }
    
    /* ========== 11. RESETEO ========== */
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
        this.ultimaTirada = [];
        
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
    
    /* ========== 12. SISTEMA DE ALERTAS ========== */
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
    
    /* ========== 13. ESTADÍSTICAS ========== */
    static async guardarEstadisticas() {
        if (!CircuitosApp.isLoggedIn) {
            console.log('Usuario no logueado, no se guardan estadísticas');
            return;
        }
        
        // Usar ultimaTirada (todos los circuitos) pero necesitamos identificar al ganador
        const datosAGuardar = this.ultimaTirada;
        const ganador = this.winners[0]; // El ganador está en winners
        
        if (!datosAGuardar || datosAGuardar.length === 0) {
            console.log('No hay datos para guardar');
            return;
        }
        
        console.log('Usuario logueado, guardando estadísticas...');
        console.log('Todos los circuitos:', datosAGuardar);
        console.log('Ganador:', ganador);
        
        try {
            const response = await fetch('../backend/api/guardar_estadisticas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    resultados: datosAGuardar,
                    ganador_id: ganador.id // Enviamos explícitamente el ID del ganador
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
}

window.RuletaApp = RuletaApp;