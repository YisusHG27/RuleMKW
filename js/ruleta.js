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
    
    // ... (el resto del código se mantiene igual) ...
    
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

window.RuletaApp = RuletaApp;