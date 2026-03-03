/* ==========================================================================
   COOKIES APP - GESTIÓN DEL BANNER DE COOKIES
   ========================================================================== */

class CookiesApp {
    /* ========== 1. PROPIEDADES ESTÁTICAS ========== */
    static cookieName = 'cookie_consent';
    static cookieDays = 365;
    
    /* ========== 2. INICIALIZACIÓN ========== */
    static init() {
        // Verificar si ya existe la cookie
        if (!this.hasCookie()) {
            this.createBanner();
        }
    }
    
    /* ========== 3. VERIFICACIÓN DE COOKIE ========== */
    static hasCookie() {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === this.cookieName) {
                return true;
            }
        }
        return false;
    }
    
    /* ========== 4. CREAR BANNER ========== */
    static createBanner() {
        // Crear elementos del banner
        const banner = document.createElement('div');
        banner.id = 'cookieBanner';
        banner.className = 'cookie-banner';
        
        banner.innerHTML = `
            <div class="cookie-content">
                <div class="cookie-icon">
                    <i class="fas fa-cookie-bite"></i>
                </div>
                <div class="cookie-text">
                    <h4>🍪 Uso de cookies</h4>
                    <p>Utilizamos cookies propias y de terceros para mejorar tu experiencia en RuleMKW. 
                       Al hacer clic en "Aceptar", consientes el uso de todas las cookies. 
                       Puedes obtener más información en nuestras 
                       <a href="#" id="downloadPoliticas" class="cookie-link">políticas de privacidad</a>.</p>
                </div>
                <div class="cookie-buttons">
                    <button id="acceptCookies" class="btn-cookie-accept">
                        <i class="fas fa-check"></i> Aceptar
                    </button>
                    <button id="rejectCookies" class="btn-cookie-reject">
                        <i class="fas fa-times"></i> Rechazar
                    </button>
                </div>
            </div>
        `;
        
        // Añadir al body
        document.body.appendChild(banner);
        
        // Configurar event listeners
        this.setupEventListeners();
    }
    
    /* ========== 5. CONFIGURAR EVENT LISTENERS ========== */
    static setupEventListeners() {
        const banner = document.getElementById('cookieBanner');
        const acceptBtn = document.getElementById('acceptCookies');
        const rejectBtn = document.getElementById('rejectCookies');
        const downloadLink = document.getElementById('downloadPoliticas');
        
        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => this.acceptCookies(banner));
        }
        
        if (rejectBtn) {
            rejectBtn.addEventListener('click', () => this.rejectCookies(banner));
        }
        
        if (downloadLink) {
            downloadLink.addEventListener('click', (e) => this.downloadPoliticas(e));
        }
    }
    
    /* ========== 6. ACEPTAR COOKIES ========== */
    static acceptCookies(banner) {
        this.setCookie('accepted');
        this.hideBanner(banner);
        
        // ===== NUEVO: ENVIAR LOG AL SERVIDOR =====
        this.sendLogToServer('aceptadas');
    }
    
    /* ========== 7. RECHAZAR COOKIES ========== */
    static rejectCookies(banner) {
        this.setCookie('rejected');
        this.hideBanner(banner);
        
        // ===== NUEVO: ENVIAR LOG AL SERVIDOR =====
        this.sendLogToServer('rechazadas');
        
        console.log('Cookies no esenciales rechazadas');
    }
    
    /* ========== 8. ENVIAR LOG AL SERVIDOR ========== */
    static sendLogToServer(accion) {
        // Obtener información del navegador
        const userAgent = navigator.userAgent;
        const screenWidth = window.screen.width;
        const screenHeight = window.screen.height;
        const language = navigator.language;
        
        // Crear datos para el log
        const logData = {
            accion: accion,
            user_agent: userAgent,
            screen_resolution: `${screenWidth}x${screenHeight}`,
            language: language,
            timestamp: new Date().toISOString()
        };
        
        // Enviar al servidor (usando fetch con beacon para no bloquear)
        if (navigator.sendBeacon) {
            // Usar Beacon para enviar incluso si la página se cierra
            const blob = new Blob([JSON.stringify(logData)], { type: 'application/json' });
            navigator.sendBeacon('backend/api/log_cookie.php', blob);
        } else {
            // Fallback con fetch
            fetch('backend/api/log_cookie.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(logData),
                keepalive: true // Importante para que se envíe aunque se cierre la página
            }).catch(error => console.error('Error enviando log:', error));
        }
    }
    
    /* ========== 9. ESTABLECER COOKIE ========== */
    static setCookie(value) {
        const date = new Date();
        date.setTime(date.getTime() + (this.cookieDays * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = `${this.cookieName}=${value};${expires};path=/`;
    }
    
    /* ========== 10. OCULTAR BANNER ========== */
    static hideBanner(banner) {
        banner.classList.add('hide');
        setTimeout(() => {
            if (banner && banner.parentNode) {
                banner.parentNode.removeChild(banner);
            }
        }, 500);
    }
    
    /* ========== 11. DESCARGAR POLÍTICAS ========== */
    static downloadPoliticas(e) {
        e.preventDefault();
        
        // Intentar con diferentes rutas posibles
        const rutas = [
            'media/politicas_privacidad.pdf',
            '../media/politicas_privacidad.pdf',
            '/media/politicas_privacidad.pdf'
        ];
        
        // Probar la primera ruta
        const link = document.createElement('a');
        link.href = rutas[0];
        link.download = 'politicas_privacidad_rulemkw.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Mostrar mensaje si el archivo no existe
        setTimeout(() => {
            if (link.href.endsWith('.pdf')) {
                console.log('Descargando políticas de privacidad...');
            }
        }, 100);
    }
}

/* ========== 12. INICIALIZAR AUTOMÁTICAMENTE ========== */
document.addEventListener('DOMContentLoaded', function() {
    CookiesApp.init();
});