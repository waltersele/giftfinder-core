<?php
/**
 * GIFTIA SECURITY & UTILITIES MODULE
 * 
 * Funciones reutilizables para:
 * - Validación de entrada
 * - Manejo de errores
 * - Logging
 * - Rate limiting
 * 
 * Ubicación: wp-content/plugins/giftfinder-core/includes/giftia-utils.php
 * Incluir en: giftfinder-core.php con: require_once plugin_dir_path(__FILE__) . 'includes/giftia-utils.php';
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ============================================================
// LOGGING CENTRALIZADO
// ============================================================

function gf_log($message, $level = 'info', $data = []) {
    $log_file = WP_CONTENT_DIR . '/giftia-debug.log';
    $timestamp = current_time('Y-m-d H:i:s');
    
    // Formatear mensaje
    $log_msg = "[$timestamp][$level] $message";
    
    if(!empty($data)) {
        $log_msg .= " | DATA: " . json_encode($data);
    }
    
    // Escribir en log
    error_log($log_msg . "\n", 3, $log_file);
    
    // Si es error crítico, también a syslog de WP
    if($level === 'error' || $level === 'critical') {
        error_log("GIFTIA: " . $log_msg);
    }
}

// ============================================================
// RATE LIMITING
// ============================================================

function gf_check_rate_limit($identifier, $max_requests = 100, $window_seconds = 3600) {
    $cache_key = "gf_ratelimit_" . sanitize_key($identifier);
    $current_count = (int) get_transient($cache_key);
    
    if($current_count >= $max_requests) {
        gf_log("Rate limit exceeded for: $identifier", 'warning');
        return false;
    }
    
    set_transient($cache_key, $current_count + 1, $window_seconds);
    return true;
}

function gf_reset_rate_limit($identifier) {
    $cache_key = "gf_ratelimit_" . sanitize_key($identifier);
    delete_transient($cache_key);
}

// ============================================================
// VALIDACIÓN DE ASIN
// ============================================================

function gf_is_valid_asin($asin) {
    // ASINs son strings de 10 caracteres alfanuméricos
    if(empty($asin) || strlen($asin) !== 10) {
        return false;
    }
    
    // Validar que sean caracteres válidos
    if(!preg_match('/^[A-Z0-9]{10}$/', $asin)) {
        return false;
    }
    
    return true;
}

// ============================================================
// VALIDACIÓN DE PRECIO
// ============================================================

function gf_parse_price($price_string) {
    // Limpiar: "25,99€" o "25.99" o "25" -> 25.99
    $cleaned = preg_replace('/[^0-9.,]/', '', $price_string);
    $cleaned = str_replace(',', '.', $cleaned);
    
    $parsed = (float) $cleaned;
    
    // Validar rango razonable (0.01€ a 99,999€)
    if($parsed < 0.01 || $parsed > 99999) {
        return false;
    }
    
    return $parsed;
}

function gf_is_valid_price($price_string) {
    return gf_parse_price($price_string) !== false;
}

// ============================================================
// VALIDACIÓN DE URL
// ============================================================

function gf_is_valid_affiliate_url($url) {
    // Debe ser HTTPS y empezar con https://
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    if(strpos($url, 'https://') !== 0) {
        gf_log("Non-HTTPS URL attempted: $url", 'warning');
        return false;
    }
    
    return true;
}

// ============================================================
// VALIDACIÓN DE IMAGEN URL
// ============================================================

function gf_is_valid_image_url($url) {
    if(empty($url)) {
        return false;
    }
    
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Verificar extensión o Content-Type
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
    
    // Si tiene extensión, validar
    if(!empty($extension) && !in_array($extension, $allowed_extensions)) {
        return false;
    }
    
    return true;
}

// ============================================================
// SANITIZACIÓN DE TÍTULO
// ============================================================

function gf_sanitize_title($title) {
    // Remover caracteres especiales peligrosos
    $title = sanitize_text_field($title);
    
    // Trimear y limitar longitud
    $title = trim($title);
    $title = substr($title, 0, 255); // MySQL VARCHAR max
    
    // Si quedó vacío, retornar "Sin Título"
    if(empty($title)) {
        return "Sin Título";
    }
    
    return $title;
}

// ============================================================
// VALIDACIÓN DE CORS
// ============================================================

function gf_validate_cors($allowed_origins = []) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Si no hay allowed_origins especificado, rechazar
    if(empty($allowed_origins)) {
        $allowed_origins = [
            home_url(),
            'https://giftia.es',
            'https://www.giftia.es',
        ];
    }
    
    // Validar origen
    if(!in_array($origin, $allowed_origins)) {
        gf_log("CORS rejected for origin: $origin", 'warning');
        return false;
    }
    
    // Si pasó, permitir
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, X-GIFTIA-TOKEN");
    
    return true;
}

// ============================================================
// VALIDACIÓN DE TOKEN
// ============================================================

function gf_validate_token($incoming_token) {
    $stored_token = get_option('gf_ingest_secret_token');
    
    // Usar hash_equals para evitar timing attacks
    if(empty($stored_token) || !hash_equals($stored_token, $incoming_token)) {
        gf_log("Token validation failed", 'error');
        return false;
    }
    
    return true;
}

// ============================================================
// GENERACIÓN SEGURA DE TOKEN
// ============================================================

function gf_generate_secure_token($length = 32) {
    // Usar random_bytes para máxima entropía
    if(function_exists('random_bytes')) {
        return bin2hex(random_bytes($length / 2));
    } else {
        // Fallback para PHP < 7
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    }
}

// ============================================================
// VALIDACIÓN DE TABLA EXISTE
// ============================================================

function gf_table_exists($table_name) {
    global $wpdb;
    return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
}

// ============================================================
// FUNCIÓN SEGURA DE BÚSQUEDA
// ============================================================

function gf_search_products($args = []) {
    global $wpdb;
    
    // Validar tabla existe
    $table_offers = $wpdb->prefix . 'gf_affiliate_offers';
    if(!gf_table_exists($table_offers)) {
        gf_log("Tabla no existe: $table_offers", 'error');
        return new WP_Query(['posts_per_page' => 0]); // Retornar query vacía
    }
    
    $base_args = [
        'post_type' => 'gf_gift',
        'posts_per_page' => 20,
        'post_status' => ['publish', 'draft'],
    ];
    
    $final_args = array_merge($base_args, $args);
    
    // Aplicar caché si es búsqueda estándar
    $cache_key = 'gf_search_' . md5(json_encode($final_args));
    $cached = get_transient($cache_key);
    
    if($cached !== false) {
        gf_log("Cache hit for search", 'debug');
        return $cached;
    }
    
    $query = new WP_Query($final_args);
    
    // Cachear por 1 hora
    set_transient($cache_key, $query, HOUR_IN_SECONDS);
    
    return $query;
}

// ============================================================
// OBTENER OFERTAS DE UN PRODUCTO
// ============================================================

function gf_get_product_offers($post_id) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'gf_affiliate_offers';
    
    $offers = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE post_id = %d AND is_active = 1 ORDER BY price ASC",
        $post_id
    ));
    
    if($wpdb->last_error) {
        gf_log("Error fetching offers: " . $wpdb->last_error, 'error');
        return [];
    }
    
    return $offers;
}

// ============================================================
// FUNCIÓN PARA ELEGIR MEJOR OFERTA
// ============================================================

function gf_choose_best_offer($offers, $time_mode = 'any') {
    global $GIFTIA_FAST_VENDORS;
    
    if(empty($offers)) {
        return null;
    }
    
    // Si es inmediato, cualquiera vale
    if($time_mode === 'immed') {
        return $offers[0]; // Ya está ordenada por precio ASC
    }
    
    // Si es rápido, buscar entre fast vendors
    if($time_mode === 'fast') {
        foreach($offers as $offer) {
            $vendor_lower = strtolower($offer->vendor_name);
            foreach($GIFTIA_FAST_VENDORS as $fast_vendor) {
                if(strpos($vendor_lower, $fast_vendor) !== false) {
                    return $offer;
                }
            }
        }
        // Si no encuentra fast vendor, devolver la más barata
        return $offers[0];
    }
    
    // Cualquier otra cosa, devolver la más barata
    return $offers[0];
}

// ============================================================
// CACHÉ CON CLAVE INTELIGENTE
// ============================================================

function gf_cache_set($key, $value, $ttl = HOUR_IN_SECONDS) {
    $safe_key = 'gf_' . sanitize_key($key);
    set_transient($safe_key, $value, $ttl);
}

function gf_cache_get($key) {
    $safe_key = 'gf_' . sanitize_key($key);
    return get_transient($safe_key);
}

function gf_cache_delete($key) {
    $safe_key = 'gf_' . sanitize_key($key);
    delete_transient($safe_key);
}

// ============================================================
// RESPUESTA JSON SEGURA
// ============================================================

function gf_json_response($data, $http_code = 200) {
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    wp_die();
}

// ============================================================
// ERROR HANDLER CENTRALIZADO
// ============================================================

function gf_error_handler($error_code, $message, $data = []) {
    $errors = [
        'token_invalid'     => ['code' => 403, 'message' => 'Token inválido'],
        'rate_limit'        => ['code' => 429, 'message' => 'Límite de solicitudes excedido'],
        'invalid_asin'      => ['code' => 400, 'message' => 'ASIN inválido'],
        'invalid_price'     => ['code' => 400, 'message' => 'Precio inválido'],
        'invalid_vendor'    => ['code' => 400, 'message' => 'Vendor no permitido'],
        'internal_error'    => ['code' => 500, 'message' => 'Error interno del servidor'],
    ];
    
    $error = $errors[$error_code] ?? ['code' => 500, 'message' => 'Error desconocido'];
    $error['custom_message'] = $message;
    
    gf_log("Error [$error_code]: $message", 'error', $data);
    
    gf_json_response($error, $error['code']);
}
