<?php
/**
 * DEBUG - Verificar estado de Giftia
 * Acceder a: https://tu-dominio.com/wp-content/plugins/giftfinder-core/debug.php
 */

// Suppress errors initially
@ini_set('display_errors', 0);
error_reporting(E_ALL);

// Output en JSON
header('Content-Type: application/json; charset=UTF-8');

$debug = ['status' => 'initializing', 'errors' => []];

try {
    // Cargar WordPress - intentar múltiples rutas
    $wp_loaded = false;
    $paths_tried = [];
    
    $possible_paths = [
        dirname(__FILE__) . '/../../../../wp-load.php',
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../wp-load.php',
        $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
    ];
    
    foreach ($possible_paths as $path) {
        $paths_tried[] = $path;
        if (file_exists($path)) {
            require_once $path;
            if (function_exists('get_option')) {
                $wp_loaded = true;
                break;
            }
        }
    }
    
    $debug['wordpress_load'] = $wp_loaded ? 'SUCCESS' : 'FAILED';
    $debug['paths_tried'] = $paths_tried;
    
    if (!$wp_loaded) {
        throw new Exception('No se pudo cargar WordPress. Verifique que wp-load.php existe en las rutas buscadas.');
    }
    
    // 1. Verificar post type
    $debug['post_type_registered'] = post_type_exists('gf_gift');
    
    // 2. Contar productos
    $products = get_posts([
        'post_type' => 'gf_gift',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ]);
    $debug['total_products'] = count($products);
    $debug['products'] = [];
    foreach ($products as $p) {
        $debug['products'][] = [
            'ID' => $p->ID,
            'title' => $p->post_title,
            'status' => $p->post_status,
            'date' => $p->post_date
        ];
    }
    
    // 3. Verificar tablas
    global $wpdb;
    $debug['database'] = [];
    $debug['database']['prefix'] = $wpdb->prefix;
    
    $table_ai = $wpdb->prefix . 'gf_products_ai';
    $table_offers = $wpdb->prefix . 'gf_affiliate_offers';
    $table_logs = $wpdb->prefix . 'gf_price_logs';
    
    // Verificar tabla AI
    $ai_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_ai'");
    $debug['tables']['gf_products_ai'] = $ai_exists ? 'EXISTS' : 'NOT FOUND';
    
    if ($ai_exists) {
        $ai_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_ai");
        $debug['tables']['gf_products_ai_count'] = $ai_count;
    }
    
    // Verificar tabla offers
    $offers_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_offers'");
    $debug['tables']['gf_affiliate_offers'] = $offers_exists ? 'EXISTS' : 'NOT FOUND';
    
    if ($offers_exists) {
        $offers_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_offers");
        $debug['tables']['gf_affiliate_offers_count'] = $offers_count;
    }
    
    // Verificar tabla logs
    $logs_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_logs'");
    $debug['tables']['gf_price_logs'] = $logs_exists ? 'EXISTS' : 'NOT FOUND';
    
    // 4. Verificar taxonomías
    $debug['taxonomies'] = [
        'gf_interest' => taxonomy_exists('gf_interest'),
        'gf_recipient' => taxonomy_exists('gf_recipient'),
        'gf_occasion' => taxonomy_exists('gf_occasion'),
        'gf_budget' => taxonomy_exists('gf_budget'),
    ];
    
    // 5. Verificar envs
    $wp_token = getenv('WP_API_TOKEN');
    $gemini_key = getenv('GEMINI_API_KEY');
    $amazon_tag = getenv('AMAZON_TAG');
    
    // Fallback a wp_options
    if (!$wp_token) {
        $wp_token = get_option('gf_wp_api_token', false);
    }
    if (!$gemini_key) {
        $gemini_key = get_option('gf_gemini_api_key', false);
    }
    if (!$amazon_tag) {
        $amazon_tag = get_option('gf_amazon_tag', false);
    }
    
    $debug['env_vars'] = [
        'WP_API_TOKEN' => $wp_token ? '***SET (via env or options)***' : 'NOT SET',
        'GEMINI_API_KEY' => $gemini_key ? '***SET (via env or options)***' : 'NOT SET',
        'AMAZON_TAG' => $amazon_tag ? $amazon_tag : 'NOT SET',
    ];
    
    // 6. Verificar archivos del plugin
    $debug['plugin_files'] = [
        'giftfinder-core.php' => file_exists(dirname(__FILE__) . '/giftfinder-core.php'),
        'config/giftia-config.php' => file_exists(dirname(__FILE__) . '/config/giftia-config.php'),
        'includes/giftia-utils.php' => file_exists(dirname(__FILE__) . '/includes/giftia-utils.php'),
        'includes/env-loader.php' => file_exists(dirname(__FILE__) . '/includes/env-loader.php'),
        'install.php' => file_exists(dirname(__FILE__) . '/install.php'),
    ];
    
    // 7. Verificar .env file
    $env_paths = [
        dirname(__FILE__) . '/.env',
        dirname(__FILE__) . '/../../.env',
        dirname(__FILE__) . '/../../../.env',
        $_SERVER['DOCUMENT_ROOT'] . '/.env',
    ];
    
    $debug['env_file'] = [];
    foreach ($env_paths as $path) {
        $debug['env_file'][$path] = file_exists($path) ? 'EXISTS' : 'NOT FOUND';
    }
    
    $debug['status'] = 'OK';

} catch (Exception $e) {
    $debug['status'] = 'ERROR';
    $debug['error_message'] = $e->getMessage();
    $debug['error_trace'] = $e->getTraceAsString();
}

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
