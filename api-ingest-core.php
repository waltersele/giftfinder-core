<?php
/**
 * GIFTIA API INGEST CORE - v6.1
 * Lógica de procesamiento de productos
 * Sin dependencias de carga de WordPress (asume ya cargado)
 */

// Usar los datos de $_POST o $data (ya decodificados como JSON)
$data = isset($data) ? $data : $_POST;

// === VALIDAR TOKEN (contra timing attacks) ===
$incoming_token = $_SERVER['HTTP_X_GIFTIA_TOKEN'] ?? '';
$stored_token = get_option('gf_wp_api_token', '');

if (empty($stored_token)) {
    // Primera ejecución: generar y guardar token
    $stored_token = bin2hex(random_bytes(16));
    update_option('gf_wp_api_token', $stored_token);
    error_log('[GIFTIA-API] Token generado automáticamente: ' . substr($stored_token, 0, 10) . '...');
}

if (empty($incoming_token) || !hash_equals($stored_token, $incoming_token)) {
    error_log('[GIFTIA-API] Token inválido desde ' . $_SERVER['REMOTE_ADDR']);
    http_response_code(403);
    die(json_encode(['error' => 'Token inválido']));
}

// === RATE LIMIT ===
$client_ip = $_SERVER['REMOTE_ADDR'];
if (!gf_check_rate_limit($client_ip, 100, 3600)) {
    error_log('[GIFTIA-API] Rate limit excedido para ' . $client_ip);
    http_response_code(429);
    die(json_encode(['error' => 'Rate limit exceeded']));
}

error_log('[GIFTIA-API] Solicitud recibida: ' . $_SERVER['REQUEST_METHOD'] . ' desde ' . $_SERVER['REMOTE_ADDR']);
error_log('[GIFTIA-API] Datos recibidos: ' . substr(json_encode($data), 0, 200) . '...');

if (!$data) {
    error_log('[GIFTIA-API] ERROR: JSON inválido');
    http_response_code(400);
    die(json_encode(['error' => 'JSON inválido']));
}

// Validaciones básicas
if (empty($data['asin'])) {
    error_log('[GIFTIA-API] ERROR: ASIN faltante');
    http_response_code(400);
    die(json_encode(['error' => 'ASIN requerido']));
}

if (!gf_is_valid_asin($data['asin'])) {
    error_log('[GIFTIA-API] ERROR: ASIN inválido: ' . $data['asin']);
    http_response_code(400);
    die(json_encode(['error' => 'ASIN inválido (debe ser 10 caracteres)']));
}

if (!gf_is_valid_price($data['price'] ?? '0')) {
    error_log('[GIFTIA-API] ERROR: Precio inválido: ' . $data['price']);
    http_response_code(400);
    die(json_encode(['error' => 'Precio inválido']));
}

// === LIMPIAR DATOS ===
$price = gf_parse_price($data['price'] ?? '0');
$title_clean = gf_sanitize_title($data['title']);
$vendor = gf_validate_vendor($data['vendor'] ?? 'amazon');
$image_url = gf_is_valid_image_url($data['image_url'] ?? '') ? ($data['image_url'] ?? '') : '';
$affiliate_url = gf_is_valid_affiliate_url($data['affiliate_url'] ?? '') ? ($data['affiliate_url'] ?? '') : '';

error_log('[GIFTIA-API] Datos limpios: title=' . $title_clean . ', asin=' . $data['asin'] . ', price=' . $price);

// === BUSCAR O CREAR PRODUCTO ===
global $wpdb;
$table_ai = $wpdb->prefix . 'gf_products_ai';

// Verificar tabla existe
if ($wpdb->get_var("SHOW TABLES LIKE '$table_ai'") !== $table_ai) {
    error_log('[GIFTIA-API] Tabla no existe, creando...');
    gf_create_custom_tables();
}

// Verificar post type
if (!post_type_exists('gf_gift')) {
    error_log('[GIFTIA-API] Post type gf_gift no existe, registrando...');
    do_action('init');
}

$existing = $wpdb->get_row($wpdb->prepare(
    "SELECT post_id FROM $table_ai WHERE asin = %s",
    $data['asin']
));

