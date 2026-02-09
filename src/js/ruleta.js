class RuletaApp {
    static resultados = [];
    static isSpinning = false;
    static currentSlot = 0;
    static animationInterval = null;
    static winners = [];
    
    static init() {
        this.resetSlots();
        this.setupEventListeners();
    }
    
    static setupEventListeners() {
        document.getElementById('btnGirar').addEventListener('click', () => this.girarRuleta());
        document.getElementById('btnReset').addEventListener('click', () => this.resetRuleta());
        document.getElementById('btnNuevoIntento')?.addEventListener('click', () => this.resetRuleta());
    }
    
    static async girarRuleta() {
        if (this.isSpinning) return;
        
        const circuitos = CircuitosApp?.selectedCircuits || [];
        if (circuitos.length < 2) {
            this.showAlert('Selecciona al menos 2 circuitos', 'warning');
            return;
        }
        
        this.isSpinning = true;
        this.winners = [];
        this.resetSlots();
        
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = true;
        btnGirar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> GIRANDO...';
        
        // Ocultar resultados anteriores
        document.getElementById('resultsSection').style.display = 'none';
        
        // Iniciar animación
        this.startSlotAnimation();
        
        // Simular proceso de selección
        setTimeout(() => {
            this.selectWinners(circuitos);
            this.stopSlotAnimation();
            this.animateWinners();
            
            // Mostrar resultados después de la animación
            setTimeout(() => {
                this.mostrarResultados();
                this.isSpinning = false;
                btnGirar.disabled = false;
                btnGirar.innerHTML = '<i class="fas fa-play me-2"></i> GIRAR RULETA';
                
                // Guardar estadísticas solo si está logueado
                if (CircuitosApp.isLoggedIn) {
                    this.guardarEstadisticas();
                } else {
                    this.showAlert('Inicia sesión para guardar tus estadísticas', 'info');
                }
            }, 2000);
        }, 3000);
    }
    
    static startSlotAnimation() {
        const slots = document.querySelectorAll('.ruleta-slot');
        let slotIndex = 0;
        
        this.animationInterval = setInterval(() => {
            // Quitar clase activa a todos
            slots.forEach(slot => slot.classList.remove('active'));
            
            // Activar slot actual
            slots[slotIndex].classList.add('active');
            
            // Efecto de sonido (opcional)
            this.playSound('tick');
            
            // Siguiente slot
            slotIndex = (slotIndex + 1) % slots.length;
            
        }, 150); // Velocidad de animación
    }
    
    static stopSlotAnimation() {
        if (this.animationInterval) {
            clearInterval(this.animationInterval);
            this.animationInterval = null;
        }
        
        // Quitar clase activa a todos
        document.querySelectorAll('.ruleta-slot').forEach(slot => {
            slot.classList.remove('active');
        });
    }
    
    static selectWinners(circuitos) {
        // Seleccionar 4 circuitos únicos aleatoriamente
        this.winners = [];
        const available = [...circuitos];
        
        while (this.winners.length < 4 && available.length > 0) {
            const randomIndex = Math.floor(Math.random() * available.length);
            this.winners.push(available[randomIndex]);
            available.splice(randomIndex, 1);
        }
    }
    
    static animateWinners() {
        const slots = document.querySelectorAll('.ruleta-slot');
        
        // Animar cada ganador con retraso progresivo
        this.winners.forEach((winner, index) => {
            setTimeout(() => {
                const slot = slots[index];
                
                // Actualizar contenido del slot
                this.updateSlotContent(slot, winner);
                
                // Animación de ganador
                slot.classList.add('winner');
                slot.classList.add('animate__animated', 'animate__pulse');
                
                // Efecto de confeti (opcional)
                if (index === 0) { // Solo para el primer lugar
                    this.createConfetti();
                }
                
            }, index * 500); // Retraso progresivo
        });
    }
    
    static updateSlotContent(slot, circuito) {
        const content = slot.querySelector('.slot-content');
        const displayName = this.formatCircuitoNombre(circuito.nombre);
        content.innerHTML = `
        <div class="circuito-info">
            <h6 class="fw-bold mb-1">${displayName}</h6>
                <small class="text-white-80">${circuito.copa_nombre || ''}</small>
                <div class="mt-3">
                    <span class="badge bg-warning">${this.getPositionName(slot.classList[1])}</span>
                </div>
            </div>
        `;
        
        // Cambiar color según posición
        const slotNumber = slot.classList[1];
        switch(slotNumber) {
            case 'slot-1':
                content.style.background = 'linear-gradient(135deg, #FFD700, #FFA500)';
                break;
            case 'slot-2':
                content.style.background = 'linear-gradient(135deg, #C0C0C0, #A9A9A9)';
                break;
            case 'slot-3':
                content.style.background = 'linear-gradient(135deg, #CD7F32, #8B4513)';
                break;
            default:
                content.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
        }
    }
    
    static getPositionName(slotClass) {
        switch(slotClass) {
            case 'slot-1': return '1º';
            case 'slot-2': return '2º';
            case 'slot-3': return '3º';
            case 'slot-4': return '4º';
            default: return '';
        }
    }
    
    static mostrarResultados() {
        const container = document.getElementById('resultadoRuleta');
        const resultsSection = document.getElementById('resultsSection');
        
        container.innerHTML = '';
        
        this.winners.forEach((circuito, index) => {
            const position = index + 1;
            const positionClass = position === 1 ? 'gold' : 
                                 position === 2 ? 'silver' : 
                                 position === 3 ? 'bronze' : 'normal';
            const displayName = this.formatCircuitoNombre(circuito.nombre);
            const col = document.createElement('div');
            col.className = 'col-md-3 col-6 mb-3';
            col.innerHTML = `
                <div class="result-card ${positionClass} animate__animated animate__fadeInUp" style="animation-delay: ${index * 0.1}s">
                    <div class="position-badge">${position}º</div>
                    <div class="circuito-image">
                        <img src="media/circuitos/${this.getImageName(circuito.nombre)}.jpg" 
                             alt="${displayName}"
                             class="img-fluid rounded-top">
                    </div>
                    <div class="circuito-details p-3">
                        <h6 class="fw-bold mb-1">${displayName}</h6>
                        <small class="text-muted d-block mb-2">${circuito.copa_nombre || ''}</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-flag-checkered me-1"></i>
                                Circuito
                            </span>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-star me-1"></i> Favorito
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(col);
        });
        
        // Mostrar sección de resultados
        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });
        
        // Agregar estilos para resultados
        this.addResultStyles();
    }

    static formatCircuitoNombre(circuitoNombre) {
        if (circuitoNombre === 'CanionFerroviario') {
            return 'Cañon Ferroviario';
        }
        return circuitoNombre;
    }
    
    static addResultStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .result-card {
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                transition: all 0.3s;
                position: relative;
            }
            
            .result-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            }
            
            .result-card.gold {
                border: 3px solid #FFD700;
            }
            
            .result-card.silver {
                border: 3px solid #C0C0C0;
            }
            
            .result-card.bronze {
                border: 3px solid #CD7F32;
            }
            
            .position-badge {
                position: absolute;
                top: 10px;
                left: 10px;
                width: 40px;
                height: 40px;
                background: var(--primary-color);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 18px;
                z-index: 2;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            }
            
            .circuito-image {
                height: 150px;
                overflow: hidden;
            }
            
            .circuito-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s;
            }
            
            .result-card:hover .circuito-image img {
                transform: scale(1.1);
            }
        `;
        document.head.appendChild(style);
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
            
            slot.classList.remove('active', 'winner', 'animate__animated', 'animate__pulse');
        });
    }
    
    static resetRuleta() {
        this.stopSlotAnimation();
        this.resetSlots();
        this.isSpinning = false;
        
        const btnGirar = document.getElementById('btnGirar');
        btnGirar.disabled = CircuitosApp?.selectedCircuits?.length < 2;
        btnGirar.innerHTML = '<i class="fas fa-play me-2"></i> GIRAR RULETA';
        
        document.getElementById('resultsSection').style.display = 'none';
        
        this.showAlert('Ruleta reiniciada', 'info');
    }
    
    // Convertir nombre a formato de archivo
    static getImageName(circuitoNombre) {
        if (circuitoNombre === 'Cañon Ferroviario' || circuitoNombre === 'CanionFerroviario') {
            return 'CanionFerroviario';
        }
        return circuitoNombre
            .replace(/[^\w\s]/gi, '')
            .replace(/\s+/g, '')
            .replace(/[()]/g, '');
    }
    
    static createConfetti() {
        // Efecto simple de confetti con elementos HTML
        const confettiContainer = document.createElement('div');
        confettiContainer.style.position = 'fixed';
        confettiContainer.style.top = '0';
        confettiContainer.style.left = '0';
        confettiContainer.style.width = '100%';
        confettiContainer.style.height = '100%';
        confettiContainer.style.pointerEvents = 'none';
        confettiContainer.style.zIndex = '9999';
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'absolute';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.background = this.getRandomColor();
            confetti.style.borderRadius = '50%';
            confetti.style.top = '50%';
            confetti.style.left = '50%';
            confetti.style.animation = `confettiFall ${Math.random() * 1 + 1}s linear forwards`;
            
            confettiContainer.appendChild(confetti);
        }
        
        document.body.appendChild(confettiContainer);
        
        setTimeout(() => {
            confettiContainer.remove();
        }, 2000);
        
        // Agregar animación CSS para confetti
        this.addConfettiAnimation();
    }
    
    static addConfettiAnimation() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes confettiFall {
                0% {
                    transform: translate(0, 0) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translate(${Math.random() * 200 - 100}px, 100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    static getRandomColor() {
        const colors = ['#FF6B6B', '#4ECDC4', '#FFD166', '#06D6A0', '#118AB2', '#EF476F'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    static async guardarEstadisticas() {
        // Solo guardar si hay usuario logueado
        if (!CircuitosApp.isLoggedIn) {
            return;
        }
        
        try {
            // Llamar a la función de CircuitosApp para guardar estadísticas
            await CircuitosApp.guardarEstadisticas(this.winners);
        } catch (error) {
            console.error('Error guardando estadísticas:', error);
        }
    }
}