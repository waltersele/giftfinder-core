<?php
/**
 * GIFTIA ENV LOADER
 * Carga variables de entorno desde .env file
 * Se incluye en wp-config.php ANTES de cargar WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cargar .env file desde la raíz del sitio
 * Soporta tanto raíz de WordPress como raíz de plugin
 */
function giftia_load_env_file() {
    // Intentar varias ubicaciones
    $possible_paths = [
        dirname(dirname(__FILE__)) . '/.env',           // giftfinder-core/../.env
        dirname(dirname(dirname(__FILE__))) . '/.env',  // wp-content/../.env
        dirname(dirname(dirname(dirname(__FILE__)))) . '/.env', // www/.env
        $_SERVER['DOCUMENT_ROOT'] . '/.env',
        '/var/www/html/.env',
    ];

    $env_file = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path) && is_readable($path)) {
            $env_file = $path;
            break;
        }
    }

    if (!$env_file) {
        // Sin .env, usar valores por defecto
        return;
    }

    // Leer líneas del .env
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (!$lines) {
        return;
    }

    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remover comillas si existen
        if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
            (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
            $value = substr($value, 1, -1);
        }

        // No sobrescribir si ya existe
        if (!getenv($key)) {
            putenv("$key=$value");
        }
        
        // También definir como constante de PHP para fácil acceso
        if (!defined("GIFTIA_" . strtoupper($key))) {
            define("GIFTIA_" . strtoupper($key), $value);
        }
    }
}

// Cargar .env file
giftia_load_env_file();

/**
 * Función helper para obtener variables de entorno
 * Con fallback a constante de WordPress
 */
function giftia_env($key, $default = null) {
    // Primero intentar desde putenv (del .env file)
    $value = getenv($key);
    
    // Si no existe, intentar constante GIFTIA_
    if ($value === false) {
        $const_name = 'GIFTIA_' . strtoupper($key);
        if (defined($const_name)) {
            $value = constant($const_name);
        }
    }
    
    // Si aún no existe, intentar desde opciones de WordPress (guardado en Admin)
    if ($value === false && function_exists('get_option')) {
        $option_key = 'gf_' . strtolower($key);
        $wp_value = get_option($option_key);
        if ($wp_value !== false && $wp_value !== '') {
            $value = $wp_value;
        }
    }
    
    // Si aún no existe, retornar default
    if ($value === false || $value === '') {
        return $default;
    }
    
    // Parsear booleanos
    if (strtolower($value) === 'true') {
        return true;
    }
    if (strtolower($value) === 'false') {
        return false;
    }
    
    return $value;
}
