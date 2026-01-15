<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gf_create_custom_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // 1. Tabla Principal de Productos IA
    $table_ai = $wpdb->prefix . 'gf_products_ai';
    $sql_ai = "CREATE TABLE IF NOT EXISTS $table_ai (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL UNIQUE,
        asin varchar(50) NOT NULL UNIQUE,
        affiliate_network varchar(50) DEFAULT 'amazon' NOT NULL,
        ai_tags_json text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY asin (asin)
    ) $charset_collate;";

    // 2. Histórico de Precios
    $table_logs = $wpdb->prefix . 'gf_price_logs';
    $sql_logs = "CREATE TABLE IF NOT EXISTS $table_logs (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_ai_id mediumint(9) NOT NULL,
        price_current float NOT NULL,
        vendor varchar(50) DEFAULT 'amazon' NOT NULL,
        date_check datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY product_ai_id (product_ai_id)
    ) $charset_collate;";

    // 3. Tabla de Ofertas
    $table_offers = $wpdb->prefix . 'gf_affiliate_offers';
    $sql_offers = "CREATE TABLE IF NOT EXISTS $table_offers (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        vendor_name varchar(50) NOT NULL,
        affiliate_url text NOT NULL,
        price float DEFAULT 0.0,
        currency varchar(5) DEFAULT 'EUR',
        is_active tinyint(1) DEFAULT 1,
        last_update datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        UNIQUE KEY post_vendor (post_id, vendor_name)
    ) $charset_collate;";

    // Crear todas las tablas
    dbDelta( $sql_ai );
    dbDelta( $sql_logs );
    dbDelta( $sql_offers );

    // Guardar versión de BD
    update_option( 'gf_db_version', '6.2' );
    
    // Migrar token antiguo si existe
    gf_migrate_legacy_token();
    
    // Log
    error_log( '[GIFTIA] Tablas de base de datos creadas/verificadas' );
}

/**
 * Migra tokens del nombre antiguo al nuevo para consistencia
 * gf_ingest_secret_token → gf_wp_api_token
 */
function gf_migrate_legacy_token() {
    $old_token = get_option( 'gf_ingest_secret_token', '' );
    $new_token = get_option( 'gf_wp_api_token', '' );
    
    // Si existe token antiguo pero no nuevo, migrar
    if ( !empty( $old_token ) && empty( $new_token ) ) {
        update_option( 'gf_wp_api_token', $old_token );
        error_log( '[GIFTIA] Token migrado de gf_ingest_secret_token a gf_wp_api_token' );
    }
    
    // Si no hay ningún token, generar uno nuevo
    if ( empty( $old_token ) && empty( $new_token ) ) {
        $generated_token = bin2hex( random_bytes( 16 ) );
        update_option( 'gf_wp_api_token', $generated_token );
        error_log( '[GIFTIA] Token generado automáticamente: ' . substr( $generated_token, 0, 10 ) . '...' );
    }
}

// Ejecutar on activation
register_activation_hook( dirname( dirname( __FILE__ ) ) . '/giftfinder-core.php', 'gf_create_custom_tables' );

// También ejecutar cada vez que se carga el plugin (para estar seguro)
add_action( 'plugins_loaded', function() {
    global $wpdb;
    $table_ai = $wpdb->prefix . 'gf_products_ai';
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_ai'" ) !== $table_ai ) {
        gf_create_custom_tables();
    }
    
    // Siempre intentar migrar token (una vez por carga)
    static $migrated = false;
    if ( !$migrated ) {
        gf_migrate_legacy_token();
        $migrated = true;
    }
}, 1 );

?>