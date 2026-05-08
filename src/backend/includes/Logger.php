<?php
/* ==========================================================================
   LOGGER.PHP - SISTEMA DE LOGGING CON MONOLOG
   ========================================================================== */

/* ========== 1. CONFIGURACIÓN ========== */
// ===== 1.1. ESTABLECER ZONA HORARIA =====
date_default_timezone_set('Europe/Madrid');

// ===== 1.2. RUTA AL AUTOLOAD DE COMPOSER =====
$vendorPath = __DIR__ . '/../../vendor/autoload.php';

/* ========== 2. VALIDAR DEPENDENCIAS ========== */
// ===== 2.1. VERIFICAR SI EXISTE COMPOSER AUTOLOAD =====
if (!file_exists($vendorPath)) {
    die('Error: Vendor no encontrado. Ejecuta "composer install" en la raíz del proyecto. Ruta buscada: ' . $vendorPath);
}

// ===== 2.2. INCLUIR COMPOSER AUTOLOAD =====
require_once $vendorPath;

/* ========== 3. USAR MONOLOG =====*/
// ===== 3.1. IMPORTAR CLASES DE MONOLOG =====
use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

/* ========== 4. CLASE DE LOGGING ========== */
// ===== 4.1. DEFINIR CLASE APPLOGGER =====
if (!class_exists('AppLogger')) {
    class AppLogger {
        // ===== 4.1.1. PROPIEDAD ESTÁTICA PARA LOGGER =====
        private static $logger = null;
        
        /* ========== 5. OBTENER INSTANCIA DE LOGGER ========== */
        /**
         * ===== 5.1. getLogger() =====
         * Obtiene o crea la instancia única de Monolog Logger
         * @return MonologLogger Instancia del logger
         */
        private static function getLogger() {
            if (self::$logger === null) {
                // ===== 5.1.1. CREAR INSTANCIA DE MONOLOG =====
                self::$logger = new MonologLogger('rulemkw');
                
                // ===== 5.1.2. CREAR DIRECTORIO DE LOGS =====
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                
                // ===== 5.1.3. CONFIGURAR HANDLER DE ARCHIVO ROTATORIO =====
                $logFile = $logDir . '/rulemkw.log';
                $rotatingHandler = new RotatingFileHandler($logFile, 30, MonologLogger::DEBUG);
                
                // ===== 5.1.4. CONFIGURAR FORMATO DE LOG =====
                $dateFormat = "Y-m-d H:i:s";
                $output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
                $formatter = new LineFormatter($output, $dateFormat);
                $rotatingHandler->setFormatter($formatter);
                
                // ===== 5.1.5. AGREGAR HANDLER AL LOGGER =====
                self::$logger->pushHandler($rotatingHandler);
            }
            return self::$logger;
        }
        
        /* ========== 6. MÉTODOS DE LOGGING ========== */
        /**
         * ===== 6.1. debug() =====
         * Registra mensaje de depuración
         * @param string $mensaje Mensaje a registrar
         * @param array $context Contexto adicional
         */
        public static function debug($mensaje, $context = []) {
            self::getLogger()->debug($mensaje, $context);
        }
        
        /**
         * ===== 6.2. info() =====
         * Registra mensaje informativo
         * @param string $mensaje Mensaje a registrar
         * @param array $context Contexto adicional
         */
        public static function info($mensaje, $context = []) {
            self::getLogger()->info($mensaje, $context);
        }
        
        /**
         * ===== 6.3. warning() =====
         * Registra mensaje de advertencia
         * @param string $mensaje Mensaje a registrar
         * @param array $context Contexto adicional
         */
        public static function warning($mensaje, $context = []) {
            self::getLogger()->warning($mensaje, $context);
        }
        
        /**
         * ===== 6.4. error() =====
         * Registra mensaje de error
         * @param string $mensaje Mensaje a registrar
         * @param array $context Contexto adicional
         */
        public static function error($mensaje, $context = []) {
            self::getLogger()->error($mensaje, $context);
        }
        
        /**
         * ===== 6.5. critical() =====
         * Registra mensaje crítico
         * @param string $mensaje Mensaje a registrar
         * @param array $context Contexto adicional
         */
        public static function critical($mensaje, $context = []) {
            self::getLogger()->critical($mensaje, $context);
        }
        
        /**
         * ===== 6.6. cookie() =====
         * Registra evento de consentimiento de cookies
         * @param string $accion Acción realizada
         * @param array $context Contexto adicional
         */
        public static function cookie($accion, $context = []) {
            $mensaje = "Cookie consent: " . $accion;
            self::getLogger()->info($mensaje, $context);
        }
    }
}