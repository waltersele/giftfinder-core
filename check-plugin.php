<?php
// Simple test to check if plugin is loaded
header('Content-Type: application/json');

$plugin_file = dirname(__FILE__) . '/giftfinder-core.php';
$is_loaded = function_exists('wp_insert_post');

echo json_encode([
    'plugin_file_exists' => file_exists($plugin_file),
    'wordpress_loaded' => $is_loaded,
    'has_gf_functions' => function_exists('gf_register_rest_routes_early'),
    'message' => $is_loaded ? 'WordPress cargado' : 'WordPress NO cargado'
]);
?>
