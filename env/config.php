<?php
/**
 * ============================================
 * CONFIGURACIÓN GENERAL DEL SISTEMA
 * ============================================
 * Archivo centralizado de configuración
 * ============================================
 */

// ============================================
// ENTORNO DE EJECUCIÓN
// ============================================
// Valores posibles: 'desarrollo', 'produccion'
define('ENTORNO', 'desarrollo'); // Cambiar a 'produccion' en el servidor real

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'dpimeduchile_eunacom');
define('DB_USER', 'root');
define('DB_PASS', '');

// ============================================
// CONFIGURACIÓN DE URLs
// ============================================
if (ENTORNO === 'desarrollo') {
    // Desarrollo local
    define('BASE_URL', 'https://dpi.med.uchile.cl/test/eunacom/');
    define('MATERIALES_URL', 'https://dpi.med.uchile.cl/test/eunacom/materiales');
} else {
    // Producción
    define('BASE_URL', 'https://eunacom-prep.dpimed.cl/');
    define('MATERIALES_URL', 'https://eunacom-prep.dpimed.cl/materiales');
}

// ============================================
// CONFIGURACIÓN DE SESIONES
// ============================================
define('SESSION_LIFETIME', 86400); // 24 horas en segundos

// ============================================
// CONFIGURACIÓN DE ERRORES
// ============================================
if (ENTORNO === 'desarrollo') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// ============================================
// ZONA HORARIA
// ============================================
date_default_timezone_set('America/Santiago');

// ============================================
// AUTOLOAD DE CLASES
// ============================================
// Autocargar clases desde el directorio /classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Construir URL completa
 * 
 * @param string $path Ruta relativa
 * @return string URL completa
 */
function buildUrl($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Construir URL de materiales
 * 
 * @param string $path Ruta relativa del material
 * @return string URL completa
 */
function buildMaterialUrl($path = '') {
    return MATERIALES_URL . '/' . ltrim($path, '/');
}

/**
 * Redireccionar a una URL
 * 
 * @param string $url URL de destino
 * @param int $code Código HTTP (301, 302, etc)
 */
function redirect($url, $code = 302) {
    header("Location: " . $url, true, $code);
    exit;
}

/**
 * Escapar HTML para prevenir XSS
 * 
 * @param string $string Cadena a escapar
 * @return string Cadena escapada
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear bytes a formato legible (KB, MB, GB)
 * 
 * @param int $bytes Tamaño en bytes
 * @param int $precision Decimales a mostrar
 * @return string Tamaño formateado
 */
function formatBytes($bytes, $precision = 2) {
    $bytes = (int)$bytes;
    
    if ($bytes <= 0) {
        return '0 B';
    }
    
    $units = array('KB', 'MB', 'GB', 'TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    
    if ($factor >= count($units)) {
        $factor = count($units) - 1;
    }
    
    return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
}

/**
 * Obtener conexión a la base de datos (SINGLETON)
 * 
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

// ============================================
// CONSTANTES ADICIONALES
// ============================================
define('SITE_NAME', 'EUNACOM Preparación');
define('SITE_VERSION', '1.0.0');
define('MAINTENANCE_MODE', false); // Activar para modo mantenimiento