/* ==========================================================================
   COOKIES APP - FUNCIONES COMPLEMENTARIAS
   ========================================================================== */

   class CookiesApp {
    /* ========== 1. INICIALIZACIÓN ========== */
    static init() {
        this.setupEventListeners();
    }
    
    /* ========== 2. CONFIGURAR EVENT LISTENERS ========== */
    static setupEventListeners() {
        // Añadir efecto de fade out al hacer clic en los botones
        const acceptBtn = document.querySelector('.btn-cookie-accept');
        const rejectBtn = document.querySelector('.btn-cookie-reject');
        const banner = document.getElementById('cookieBanner');
        
        if (acceptBtn) {
            acceptBtn.addEventListener('click', (e) => {
                e.preventDefault();
                banner.classList.add('hide');
                setTimeout(() => {
                    window.location.href = acceptBtn.href;
                }, 500);
            });
        }
        
        if (rejectBtn) {
            rejectBtn.addEventListener('click', (e) => {
                e.preventDefault();
                banner.classList.add('hide');
                setTimeout(() => {
                    window.location.href = rejectBtn.href;
                }, 500);
            });
        }
    }
}

// Inicializar solo si existe el banner
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('cookieBanner')) {
        CookiesApp.init();
    }
});