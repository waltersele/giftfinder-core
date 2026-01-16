<?php
/*
Plugin Name: GiftFinder Core
Description: Sistema Giftia 100% Automático. IA Profiling + Multi-Vendor + SEO Protection.
Version: 6.1 (PRODUCTION READY)
Author: Giftia Team
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// --- 0. CARGAR VARIABLES DE ENTORNO ---
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/env-loader.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/env-loader.php';
}

// --- 1. CARGA DE DEPENDENCIAS ---
if ( file_exists( plugin_dir_path( __FILE__ ) . 'config/giftia-config.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'config/giftia-config.php';
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/giftia-utils.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/giftia-utils.php';
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'install.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'install.php';
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin-settings.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin-settings.php';
}

// v4: JavaScript externo para evitar errores de sintaxis
if ( file_exists( plugin_dir_path( __FILE__ ) . 'frontend-ui-v4.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'frontend-ui-v4.php';
}

// --- 2. REST API ENDPOINT PARA HUNTER ---
// Usar un hook más temprano para asegurar registro
add_action( 'plugins_loaded', 'gf_register_rest_routes_early', 5 );

function gf_register_rest_routes_early() {
    add_action( 'rest_api_init', 'gf_register_rest_ingest_endpoint' );
}

function gf_register_rest_ingest_endpoint() {
    // Endpoint de ingesta para Hunter
    register_rest_route( 'giftia/v1', '/ingest', [
        'methods' => ['POST', 'OPTIONS'],
        'callback' => 'gf_handle_ingest_request',
        'permission_callback' => '__return_true',
        'args' => [
            'asin' => ['required' => true, 'type' => 'string'],
            'title' => ['required' => true, 'type' => 'string'],
            'price' => ['required' => true, 'type' => 'string'],
        ]
    ]);
    
    // Endpoint de recomendación con Avatar AI
    register_rest_route( 'giftia/v1', '/recommend', [
        'methods' => ['POST', 'OPTIONS'],
        'callback' => 'gf_handle_recommend_request',
        'permission_callback' => '__return_true',
    ]);
}

/**
 * Manejar petición de recomendación con Avatar AI
 */
function gf_handle_recommend_request( WP_REST_Request $request ) {
    // Incluir el archivo de recomendación
    $recommend_file = plugin_dir_path( __FILE__ ) . 'api-recommend.php';
    
    if ( file_exists( $recommend_file ) ) {
        // Pasar datos al script
        $_POST = $request->get_json_params();
        
        ob_start();
        include $recommend_file;
        $output = ob_get_clean();
        
        $data = json_decode( $output, true );
        return rest_ensure_response( $data );
    }
    
    return new WP_Error( 'not_found', 'API de recomendación no disponible', ['status' => 404] );
}

function gf_handle_ingest_request( WP_REST_Request $request ) {
    // Verificar token
    $token = $request->get_header( 'X-GIFTIA-TOKEN' );
    $stored_token = get_option( 'gf_wp_api_token', '' );
    
    if ( empty( $token ) || empty( $stored_token ) || $token !== $stored_token ) {
        error_log('[GIFTIA-API] Token inválido');
        return new WP_Error( 'forbidden', 'Token inválido', ['status' => 403] );
    }
    
    // Obtener datos
    $data = $request->get_json_params();
    
    // Validaciones básicas
    if ( empty( $data['asin'] ) || empty( $data['title'] ) ) {
        return new WP_Error( 'missing_fields', 'ASIN y title son requeridos', ['status' => 400] );
    }
    
    // Procesar ingesta
    return gf_process_product_ingest( $data );
}

