<?php
// src/backend/includes/logger.php

// FORZAR ZONA HORARIA DESDE PHP (funciona independientemente del contenedor)
date_default_timezone_set('Europe/Madrid'); // Cambia a tu zona: Europe/Madrid, Europe/London, America/Mexico_City, etc.

$vendorPath = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($vendorPath)) {
    die('Error: Vendor no encontrado. Ejecuta "composer install" en la raíz del proyecto. Ruta buscada: ' . $vendorPath);
}

require_once $vendorPath;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

if (!class_exists('AppLogger')) {
    class AppLogger {
        private static $logger = null;
        
        private static function getLogger() {
            if (self::$logger === null) {
                self::$logger = new MonologLogger('rulemkw');
                
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                
                $logFile = $logDir . '/rulemkw.log';
                $rotatingHandler = new RotatingFileHandler($logFile, 30, MonologLogger::DEBUG);
                
                // Formato personalizado
                $dateFormat = "Y-m-d H:i:s";
                $output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
                $formatter = new LineFormatter($output, $dateFormat);
                $rotatingHandler->setFormatter($formatter);
                
                self::$logger->pushHandler($rotatingHandler);
            }
            return self::$logger;
        }
        
        public static function debug($mensaje, $context = []) {
            self::getLogger()->debug($mensaje, $context);
        }
        
        public static function info($mensaje, $context = []) {
            self::getLogger()->info($mensaje, $context);
        }
        
        public static function warning($mensaje, $context = []) {
            self::getLogger()->warning($mensaje, $context);
        }
        
        public static function error($mensaje, $context = []) {
            self::getLogger()->error($mensaje, $context);
        }
        
        public static function critical($mensaje, $context = []) {
            self::getLogger()->critical($mensaje, $context);
        }
    }
}