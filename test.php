<?php
/**
 * TEST MANUAL - Enviar un producto de prueba a la API
 * Acceder a: https://tu-dominio.com/wp-content/plugins/giftfinder-core/test.php
 */

header('Content-Type: text/html; charset=UTF-8');

// Cargar WordPress para obtener home_url()
$wp_loaded = false;
$possible_paths = [
    dirname(__FILE__) . '/../../../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php',
    dirname(__FILE__) . '/../../wp-load.php',
    $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        @require_once $path;
        if (function_exists('home_url')) {
            $wp_loaded = true;
            break;
        }
    }
}

// URL de la API
$api_url = $wp_loaded ? home_url('/wp-json/giftia/v1/ingest') : 'https://giftia.es/wp-json/giftia/v1/ingest';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Giftia API</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 3px; }
        .success { background: #d4edda; border-color: #28a745; }
        .error { background: #f8d7da; border-color: #dc3545; }
        textarea { width: 100%; height: 150px; font-family: monospace; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 GIFTIA API TEST</h1>
        
        <div class="section">
            <h2>Enviar Producto de Prueba</h2>
            <p>Este formulario envía un producto de prueba directamente a la API.</p>
            
            <form method="POST">
                <p>
                    <label>Token (copialo de Admin → Settings)</label><br>
                    <input type="text" name="token" style="width: 100%; padding: 8px;" placeholder="nu27OrX2t5VZQmrGXfoZk3pbcS97yiP5" value="<?php echo isset($_POST['token']) ? htmlspecialchars($_POST['token']) : ''; ?>">
                </p>
                
                <p>
                    <button type="submit" name="action" value="send">📤 Enviar Producto de Prueba</button>
                    <button type="submit" name="action" value="status">📋 Ver Estado</button>
                </p>
            </form>
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST'):
            $action = $_POST['action'] ?? '';
            $token = $_POST['token'] ?? '';
            
            if ($action === 'send'):
                // Prepare test product
                $product = [
                    'title' => 'Test Product - AirPods Pro',
                    'asin' => 'B08HXVQG7K',
                    'price' => '229.99',
                    'image_url' => 'https://m.media-amazon.com/images/I/31sKJUAp9PL.jpg',
                    'vendor' => 'Amazon',
                    'affiliate_url' => 'https://amazon.es/dp/B08HXVQG7K?tag=GIFTIA-21',
                    'description' => 'Auriculares premium con cancelación activa de ruido.',
                ];
                
                $json_data = json_encode($product);
                
                // Send to API (usando variable global $api_url)
                global $api_url;
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $api_url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $json_data,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'X-GIFTIA-TOKEN: ' . $token,
                        'User-Agent: GiftiaTest/1.0'
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT => 15,
                ]);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);
                curl_close($ch);
                
                echo '<div class="section ' . ($http_code === 200 ? 'success' : 'error') . '">';
                echo '<h2>' . ($http_code === 200 ? '✅ ÉXITO' : '❌ ERROR') . '</h2>';
                echo '<p><strong>HTTP Status:</strong> ' . $http_code . '</p>';
                
                if ($curl_error):
                    echo '<p><strong>cURL Error:</strong> ' . htmlspecialchars($curl_error) . '</p>';
                endif;
                
                echo '<p><strong>Respuesta API:</strong></p>';
                echo '<textarea readonly>';
                if ($response):
                    echo htmlspecialchars(json_encode(json_decode($response, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                else:
                    echo '(Sin respuesta)';
                endif;
                echo '</textarea>';
                
                echo '<p><strong>Producto enviado:</strong></p>';
                echo '<textarea readonly>';
                echo htmlspecialchars(json_encode($product, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                echo '</textarea>';
                
                echo '</div>';
                
                echo '<div class="section">';
                echo '<h2>🔍 Próximos Pasos</h2>';
                echo '<ol>';
                echo '<li>Si HTTP Status es 200: El producto se envió correctamente. Ve a WordPress → Products → All Gifts para verificar.</li>';
                echo '<li>Si HTTP Status es 403: El token es incorrecto. Cópialo de WordPress Admin → Settings.</li>';
                echo '<li>Si HTTP Status es 500: Hay un error en la API. Revisa wp-content/debug.log.</li>';
                echo '</ol>';
                echo '</div>';
                
            elseif ($action === 'status'):
                // Load WordPress for status check
                $wp_loaded = false;
                $possible_paths = [
                    dirname(__FILE__) . '/../../../../wp-load.php',
                    dirname(__FILE__) . '/../../../wp-load.php',
                    dirname(__FILE__) . '/../../wp-load.php',
                    $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
                ];
                
                foreach ($possible_paths as $path) {
                    if (file_exists($path)) {
                        @require_once $path;
                        if (function_exists('get_option')) {
                            $wp_loaded = true;
                            break;
                        }
                    }
                }
                
                if ($wp_loaded):
                    $products = get_posts(['post_type' => 'gf_gift', 'posts_per_page' => 5]);
                    
                    echo '<div class="section success">';
                    echo '<h2>✅ Estado Actual</h2>';
                    echo '<p><strong>Total de productos:</strong> ' . count(get_posts(['post_type' => 'gf_gift', 'posts_per_page' => -1])) . '</p>';
                    
                    if ($products):
                        echo '<p><strong>Últimos productos:</strong></p>';
                        echo '<ul>';
                        foreach ($products as $p):
                            echo '<li>' . htmlspecialchars($p->post_title) . ' (' . $p->post_status . ')</li>';
                        endforeach;
                        echo '</ul>';
                    else:
                        echo '<p>⚠️ Sin productos aún. Ejecuta Hunter.py o envía un producto de prueba.</p>';
                    endif;
                    echo '</div>';
                else:
                    echo '<div class="section error">';
                    echo '<h2>❌ Error</h2>';
                    echo '<p>No se pudo cargar WordPress. Verifica que el plugin está instalado correctamente.</p>';
                    echo '</div>';
                endif;
            endif;
        endif;
        ?>
        
        <hr>
        <p style="font-size: 12px; color: #666;">
            URL de la API: <code><?php echo esc_html($api_url); ?></code>
        </p>
    </div>
</body>
</html>
