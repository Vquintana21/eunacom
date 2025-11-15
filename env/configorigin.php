<?php
// ============================================
// CONFIGURACIÓN GLOBAL - EUNACOM
// ============================================

// ENTORNO: 'desarrollo' | 'produccion'
define('ENTORNO', 'desarrollo');

// CONFIGURACIÓN POR ENTORNO
if (ENTORNO === 'desarrollo') {
    // Configuración de DESARROLLO (Test)
    define('BASE_URL', '/test/eunacom/');
    define('MATERIALES_URL', BASE_URL . 'materiales/');
    define('MATERIALES_PATH', $_SERVER['DOCUMENT_ROOT'] . BASE_URL . 'materiales/');
    
    // Base de datos
    define('DB_HOST', 'localhost');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_NAME', '');
    
} else {
    // Configuración de PRODUCCIÓN
    define('BASE_URL', '/');
    define('MATERIALES_URL', '/materiales/');
    define('MATERIALES_PATH', $_SERVER['DOCUMENT_ROOT'] . '/materiales/');
    
    // Base de datos (ajustar para producción)
    define('DB_HOST', 'localhost');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_NAME', '');
}

// ============================================
// FUNCIONES GLOBALES
// ============================================

function buildUrl($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function buildMaterialUrl($relativePath) {
    // Limpiar la ruta relativa (quitar /materiales/ si viene)
    $relativePath = str_replace('/materiales/', '', $relativePath);
    $relativePath = ltrim($relativePath, '/');
    return MATERIALES_URL . $relativePath;
}

function formatBytes($kb) {
    if ($kb < 1024) {
        return number_format($kb, 0) . ' KB';
    } else {
        return number_format($kb / 1024, 2) . ' MB';
    }
}