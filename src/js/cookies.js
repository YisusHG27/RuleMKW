/* ==========================================================================
   COOKIES APP - GESTIÓN DE COOKIES Y CONSENTIMIENTO
   ========================================================================== */

   class CookiesApp {
    /* ========== 1. INICIALIZACIÓN ========== */
    static init() {
        // ===== 1.1. CONFIGURAR EVENT LISTENERS DEL BANNER =====
        this.setupEventListeners();
    }
    
    /* ========== 2. CONFIGURAR EVENT LISTENERS ========== */
    static setupEventListeners() {
        // ===== 2.1. OBTENER BOTÓNES Y BANNER =====
        // Obtener referencias a los elementos del banner de cookies
        const acceptBtn = document.querySelector('.btn-cookie-accept');
        const rejectBtn = document.querySelector('.btn-cookie-reject');
        const banner = document.getElementById('cookieBanner');
        
        // ===== 2.2. BOTÓN ACEPTAR COOKIES =====
        if (acceptBtn) {
            acceptBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // ===== 2.2.1. ANIMAR DESAPARICIÓN DEL BANNER =====
                banner.classList.add('hide');
                // ===== 2.2.2. REDIRIGIR DESPUÉS DE ANIMACIÓN =====
                setTimeout(() => {
                    window.location.href = acceptBtn.href;
                }, 500);
            });
        }
        
        // ===== 2.3. BOTÓN RECHAZAR COOKIES =====
        if (rejectBtn) {
            rejectBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // ===== 2.3.1. ANIMAR DESAPARICIÓN DEL BANNER =====
                banner.classList.add('hide');
                // ===== 2.3.2. REDIRIGIR DESPUÉS DE ANIMACIÓN =====
                setTimeout(() => {
                    window.location.href = rejectBtn.href;
                }, 500);
            });
        }
    }
}

/* ========== 3. INICIALIZAR AL CARGAR DOCUMENTO ========== */
// Inicializar solo si existe el banner de cookies
document.addEventListener('DOMContentLoaded', function() {
    // ===== 3.1. VERIFICAR EXISTENCIA DEL BANNER =====
    if (document.getElementById('cookieBanner')) {
        // ===== 3.2. INICIALIZAR LA APLICACIÓN =====
        CookiesApp.init();
    }
});