<?php
/**
 * SIMPLE STATUS CHECK - Listar regalos actuales
 * Acceder a: https://tu-dominio.com/wp-content/plugins/giftfinder-core/status.php
 */

@ini_set('display_errors', 0);

header('Content-Type: text/html; charset=UTF-8');

// Intentar cargar WordPress
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

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Giftia Status</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 3px; }
        .success { background: #d4edda; border-color: #28a745; }
        .error { background: #f8d7da; border-color: #dc3545; }
        .warning { background: #fff3cd; border-color: #ffc107; }
        h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎁 GIFTIA STATUS</h1>
        
        <?php if (!$wp_loaded): ?>
            <div class="section error">
                <h2>❌ ERROR: WordPress no se cargó</h2>
                <p>No se pudo cargar wp-load.php desde ninguna de estas rutas:</p>
                <ul>
                    <?php foreach ($possible_paths as $path): ?>
                        <li><code><?php echo htmlspecialchars($path); ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Solución:</strong> Verifica que el plugin está en la ubicación correcta.</p>
            </div>
        <?php else: ?>
            <!-- WordPress Status -->
            <div class="section success">
                <h2>✅ WordPress Cargado</h2>
                <p>Versión: <?php echo get_bloginfo('version'); ?></p>
                <p>URL: <?php echo get_bloginfo('url'); ?></p>
            </div>
            
            <!-- Post Type Status -->
            <div class="section <?php echo post_type_exists('gf_gift') ? 'success' : 'error'; ?>">
                <h2><?php echo post_type_exists('gf_gift') ? '✅' : '❌'; ?> Post Type: gf_gift</h2>
                <p><?php echo post_type_exists('gf_gift') ? 'REGISTRADO' : 'NO ENCONTRADO'; ?></p>
            </div>
            
            <!-- Products Count -->
            <div class="section">
                <h2>📦 Productos (Regalos)</h2>
                <?php
                $products = get_posts([
                    'post_type' => 'gf_gift',
                    'posts_per_page' => -1,
                    'post_status' => 'any'
                ]);
                
                if (empty($products)):
                ?>
                    <div class="warning">
                        <p><strong>⚠️ Sin productos</strong></p>
                        <p>No hay regalos en la base de datos. Hunter.py debe crear algunos.</p>
                    </div>
                <?php else: ?>
                    <p><strong>Total: <?php echo count($products); ?> productos</strong></p>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Status</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($products, 0, 20) as $p): ?>
                                <tr>
                                    <td><?php echo $p->ID; ?></td>
                                    <td><?php echo htmlspecialchars(substr($p->post_title, 0, 50)); ?></td>
                                    <td><?php echo $p->post_status; ?></td>
                                    <td><?php echo $p->post_date; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($products) > 20): ?>
                        <p><em>Mostrando 20 de <?php echo count($products); ?> productos</em></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Database Tables -->
            <div class="section">
                <h2>🗄️ Tablas de Base de Datos</h2>
                <?php
                global $wpdb;
                $tables = [
                    $wpdb->prefix . 'gf_products_ai' => 'Productos IA',
                    $wpdb->prefix . 'gf_affiliate_offers' => 'Ofertas',
                    $wpdb->prefix . 'gf_price_logs' => 'Logs de Precio'
                ];
                
                foreach ($tables as $table => $name):
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
                    if ($exists):
                        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                        echo '<p><span style="color: green;">✅</span> <strong>' . $name . '</strong> - ' . $count . ' registros</p>';
                    else:
                        echo '<p><span style="color: red;">❌</span> <strong>' . $name . '</strong> - NO EXISTE</p>';
                    endif;
                endforeach;
                ?>
            </div>
            
            <!-- Configuration -->
            <div class="section">
                <h2>⚙️ Configuración</h2>
                <table>
                    <tr>
                        <th>Variable</th>
                        <th>Estado</th>
                    </tr>
                    <tr>
                        <td>WP_API_TOKEN</td>
                        <td>
                            <?php
                            $token = getenv('WP_API_TOKEN') ?: get_option('gf_wp_api_token');
                            echo $token ? '✅ SET' : '❌ NOT SET';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>GEMINI_API_KEY</td>
                        <td>
                            <?php
                            $gemini = getenv('GEMINI_API_KEY') ?: get_option('gf_gemini_api_key');
                            echo $gemini ? '✅ SET' : '❌ NOT SET';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>AMAZON_TAG</td>
                        <td>
                            <?php
                            $tag = getenv('AMAZON_TAG') ?: get_option('gf_amazon_tag');
                            echo $tag ? ('✅ ' . htmlspecialchars($tag)) : '❌ NOT SET';
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Logs -->
            <div class="section">
                <h2>📋 Logs Recientes</h2>
                <?php
                $log_file = WP_CONTENT_DIR . '/debug.log';
                if (file_exists($log_file)):
                    $log_content = file_get_contents($log_file);
                    $log_lines = array_slice(explode("\n", $log_content), -20);
                    ?>
                    <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 11px;">
                    <?php foreach ($log_lines as $line): ?>
                        <?php echo htmlspecialchars($line); ?>
                    <?php endforeach; ?>
                    </pre>
                <?php else: ?>
                    <p>No hay archivo debug.log</p>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
        
        <hr>
        <p style="font-size: 12px; color: #666;">
            Última actualización: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
