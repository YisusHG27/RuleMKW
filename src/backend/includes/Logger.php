<?php
// src/backend/includes/Logger.php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'conexion.php';

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Handler personalizado de Monolog para guardar en BD
 */
class DatabaseHandler extends AbstractProcessingHandler {
    private $enlace;
    
    public function __construct($enlace, $level = MonologLogger::DEBUG, $bubble = true) {
        $this->enlace = $enlace;
        parent::__construct($level, $bubble);
    }
    
    protected function write(array $record): void {
        try {
            // Verificar que la conexión existe
            if (!$this->enlace) {
                return;
            }
            
            $stmt = $this->enlace->prepare("
                INSERT INTO logs_sistema (usuario_id, tipo, accion, descripcion, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                return;
            }
            
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $tipo = $record['level_name'];
            $accion = $this->determinarAccion($record['message']);
            $descripcion = $record['message'];
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Añadir contexto extra si existe
            if (!empty($record['context'])) {
                $descripcion .= " | Contexto: " . json_encode($record['context'], JSON_UNESCAPED_UNICODE);
            }
            
            $stmt->bind_param("isssss", 
                $usuario_id, 
                $tipo, 
                $accion, 
                $descripcion, 
                $ip_address, 
                $user_agent
            );
            
            $stmt->execute();
            $stmt->close();
            
        } catch (Exception $e) {
            // Silenciar errores de BD para no interrumpir la aplicación
        }
    }
    
    private function determinarAccion($mensaje) {
        if (strpos($mensaje, 'login') !== false) return 'LOGIN';
        if (strpos($mensaje, 'registro') !== false) return 'REGISTRO';
        if (strpos($mensaje, 'apuesta') !== false) return 'APUESTA';
        if (strpos($mensaje, 'ruleta') !== false) return 'RULETA';
        if (strpos($mensaje, 'admin') !== false) return 'ADMIN';
        return 'OTRO';
    }
}

/**
 * Clase principal AppLogger
 */
class AppLogger {
    private static $logger = null;
    private static $enlace = null;
    
    /**
     * Inicializar conexión a BD
     */
    public static function init($enlace) {
        self::$enlace = $enlace;
    }
    
    /**
     * Obtener instancia de Monolog
     */
    private static function getLogger() {
        if (self::$logger === null) {
            self::$logger = new MonologLogger('rulemkw');
            
            // Ruta SIMPLE para logs (directamente en la raíz del proyecto)
            $logPath = '/var/www/html/logs/rulemkw.log';
            
            // Verificar que el directorio existe
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                // Intentar crear el directorio
                @mkdir($logDir, 0777, true);
            }
            
            // Solo añadir handler de archivo si podemos escribir
            if (is_writable($logDir) || !file_exists($logDir)) {
                try {
                    $rotatingHandler = new RotatingFileHandler($logPath, 30, MonologLogger::DEBUG);
                    
                    $dateFormat = "Y-m-d H:i:s";
                    $output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
                    $formatter = new LineFormatter($output, $dateFormat);
                    $rotatingHandler->setFormatter($formatter);
                    
                    self::$logger->pushHandler($rotatingHandler);
                } catch (Exception $e) {
                    // Si no podemos escribir en archivo, solo usamos BD
                }
            }
            
            // Handler para BD (si hay conexión)
            if (self::$enlace) {
                try {
                    $dbHandler = new DatabaseHandler(self::$enlace, MonologLogger::INFO);
                    self::$logger->pushHandler($dbHandler);
                } catch (Exception $e) {
                    // Ignorar errores de BD
                }
            }
        }
        
        return self::$logger;
    }
    
    /**
     * Métodos estáticos para logging
     */
    public static function debug($mensaje, $context = []) {
        try {
            self::getLogger()->debug($mensaje, $context);
        } catch (Exception $e) {
            // Fallback silencioso
        }
    }
    
    public static function info($mensaje, $context = []) {
        try {
            self::getLogger()->info($mensaje, $context);
        } catch (Exception $e) {
            // Fallback silencioso
        }
    }
    
    public static function warning($mensaje, $context = []) {
        try {
            self::getLogger()->warning($mensaje, $context);
        } catch (Exception $e) {
            // Fallback silencioso
        }
    }
    
    public static function error($mensaje, $context = []) {
        try {
            self::getLogger()->error($mensaje, $context);
        } catch (Exception $e) {
            // Fallback silencioso
        }
    }
    
    public static function critical($mensaje, $context = []) {
        try {
            self::getLogger()->critical($mensaje, $context);
        } catch (Exception $e) {
            // Fallback silencioso
        }
    }
}