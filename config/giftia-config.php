<?php
/**
 * GIFTIA CONFIGURATION MANAGER
 * 
 * Centraliza TODA la configuración de slugs, vibes, keywords
 * Reemplaza hardcoding distribuido en el código
 * 
 * Ubicación: wp-content/plugins/giftfinder-core/config/giftia-config.php
 * Incluir en: giftfinder-core.php con: require_once plugin_dir_path(__FILE__) . 'config/giftia-config.php';
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ============================================================
// TABLA CENTRALIZADA DE VIBES/INTERESES
// ============================================================
// Define los vibes disponibles y sus palabras clave para clasificación
// Frontend usa SLUG, Backend usa NAME para BD
// Esto evita duplicación y facilita agregar nuevos vibes sin tocar código

$GIFTIA_VIBES = [
    [
        'slug'     => 'tech',
        'name'     => 'Tech',
        'icon'     => 'fa-microchip',
        'keywords' => 'bluetooth|wifi|usb|gamer|inalambrico|smart|inteligente|movil|auriculares|teclado|drone|consola|kindle|altavoz|proyector|camara|instax',
    ],
    [
        'slug'     => 'gourmet',
        'name'     => 'Gourmet',
        'icon'     => 'fa-utensils',
        'keywords' => 'sarten|cuchillo|vino|cerveza|gourmet|sushi|recetas|cocina|coctel|cafe|cafetera|hamburguesa|pizza|air fryer|freidora',
    ],
    [
        'slug'     => 'friki',
        'name'     => 'Friki',
        'icon'     => 'fa-jedi',
        'keywords' => 'lego|star wars|marvel|harry potter|pop|juego mesa|zelda|mario|anillos|arcade|pokemon|disney|mandalorian|funko',
    ],
    [
        'slug'     => 'zen',
        'name'     => 'Zen',
        'icon'     => 'fa-spa',
        'keywords' => 'yoga|masaje|relajante|vela|aroma|baño|spa|zen|bonsai|terrario|luna|estrellas',
    ],
    [
        'slug'     => 'viajes',
        'name'     => 'Viajes',
        'icon'     => 'fa-plane',
        'keywords' => 'viaje|mapa|mochila|brujula|mundo|rascar|avion|maleta',
    ],
    [
        'slug'     => 'deporte',
        'name'     => 'Deporte',
        'icon'     => 'fa-dumbbell',
        'keywords' => 'deporte|pesas|gym|botella|fitness|entrenamiento|running',
    ],
    [
        'slug'     => 'moda',
        'name'     => 'Moda',
        'icon'     => 'fa-gem',
        'keywords' => 'moda|bolso|joya|colgante|pulsera|reloj|cartera',
    ],
];

// ============================================================
// TABLA CENTRALIZADA DE RECIPIENTS (PERFILES)
// ============================================================

$GIFTIA_RECIPIENTS = [
    ['slug' => 'pareja',      'name' => 'Pareja',       'icon' => 'fa-heart'],
    ['slug' => 'familia',     'name' => 'Familia',      'icon' => 'fa-house-user'],
    ['slug' => 'amigos',      'name' => 'Amigos',       'icon' => 'fa-users'],
    ['slug' => 'peques',      'name' => 'Peques',       'icon' => 'fa-child-reaching'],
    ['slug' => 'compromiso',  'name' => 'Compromiso',   'icon' => 'fa-handshake'],
    ['slug' => 'friki',       'name' => 'Para Mí',      'icon' => 'fa-user-astronaut'],
];

// ============================================================
// MAPEO SLUG -> VIBE NAME (para frontend)
// ============================================================
// Generado dinámicamente de $GIFTIA_VIBES
// Si cambias un slug arriba, se refleja automáticamente aquí

function gf_get_vibe_by_slug($slug) {
    global $GIFTIA_VIBES;
    foreach($GIFTIA_VIBES as $vibe) {
        if($vibe['slug'] === $slug) {
            return $vibe['name'];
        }
    }
    return 'Friki'; // Fallback
}

// ============================================================
// TABLA CENTRALIZADA DE PRESUPUESTOS
// ============================================================

$GIFTIA_BUDGETS = [
    ['slug' => 'lowcost',   'name' => 'Low Cost (<20€)',     'min' => 0,    'max' => 19.99],
    ['slug' => 'standard',  'name' => 'Estándar (20€-50€)',  'min' => 20,   'max' => 49.99],
    ['slug' => 'premium',   'name' => 'Premium (50€-150€)',  'min' => 50,   'max' => 149.99],
    ['slug' => 'luxury',    'name' => 'Lujo (+150€)',        'min' => 150,  'max' => 999999],
];

// ============================================================
// FUNCIÓN: Asignar presupuesto según precio
// ============================================================

function gf_get_budget_by_price($price) {
    global $GIFTIA_BUDGETS;
    foreach($GIFTIA_BUDGETS as $budget) {
        if($price >= $budget['min'] && $price < $budget['max']) {
            return $budget['name'];
        }
    }
    return 'Estándar (20€-50€)'; // Fallback
}

// ============================================================
// LISTA NEGRA (Anti-Basura)
// ============================================================

$GIFTIA_BLACKLIST = [
    'calentador', 'tendedero', 'grifo', 'recambio', 'bateria', 'pila', 'aceite',
    'fregona', 'limpia', 'detergente', 'papel', 'filtro', 'bombilla', 'cable',
    'adaptador', 'tornillo', 'destornillador', 'funda', 'protector', 'cartucho',
    'enchufe', 'ladron', 'soporte', 'cristal templado', 'pack ahorro', 'bolsa',
    'alfombrilla', 'molde', 'accesorio', 'descalcificador', 'pastillas', 'repuesto'
];

// ============================================================
// VENDEDORES PERMITIDOS (Whitelist)
// ============================================================

$GIFTIA_ALLOWED_VENDORS = [
    'amazon',
    'awin',
    'tradedoubler',
    'fnac',
    'corteingles',
    'mediamarkt',
    'carrefour',
    'pccomponentes',
];

// ============================================================
// VENDORS RÁPIDOS (Para filtro de urgencia)
// ============================================================

$GIFTIA_FAST_VENDORS = ['amazon', 'corte', 'media', 'fnac', 'pc', 'carrefour', 'pccomponentes', 'game'];

// ============================================================
// TIEMPOS DE ENVÍO (Asociados a vendors)
// ============================================================

$GIFTIA_SHIPPING_TIMES = [
    'immed'    => 'Digital / Inmediato',
    'fast'     => '24h - 48h',
    'standard' => '4-7 Días',
    'any'      => 'Sin Prisa',
];

// ============================================================
// FUNCIÓN: Obtener todos los vibes formateados
// ============================================================

function gf_get_all_vibes() {
    global $GIFTIA_VIBES;
    return $GIFTIA_VIBES;
}

// ============================================================
// FUNCIÓN: Obtener todos los recipients formateados
// ============================================================

function gf_get_all_recipients() {
    global $GIFTIA_RECIPIENTS;
    return $GIFTIA_RECIPIENTS;
}

// ============================================================
// FUNCIÓN: Validar y limpiar vendor
// ============================================================

function gf_validate_vendor($vendor) {
    global $GIFTIA_ALLOWED_VENDORS;
    $vendor_clean = strtolower(trim($vendor ?? 'amazon'));
    
    // Buscar match parcial para "Amazon Associates" -> "amazon"
    foreach($GIFTIA_ALLOWED_VENDORS as $allowed) {
        if(strpos($vendor_clean, $allowed) !== false) {
            return $allowed;
        }
    }
    
    return 'amazon'; // Fallback seguro
}

// ============================================================
// FUNCIÓN: Clasificar producto por keywords
// ============================================================

function gf_classify_product($title) {
    global $GIFTIA_VIBES;
    $title_lower = strtolower($title);
    $matched_vibes = [];
    
    foreach($GIFTIA_VIBES as $vibe) {
        if(preg_match('/' . $vibe['keywords'] . '/i', $title_lower)) {
            $matched_vibes[] = $vibe['name'];
        }
    }
    
    // Fallback: si no encaja nada, es Friki/Original
    if(empty($matched_vibes)) {
        $matched_vibes[] = 'Friki';
    }
    
    return $matched_vibes;
}

// ============================================================
// FUNCIÓN: Validar si un producto es basura
// ============================================================

function gf_is_blacklisted($title, $price = 0) {
    global $GIFTIA_BLACKLIST;
    
    $title_lower = strtolower($title);
    
    // Filtro por palabras prohibidas
    foreach($GIFTIA_BLACKLIST as $word) {
        if(strpos($title_lower, $word) !== false) {
            return true;
        }
    }
    
    // Filtro de precio absurdo
    try {
        $price_float = (float)preg_replace('/[^0-9.]/', '', str_replace(',', '.', $price));
        if($price_float < 12) {
            return true; // Menos de 12€ = probable basura
        }
    } catch (Exception $e) {
        // Si no se puede parsear, no bloquear
    }
    
    return false;
}

// ============================================================
// FUNCIÓN: Obtener configuración global
// ============================================================

function gf_get_config($key, $default = null) {
    switch($key) {
        case 'amazon_tag':
            return get_option('gf_amazon_tag') ?: getenv('AMAZON_TAG') ?: 'GIFTIA-21';
        case 'gemini_key':
            return get_option('gf_gemini_api_key') ?: getenv('GEMINI_API_KEY');
        case 'ingest_token':
            return get_option('gf_wp_api_token') ?: getenv('WP_API_TOKEN');
        default:
            return $default;
    }
}

// ============================================================
// FILTROS WORDPRESS (Para que el sistema sea pluggable)
// ============================================================

// ============================================================
// FUNCIÓN: Clasificar recipient por keywords
// ============================================================

function gf_classify_recipient($title) {
    $title_lower = strtolower($title);
    $recips = [];
    
    if(preg_match('/(mujer|ella|novia|esposa|madre|mama|señora|dama|chica|femenino)/', $title_lower)) {
        $recips[] = 'Novia';
        $recips[] = 'Madre';
    }
    
    if(preg_match('/(hombre|el|novio|esposo|padre|papa|señor|caballero|chico|masculino)/', $title_lower)) {
        $recips[] = 'Novio';
        $recips[] = 'Padre';
    }
    
    if(preg_match('/(niño|niña|infantil|juguete|lego|consola|peluche|bebé|bebe)/', $title_lower)) {
        $recips[] = 'Peques';
    }
    
    if(empty($recips)) {
        $recips[] = 'Amigos';
        $recips[] = 'Pareja';
    }
    
    return $recips;
}

// Permitir a otros plugins modificar vibes
$GIFTIA_VIBES = apply_filters('gf_vibes_list', $GIFTIA_VIBES);
$GIFTIA_RECIPIENTS = apply_filters('gf_recipients_list', $GIFTIA_RECIPIENTS);
$GIFTIA_BLACKLIST = apply_filters('gf_blacklist', $GIFTIA_BLACKLIST);
