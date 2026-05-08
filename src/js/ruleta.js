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
        
        // ===== 5.1. LIMPIAR TODOS LOS SLOTS PRIMERO =====
        // Restaurar slots al estado inicial con placeholder
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
        
        // ===== 5.2. LLENAR LOS SLOTS CON CIRCUITOS SELECCIONADOS =====
        // Iterar sobre los circuitos seleccionados y poblar cada slot con su contenido
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
        
        // ===== 5.3. ASIGNAR EVENT LISTENERS A BOTONES DE ELIMINAR =====
        // Configurar los botones de eliminar para cada slot
        this.addRemoveListeners();
    }
    
    /* ========== 6. GESTIÓN DE BOTONES ELIMINAR ========== */
    static addRemoveListeners() {
        // ===== 6.1. LIMPIAR LISTENERS ANTERIORES =====
        // Clonar cada botón para remover listeners duplicados
        document.querySelectorAll('.btn-remove-ruleta').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true));
        });
        
        // ===== 6.2. ASIGNAR NUEVOS LISTENERS =====
        // Añadir listeners a los botones de eliminar circuito
        document.querySelectorAll('.btn-remove-ruleta').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                
                // Obtener ID del circuito a eliminar
                const id = parseInt(btn.dataset.id);
                
                // Buscar el selector del circuito y simulare un click
                const elementoSelector = document.querySelector(`.circuito-selector[data-circuit-id="${id}"]`);
                if (elementoSelector) {
                    elementoSelector.click();
                }
            });
        });
    }
    
    /* ========== 7. FUNCIÓN PRINCIPAL DE GIRAR ========== */
    static async girarRuleta() {
        // ===== 7.1. VALIDAR QUE NO HAYA UN GIRO EN PROGRESO =====
        if (this.isSpinning) return;
        
        // ===== 7.2. VALIDAR CANTIDAD DE CIRCUITOS SELECCIONADOS =====
        const circuitos = CircuitosApp?.selectedCircuits || [];
        if (circuitos.length < 2) {
            this.showAlert('Selecciona al menos 2 circuitos en la ruleta', 'warning');
            return;
        }
        
        // ===== 7.3. PREPARAR ESTADO PARA EL GIRO =====
        this.isSpinning = true;
        this.winners = [];
        this.ultimaTirada = []; // Reiniciar la última tirada
        
        // ===== 7.4. ACTUALIZAR BOTÓN DE GIRAR =====
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = true;
        btnGirar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> GIRANDO...';
        
        // ===== 7.5. REINICIAR SLOTS A ESTADO ORIGINAL =====
        // Quitar clase winner de todos los slots
        document.querySelectorAll('.ruleta-slot').forEach(slot => {
            slot.classList.remove('winner');
        });
        
        // Restaurar los slots con los circuitos seleccionados (sin badge de ganador)
        this.actualizarRuletaConCircuitos(circuitos);
        
        // Ocultar resultados anteriores
        document.getElementById('resultsSection').style.display = 'none';
        
        // ===== 7.6. INICIAR ANIMACIÓN =====
        this.startSlotAnimation();
        
        // ===== 7.7. SIMULAR PROCESO DE SELECCIÓN (3000ms) =====
        setTimeout(() => {
            // Seleccionar solo 1 ganador aleatorio
            this.selectSingleWinner(circuitos);
            // Detener animación de slots
            this.stopSlotAnimation();
            
            // Mostrar el único ganador en el panel de resultados
            this.mostrarResultadoUnico();
            
            // Animar el slot ganador
            this.animateWinner();
            
            // Incrementar contador de veces girada
            this.spinCount++;
            this.updateSpinCount();
            
            // ===== 7.8. FINALIZAR GIRO (1500ms después) =====
            setTimeout(() => {
                this.isSpinning = false;
                btnGirar.disabled = false;
                btnGirar.innerHTML = '<i class="fas fa-play me-2"></i> GIRAR RULETA';
                
                // Guardar estadísticas si usuario está logueado
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
    
    /* ========== 9. ANIMACIONES DE SLOTS ========== */
    /* ===== 9.1. INICIAR ANIMACIÓN DE SLOTS ===== */
    static startSlotAnimation() {
        const slots = document.querySelectorAll('.ruleta-slot');
        let slotIndex = 0;
        
        // Crear intervalo que itera sobre los slots cada 150ms
        this.animationInterval = setInterval(() => {
            // Remover clase active de todos los slots
            slots.forEach(slot => slot.classList.remove('active'));
            // Añadir clase active al slot actual
            slots[slotIndex].classList.add('active');
            // Incrementar índice de forma cíclica
            slotIndex = (slotIndex + 1) % slots.length;
        }, 150);
    }
    
    /* ===== 9.2. DETENER ANIMACIÓN DE SLOTS ===== */
    static stopSlotAnimation() {
        // Limpiar intervalo si existe
        if (this.animationInterval) {
            clearInterval(this.animationInterval);
            this.animationInterval = null;
        }
        
        // Remover clase active de todos los slots
        document.querySelectorAll('.ruleta-slot').forEach(slot => {
            slot.classList.remove('active');
        });
    }
    
    /* ===== 9.3. ANIMAR SLOT GANADOR ===== */
    static animateWinner() {
        // Verificar que haya un ganador seleccionado
        if (this.winners.length === 0) return;
        
        const slots = document.querySelectorAll('.ruleta-slot');
        const winner = this.winners[0];
        
        // ===== 9.3.1. BUSCAR Y ANIMAR EL SLOT GANADOR =====
        slots.forEach((slot, index) => {
            const btn = slot.querySelector('.btn-remove-ruleta');
            if (btn) {
                const circuitoId = parseInt(btn.dataset.id);
                // Verificar si este slot contiene al ganador
                if (circuitoId === winner.id) {
                    // ===== 9.3.2. APLICAR CLASE WINNER Y ACTUALIZAR CONTENIDO =====
                    setTimeout(() => {
                        // Añadir clase visual winner
                        slot.classList.add('winner');
                        
                        // Obtener referencias necesarias
                        const content = slot.querySelector('.slot-content');
                        const displayName = CircuitosApp.formatCircuitoNombre(winner.nombre);
                        const imageName = CircuitosApp.getCircuitoImageName(winner.nombre);
                        
                        // ===== 9.3.3. ACTUALIZAR HTML CON BADGE DE GANADOR =====
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
                        
                        // ===== 9.3.4. REASIGNAR EVENT LISTENER AL NUEVO BOTÓN =====
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
    
    /* ========== 10. MOSTRAR RESULTADO ÚNICO ========== */
    static mostrarResultadoUnico() {
        // ===== 10.1. OBTENER CONTENEDOR DE RESULTADOS =====
        const container = document.getElementById('resultadosGrid');
        
        if (!container) return;
        
        // ===== 10.2. MOSTRAR ESTADO VACÍO SI NO HAY GANADOR =====
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
        
        // ===== 10.3. OBTENER DATOS DEL GANADOR =====
        const winner = this.winners[0];
        const displayName = CircuitosApp.formatCircuitoNombre(winner.nombre);
        const imageName = CircuitosApp.getCircuitoImageName(winner.nombre);
        
        // ===== 10.4. RENDERIZAR TARJETA DEL GANADOR =====
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
    
    /* ========== 11. REINICIAR ESTADOS ========== */
    /* ===== 11.1. REINICIAR SLOTS AL PLACEHOLDER INICIAL ===== */
    static resetSlots() {
        const slots = document.querySelectorAll('.ruleta-slot');
        
        // Iterar sobre cada slot y restaurar a su estado inicial
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
    
    /* ===== 11.2. REINICIAR LA RULETA COMPLETA ===== */
    static resetRuleta() {
        // ===== 11.2.1. DETENER ANIMACIÓN EN PROGRESO =====
        this.stopSlotAnimation();
        
        // ===== 11.2.2. RESTAURAR CIRCUITOS EN SLOTS =====
        const circuitos = CircuitosApp?.selectedCircuits || [];
        this.actualizarRuletaConCircuitos(circuitos);
        
        // ===== 11.2.3. RESETEAR PROPIEDADES =====
        this.isSpinning = false;
        this.winners = [];
        this.ultimaTirada = [];
        
        // ===== 11.2.4. RESTAURAR BOTÓN DE GIRAR =====
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = circuitos.length < 2;
        btnGirar.innerHTML = '<i class="fas fa-play me-2"></i> GIRAR RULETA';
        
        // ===== 11.2.5. OCULTAR SECCIÓN DE RESULTADOS =====
        document.getElementById('resultsSection').style.display = 'none';
        
        // ===== 11.2.6. LIMPIAR PANEL DE RESULTADOS =====
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
    
    /* ========== 13. GUARDAR ESTADÍSTICAS ========== */
    static async guardarEstadisticas() {
        // ===== 13.1. VERIFICAR AUTENTICACIÓN =====
        if (!CircuitosApp.isLoggedIn) {
            console.log('Usuario no logueado, no se guardan estadísticas');
            return;
        }
        
        // ===== 13.2. OBTENER DATOS A GUARDAR =====
        // Usar ultimaTirada (todos los circuitos) pero necesitamos identificar al ganador
        const datosAGuardar = this.ultimaTirada;
        const ganador = this.winners[0]; // El ganador está en winners
        
        // ===== 13.3. VALIDAR QUE HAYA DATOS =====
        if (!datosAGuardar || datosAGuardar.length === 0) {
            console.log('No hay datos para guardar');
            return;
        }
        
        console.log('Usuario logueado, guardando estadísticas...');
        console.log('Todos los circuitos:', datosAGuardar);
        console.log('Ganador:', ganador);
        
        // ===== 13.4. ENVIAR PETICIÓN AL BACKEND =====
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
            
            // ===== 13.5. PROCESAR RESPUESTA DEL BACKEND =====
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