if ($existing) {
    error_log('[GIFTIA-API] Producto existe, resucitando: ' . $existing->post_id);
    $post_id = $existing->post_id;
    // Resucitar si está en papelera
    wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
} else {
    error_log('[GIFTIA-API] Creando producto nuevo...');
    // Crear nuevo
    $post_id = wp_insert_post([
        'post_title' => $title_clean,
        'post_content' => $data['description'] ?? '',
        'post_excerpt' => substr($title_clean, 0, 160),
        'post_status' => 'publish',
        'post_type' => 'gf_gift'
    ]);
    
    if (is_wp_error($post_id)) {
        error_log('[GIFTIA-API] ERROR al crear post: ' . $post_id->get_error_message());
        http_response_code(500);
        die(json_encode(['error' => 'Error creando post: ' . $post_id->get_error_message()]));
    }
    
    error_log('[GIFTIA-API] Producto creado: ' . $post_id);
    
    // Registrar en tabla IA
    $insert_result = $wpdb->insert($table_ai, [
        'post_id' => $post_id,
        'asin' => $data['asin'],
        'affiliate_network' => 'amazon'
    ]);
    
    if ($insert_result === false) {
        error_log('[GIFTIA-API] ERROR insertando en tabla AI: ' . $wpdb->last_error);
    } else {
        error_log('[GIFTIA-API] Registro AI insertado para post ' . $post_id);
    }
    
    // Importar imagen
    if (!empty($image_url)) {
        error_log('[GIFTIA-API] Descargando imagen: ' . substr($image_url, 0, 50) . '...');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $img_id = media_sideload_image($image_url, $post_id, null, 'id');
        if (!is_wp_error($img_id)) {
            set_post_thumbnail($post_id, $img_id);
            error_log('[GIFTIA-API] Thumbnail establecido: ' . $img_id);
        } else {
            error_log('[GIFTIA-API] ERROR descargando imagen: ' . $img_id->get_error_message());
        }
    }
}

// === ACTUALIZAR OFERTAS ===
$table_offers = $wpdb->prefix . 'gf_affiliate_offers';
$wpdb->delete($table_offers, [
    'post_id' => $post_id,
    'vendor_name' => $vendor
]);

$wpdb->insert($table_offers, [
    'post_id' => $post_id,
    'vendor_name' => $vendor,
    'affiliate_url' => $affiliate_url,
    'price' => $price,
    'is_active' => 1,
    'last_update' => current_time('mysql')
]);

// === CLASIFICACIÓN AUTOMÁTICA ===
$vibes = gf_classify_product($title_clean);
foreach ($vibes as $v) {
    if (!term_exists($v, 'gf_interest')) {
        wp_insert_term($v, 'gf_interest');
    }
    wp_set_object_terms($post_id, $v, 'gf_interest', true);
}

// Presupuesto
$budget = gf_get_budget_by_price($price);
if (!term_exists($budget, 'gf_budget')) {
    wp_insert_term($budget, 'gf_budget');
}
wp_set_object_terms($post_id, $budget, 'gf_budget');

// Recipients
$recips = gf_classify_recipient($title_clean);
foreach ($recips as $r) {
    if (!term_exists($r, 'gf_recipient')) {
        wp_insert_term($r, 'gf_recipient');
    }
    wp_set_object_terms($post_id, $r, 'gf_recipient', true);
}

// === GENERAR CONTENIDO IA (Con fallback) ===
$post_obj = get_post($post_id);

if (empty($post_obj->post_content) || strlen($post_obj->post_content) < 50) {
    $gemini_key = giftia_env('GEMINI_API_KEY');
    
    if (!empty($gemini_key)) {
        $prompt = "Producto: '$title_clean' ($price EUR). Escribe JSON con: 1. 'titulo_viral' (max 60 chars), 2. 'descripcion_seo' (100 palabras persuasivas), 3. 'por_que_elegido' (50 palabras). SOLO JSON.";
        
        $response = wp_remote_post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$gemini_key",
            [
                'body' => json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]]),
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 15
            ]
        );
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                $ai_text = $body['candidates'][0]['content']['parts'][0]['text'];
                $ai_text = str_replace(['```json', '```'], '', $ai_text);
                $ai = json_decode($ai_text, true);
                
                if ($ai && !empty($ai['titulo_viral'])) {
                    wp_update_post([
                        'ID' => $post_id,
                        'post_title' => sanitize_text_field($ai['titulo_viral']),
                        'post_content' => sanitize_textarea_field($ai['descripcion_seo'])
                    ]);
                    update_post_meta($post_id, '_gf_why_selected', sanitize_textarea_field($ai['por_que_elegido']));
                    gf_log("IA content generado para $post_id", 'info');
                } else {
                    wp_update_post(['ID' => $post_id, 'post_content' => 'Regalo seleccionado especialmente para ti.']);
                }
            }
        } else {
            wp_update_post(['ID' => $post_id, 'post_content' => 'Regalo cuidadosamente seleccionado.']);
        }
    } else {
        wp_update_post(['ID' => $post_id, 'post_content' => 'Descubre este increíble regalo.']);
    }
}

// === RESPUESTA FINAL ===
gf_json_response([
    'success' => true,
    'message' => 'Producto ingestionado correctamente',
    'post_id' => $post_id
], 200);
?>
