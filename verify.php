<?php
/**
 * VERIFICACIÓN SIMPLE - Diagnosticar problemas de Giftia
 * Acceso: https://giftia.es/wp-content/plugins/giftfinder-core/verify.php
 */

header('Content-Type: text/plain; charset=UTF-8');

$issues = [];
$success = [];

echo "=== GIFTIA VERIFICACIÓN v1.0 ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

// 1. Verificar WordPress
echo "[1] Cargando WordPress...\n";
$wp_loaded = false;
$possible_paths = [
    dirname(__FILE__) . '/../../../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php',
    $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        @require_once $path;
        if (function_exists('get_option')) {
            $wp_loaded = true;
            echo "    ✓ WordPress cargado desde: $path\n";
            $success[] = 'WordPress loaded';
            break;
        }
    }
}

if (!$wp_loaded) {
    $issues[] = 'WordPress no se pudo cargar';
    echo "    ✗ ERROR: WordPress no cargado\n";
    echo "    Intentadas:\n";
    foreach ($possible_paths as $p) {
        echo "      - $p (existe: " . (file_exists($p) ? 'sí' : 'no') . ")\n";
    }
}

echo "\n";

if ($wp_loaded) {
    // 2. Post type
    echo "[2] Verificando post type 'gf_gift'...\n";
    if (post_type_exists('gf_gift')) {
        echo "    ✓ Post type registrado\n";
        $success[] = 'Post type exists';
    } else {
        $issues[] = 'Post type gf_gift no existe';
        echo "    ✗ Post type NO registrado\n";
    }
    
    // 3. Tablas
    echo "\n[3] Verificando tablas de base de datos...\n";
    global $wpdb;
    
    $tables = [
        $wpdb->prefix . 'gf_products_ai',
        $wpdb->prefix . 'gf_affiliate_offers',
        $wpdb->prefix . 'gf_price_logs'
    ];
    
    foreach ($tables as $table) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            echo "    ✓ $table ($count registros)\n";
        } else {
            $issues[] = "Tabla $table no existe";
            echo "    ✗ $table NO EXISTE\n";
        }
    }
    
    // 4. Productos
    echo "\n[4] Verificando productos...\n";
    $total = wp_count_posts('gf_gift');
    echo "    Total posts: " . ($total->publish ?? 0) . "\n";
    echo "    Borradores: " . ($total->draft ?? 0) . "\n";
    echo "    Papelera: " . ($total->trash ?? 0) . "\n";
    
    if (($total->publish ?? 0) > 0) {
        $success[] = 'Products exist';
    } else {
        $issues[] = 'Sin productos publicados';
    }
    
    // 5. Archivos
    echo "\n[5] Verificando archivos del plugin...\n";
    $files = [
        'giftfinder-core.php' => true,
        'api-ingest.php' => true,
        'install.php' => true,
        'config/giftia-config.php' => true,
        'includes/giftia-utils.php' => true,
        'includes/env-loader.php' => true,
    ];
    
    foreach ($files as $file => $required) {
        $path = dirname(__FILE__) . '/' . $file;
        $exists = file_exists($path);
        $status = $exists ? '✓' : '✗';
        echo "    $status $file\n";
        if (!$exists && $required) {
            $issues[] = "Archivo faltante: $file";
        }
    }
    
    // 6. Configuración
    echo "\n[6] Verificando configuración...\n";
    $config_vars = [
        'WP_API_TOKEN' => function_exists('giftia_env') ? giftia_env('WP_API_TOKEN', '') : get_option('gf_wp_api_token', ''),
        'GEMINI_API_KEY' => function_exists('giftia_env') ? giftia_env('GEMINI_API_KEY', '') : '',
        'AMAZON_TAG' => function_exists('giftia_env') ? giftia_env('AMAZON_TAG', '') : '',
    ];
    
    foreach ($config_vars as $var => $value) {
        $has_value = !empty($value);
        $status = $has_value ? '✓' : '✗';
        echo "    $status $var: " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value) . "\n";
        if (!$has_value) {
            $issues[] = "$var no configurado";
        }
    }
    
    // 7. Taxonomías
    echo "\n[7] Verificando taxonomías...\n";
    $taxonomies = ['gf_interest', 'gf_recipient', 'gf_occasion', 'gf_budget'];
    foreach ($taxonomies as $tax) {
        if (taxonomy_exists($tax)) {
            echo "    ✓ $tax\n";
        } else {
            echo "    ✗ $tax NO existe\n";
            $issues[] = "Taxonomía $tax no existe";
        }
    }
}

// === RESUMEN ===
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN:\n";
echo "- Éxitos: " . count($success) . "\n";
echo "- Problemas: " . count($issues) . "\n";

if (count($issues) > 0) {
    echo "\nPROBLEMAS ENCONTRADOS:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". $issue\n";
    }
    echo "\nACCIONES RECOMENDADAS:\n";
    
    // Check if issue is only tokens
    $token_issues = array_filter($issues, function($issue) {
        return strpos($issue, 'no configurado') !== false;
    });
    
    if (count($token_issues) > 0 && count($token_issues) === count($issues)) {
        echo "\n⚠️  PROBLEMA PRINCIPAL: TOKENS DE CONFIGURACIÓN VACÍOS\n\n";
        echo "SOLUCIÓN RÁPIDA:\n";
        echo "1. Ejecuta en PowerShell: D:\\HunterScrap\\config-helper.ps1 generate\n";
        echo "2. Ve a WordPress Admin → Products → ⚙️ Configuración\n";
        echo "3. Pega el token generado en el campo 'Token de API'\n";
        echo "4. Rellena también: Amazon Tag (ej: giftia0-21) y Gemini (opcional)\n";
        echo "5. Haz clic en: 💾 Guardar Configuración\n";
        echo "6. Vuelve a ejecutar este archivo\n\n";
    } else {
        echo "1. Desactiva y reactiva el plugin: Plugins → GiftFinder Core → Deactivate → Activate\n";
        echo "2. Verifica que wp-content/debug.log tenga errores recientes\n";
        echo "3. Si aún no funciona, ejecuta: https://giftia.es/wp-content/plugins/giftfinder-core/test.php\n";
    }
} else {
    echo "\n✓ Todo parece estar bien. Ahora puedes:\n";
    echo "1. Ejecutar Hunter.py: python3 D:\\HunterScrap\\hunter.py\n";
    echo "2. O enviar un producto de prueba: https://giftia.es/wp-content/plugins/giftfinder-core/test.php\n";
}

echo "\n";
?>
