<?php
/**
 * SIMPLE TEST ENDPOINT - Sin dependencias
 * Para verificar que WordPress funciona
 */

define('GIFTIA_DOING_INGEST', true);
header('Content-Type: application/json; charset=UTF-8');

error_log('[TEST-API] ===== TEST API INICIADO =====');

// === CARGAR WORDPRESS ===
error_log('[TEST-API] Intentando cargar WordPress...');

$wp_load_paths = [
    $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
    dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php',
    dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    error_log('[TEST-API] Intentando: ' . $path);
    if (file_exists($path)) {
        error_log('[TEST-API] Archivo existe, cargando...');
        @require_once $path;
        if (function_exists('get_option')) {
            $wp_loaded = true;
            error_log('[TEST-API] WordPress cargado correctamente desde: ' . $path);
            break;
        }
    }
}

if (!$wp_loaded) {
    error_log('[TEST-API] FATAL: WordPress no se puede cargar');
    http_response_code(500);
    $response = [
        'error' => 'WordPress no se puede cargar',
        'paths_tried' => $wp_load_paths
    ];
    die(json_encode($response));
}

// === VALIDAR TOKEN ===
error_log('[TEST-API] Validando token...');
$incoming_token = $_SERVER['HTTP_X_GIFTIA_TOKEN'] ?? '';
$stored_token = get_option('gf_wp_api_token', '');

error_log('[TEST-API] Token almacenado: ' . ($stored_token ? substr($stored_token, 0, 10) . '...' : 'NO ENCONTRADO'));
error_log('[TEST-API] Token enviado: ' . ($incoming_token ? substr($incoming_token, 0, 10) . '...' : 'NO ENVIADO'));

if (empty($stored_token)) {
    error_log('[TEST-API] ERROR: Token no configurado en WordPress');
}

if (empty($incoming_token) || $incoming_token !== $stored_token) {
    error_log('[TEST-API] FATAL: Token inválido');
    http_response_code(403);
    die(json_encode([
        'error' => 'Token inválido',
        'token_configured' => !empty($stored_token),
        'token_sent' => !empty($incoming_token)
    ]));
}

// === RECIBIR DATOS ===
error_log('[TEST-API] Leyendo datos POST...');
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log('[TEST-API] Datos recibidos: ' . substr($input, 0, 100) . '...');

if (!$data) {
    error_log('[TEST-API] ERROR: JSON inválido');
    http_response_code(400);
    die(json_encode(['error' => 'JSON inválido', 'raw_input' => substr($input, 0, 50)]));
}

// === RESPUESTA OK ===
error_log('[TEST-API] TEST EXITOSO');
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'API TEST OK - WordPress funcionando',
    'product_title' => $data['title'] ?? 'NO RECIBIDO',
    'asin' => $data['asin'] ?? 'NO RECIBIDO'
]);
?>