function gf_process_product_ingest( $data ) {
    // === LIMPIAR DATOS ===
    $price = gf_parse_price( $data['price'] ?? '0' );
    $title_clean = gf_sanitize_title( $data['title'] );
    $vendor = gf_validate_vendor( $data['vendor'] ?? 'amazon' );
    $image_url = gf_is_valid_image_url( $data['image_url'] ?? '' ) ? ( $data['image_url'] ?? '' ) : '';
    $affiliate_url = gf_is_valid_affiliate_url( $data['affiliate_url'] ?? '' ) ? ( $data['affiliate_url'] ?? '' ) : '';
    
    error_log( '[GIFTIA-API] Procesando: ' . $title_clean );
    
    // === BUSCAR O CREAR PRODUCTO ===
    global $wpdb;
    $table_ai = $wpdb->prefix . 'gf_products_ai';
    
    // Verificar tabla existe
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_ai'" ) !== $table_ai ) {
        gf_create_custom_tables();
    }
    
    // Verificar post type
    if ( !post_type_exists( 'gf_gift' ) ) {
        do_action( 'init' );
    }
    
    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT post_id FROM $table_ai WHERE asin = %s",
        $data['asin']
    ));
    
    if ( $existing ) {
        error_log('[GIFTIA-API] Producto existe: ' . $existing->post_id);
        $post_id = $existing->post_id;
        wp_update_post( ['ID' => $post_id, 'post_status' => 'publish'] );
    } else {
        // Crear nuevo post
        $post_id = wp_insert_post( [
            'post_title' => $title_clean,
            'post_content' => $data['description'] ?? '',
            'post_excerpt' => substr( $title_clean, 0, 160 ),
            'post_status' => 'publish',
            'post_type' => 'gf_gift'
        ]);
        
        if ( is_wp_error( $post_id ) ) {
            error_log('[GIFTIA-API] Error creando post: ' . $post_id->get_error_message());
            return new WP_Error( 'post_creation_failed', $post_id->get_error_message(), ['status' => 500] );
        }
        
        error_log('[GIFTIA-API] Post creado: ' . $post_id);
        
        // Registrar en tabla AI
        $wpdb->insert( $table_ai, [
            'post_id' => $post_id,
            'asin' => $data['asin'],
            'affiliate_network' => 'amazon'
        ]);
        
        // Descargar imagen
        if ( !empty( $image_url ) ) {
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            
            $img_id = media_sideload_image( $image_url, $post_id, null, 'id' );
            if ( !is_wp_error( $img_id ) ) {
                set_post_thumbnail( $post_id, $img_id );
            }
        }
    }
    
    // === ACTUALIZAR OFERTAS ===
    $table_offers = $wpdb->prefix . 'gf_affiliate_offers';
    $wpdb->delete( $table_offers, [
        'post_id' => $post_id,
        'vendor_name' => $vendor
    ]);
    
    $wpdb->insert( $table_offers, [
        'post_id' => $post_id,
        'vendor_name' => $vendor,
        'affiliate_url' => $affiliate_url,
        'price' => $price,
        'is_active' => 1,
        'last_update' => current_time( 'mysql' )
    ]);
    
    // === CLASIFICACIÓN AUTOMÁTICA ===
    $vibes = gf_classify_product( $title_clean );
    foreach ( $vibes as $v ) {
        if ( !term_exists( $v, 'gf_interest' ) ) {
            wp_insert_term( $v, 'gf_interest' );
        }
        wp_set_object_terms( $post_id, $v, 'gf_interest', true );
    }
    
    $budget = gf_get_budget_by_price( $price );
    if ( !term_exists( $budget, 'gf_budget' ) ) {
        wp_insert_term( $budget, 'gf_budget' );
    }
    wp_set_object_terms( $post_id, $budget, 'gf_budget' );
    
    $recips = gf_classify_recipient( $title_clean );
    foreach ( $recips as $r ) {
        if ( !term_exists( $r, 'gf_recipient' ) ) {
            wp_insert_term( $r, 'gf_recipient' );
        }
        wp_set_object_terms( $post_id, $r, 'gf_recipient', true );
    }
    
    return rest_ensure_response( [
        'success' => true,
        'message' => 'Producto ingestionado correctamente',
        'post_id' => $post_id,
        'title' => $title_clean,
        'price' => $price
    ]);
}

// --- 2. ACTIVACIÓN ---
register_activation_hook( __FILE__, 'gf_create_custom_tables' );

// --- 3. ESTRUCTURA DE CONTENIDO ---
add_action( 'init', 'gf_register_content_structure' );

function gf_register_content_structure() {
    register_post_type( 'gf_gift', [
        'labels' => ['name' => 'Regalos IA', 'singular_name' => 'Regalo', 'add_new' => 'Cazar Nuevo'],
        'public' => true, 'has_archive' => true, 'show_in_rest' => true, 
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
        'menu_icon' => 'dashicons-buddicons-activity', 'rewrite' => ['slug' => 'regalo'],
    ]);

    register_taxonomy('gf_recipient', 'gf_gift', ['label' => '👤 ¿Para quién?', 'hierarchical' => true, 'show_in_rest' => true, 'public' => true]);
    register_taxonomy('gf_interest', 'gf_gift', ['label' => '❤️ Intereses', 'hierarchical' => true, 'show_in_rest' => true, 'public' => true]);
    register_taxonomy('gf_occasion', 'gf_gift', ['label' => '📅 Ocasión', 'hierarchical' => true, 'show_in_rest' => true, 'public' => true]);
    register_taxonomy('gf_budget', 'gf_gift', ['label' => '💶 Presupuesto', 'hierarchical' => true, 'show_in_rest' => true, 'public' => true]);
}

// --- 4. RESCATADOR DE FANTASMAS (Auto-Fix) ---
// Ejecuta esto cada vez que cargas el admin para arreglar productos viejos sin tag
add_action('admin_init', 'gf_rescue_ghost_products');

function gf_rescue_ghost_products() {
    if(!current_user_can('manage_options')) return;

    $args = [
        'post_type' => 'gf_gift',
        'posts_per_page' => 20, // Hacemos lotes pequeños para no colgar la web
        'post_status' => 'any',
        'tax_query' => [
            [
                'taxonomy' => 'gf_interest',
                'operator' => 'NOT EXISTS' // Busca productos sin interés asignado
            ]
        ]
    ];
    
    $ghosts = get_posts($args);

    if(count($ghosts) > 0) {
        if(!term_exists('Friki', 'gf_interest')) wp_insert_term('Friki', 'gf_interest');
        $term = get_term_by('name', 'Friki', 'gf_interest');

        foreach($ghosts as $p) {
            wp_set_object_terms($p->ID, $term->term_id, 'gf_interest');
            wp_update_post(['ID' => $p->ID, 'post_status' => 'publish']);
        }
    }
}
?>