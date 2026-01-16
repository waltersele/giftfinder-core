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

// ============================================================================
// 6 VIBES SIMPLIFICADOS + DIGITAL - Sincronizados con Hunter y Frontend
// ============================================================================
$GIFTIA_VIBES = [
    [
        'slug'     => 'digital',
        'name'     => 'Digital',
        'icon'     => 'fa-bolt',
        'keywords' => 'tarjeta regalo|gift card|código|codigo|ebook|kindle|suscripción|suscripcion|netflix|spotify|steam|playstation store|xbox|nintendo eshop|google play|itunes|audible|kindle unlimited|amazon prime|disney|hbo|curso online|masterclass|udemy|domestika|licencia|software|descarga digital',
    ],
    [
        'slug'     => 'tech',
        'name'     => 'Tech',
        'icon'     => 'fa-microchip',
        'keywords' => 'bluetooth|wifi|usb|gamer|gaming|inalambrico|smart|inteligente|movil|auriculares|teclado|drone|consola|kindle|altavoz|proyector|camara|instax|playstation|xbox|nintendo|pc|ordenador|portatil|alexa|google home|smartwatch|airpods|realidad virtual|retro',
    ],
    [
        'slug'     => 'gourmet',
        'name'     => 'Gourmet',
        'icon'     => 'fa-utensils',
        'keywords' => 'sarten|cuchillo|vino|cerveza|gourmet|sushi|recetas|cocina|coctel|cafe|cafetera|hamburguesa|pizza|air fryer|freidora|whisky|gin|vermouth|cata|sommelier|chef|delicatessen|aceite oliva|jamon iberico|chocolate|trufa|bbq|ahumador|thermomix',
    ],
    [
        'slug'     => 'zen',
        'name'     => 'Zen',
        'icon'     => 'fa-spa',
        'keywords' => 'yoga|masaje|relajante|vela|aroma|baño|spa|zen|bonsai|terrario|luna|estrellas|meditacion|mindfulness|cuenco tibetano|incienso|aromaterapia|difusor|aceites esenciales|pilates|masajeador|gua sha|jade|relax|descanso|dormir|sueño|almohada|albornoz|weighted blanket',
    ],
    [
        'slug'     => 'viajes',
        'name'     => 'Viajes',
        'icon'     => 'fa-plane',
        'keywords' => 'viaje|mapa|mochila|brujula|mundo|rascar|avion|maleta|camping|tienda|saco dormir|linterna|hamaca|trekking|senderismo|outdoor|aventura|escalada|montaña|navaja|victorinox|filtro agua|powerbank|kindle|traductor|snorkel|acuatico',
    ],
    [
        'slug'     => 'deporte',
        'name'     => 'Deporte',
        'icon'     => 'fa-dumbbell',
        'keywords' => 'deporte|pesas|gym|botella|fitness|entrenamiento|running|bicicleta|ciclismo|natacion|padel|tenis|raqueta|mancuerna|kettlebell|trx|foam roller|garmin|electroestimulador|recuperacion|compresion|trail|hidratacion',
    ],
    [
        'slug'     => 'moda',
        'name'     => 'Moda',
        'icon'     => 'fa-gem',
        'keywords' => 'moda|bolso|joya|colgante|pulsera|reloj|cartera|gafas sol|ray-ban|perfume|colonia|fragancia|piel|cuero|cashmere|seda|elegante|diseño|decoracion|cuadro|lampara|jarron|hogar|textil',
    ],
    [
        'slug'     => 'friki',
        'name'     => 'Friki',
        'icon'     => 'fa-jedi',
        'keywords' => 'lego|star wars|marvel|harry potter|pop|funko|juego mesa|zelda|mario|anillos|arcade|pokemon|disney|mandalorian|anime|manga|dragon ball|one piece|naruto|demon slayer|coleccion|figura|sable luz|varita|catan|dungeons|magic|cartas|playmobil|peluche|squishmallow|niños|infantil|juguete|stem',
    ],
];

// ============================================================
// TABLA CENTRALIZADA DE RECIPIENTS (PERFILES)
// ============================================================

// ============================================================================
// 5 PERFILES DE DESTINATARIO - Simplificados
// ============================================================================
$GIFTIA_RECIPIENTS = [
    ['slug' => 'pareja',  'name' => 'Mi pareja',  'icon' => 'fa-heart'],
    ['slug' => 'familia', 'name' => 'Familiar',   'icon' => 'fa-house-user'],
    ['slug' => 'amigos',  'name' => 'Amig@',      'icon' => 'fa-users'],
    ['slug' => 'peques',  'name' => 'Niñ@',       'icon' => 'fa-child-reaching'],
    ['slug' => 'yo',      'name' => 'Para mí',    'icon' => 'fa-user-astronaut'],
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

// ============================================================================
// FUNCIÓN: Clasificar recipient por keywords
// ============================================================================

function gf_classify_recipient($title) {
    $title_lower = strtolower($title);
    $recips = [];
    
    // Niños - prioridad alta (si detecta infantil, asigna solo eso)
    if(preg_match('/(niño|niña|infantil|juguete|lego|playmobil|peluche|bebé|bebe|kids|junior|escolar)/', $title_lower)) {
        return ['Niñ@'];
    }
    
    // Pareja / Romántico
    if(preg_match('/(romantico|pareja|amor|aniversario|san valentin|compromiso|boda)/', $title_lower)) {
        $recips[] = 'Mi pareja';
    }
    
    // Familiar (sin género específico)
    if(preg_match('/(familia|hogar|casa|padre|madre|abuelo|abuela)/', $title_lower)) {
        $recips[] = 'Familiar';
    }
    
    // Si no encaja en nada específico, es versátil (amigo o para mí)
    if(empty($recips)) {
        $recips[] = 'Amig@';
        $recips[] = 'Para mí';
    }
    
    return $recips;
}

// ============================================================
// FUNCIÓN: Clasificar restricción de edad del producto
// Retorna: 'alcohol', 'adult', 'kids', o null (sin restricción)
// ============================================================

function gf_classify_age_restriction($title, $price = 0) {
    $title_lower = strtolower($title);
    
    // ALCOHOL - Requiere 18+
    $alcohol_keywords = [
        'whisky', 'whiskey', 'bourbon', 'scotch', 'single malt',
        'gin', 'ginebra', 'vodka', 'ron ', 'rum ', 'tequila', 'mezcal',
        'cerveza', 'beer', 'vino', 'wine', 'champagne', 'cava', 'prosecco',
        'licor', 'brandy', 'cognac', 'armagnac', 'sake', 'soju',
        'vermut', 'vermouth', 'aperitivo', 'aperol', 'campari',
        'absenta', 'orujo', 'grappa', 'pisco', 'cachaza',
        'destilado', 'fermentado', 'graduación alcohólica',
        'añejo', 'reserva', 'gran reserva', 'bodega', 'viñedo',
        'coctelería', 'cocktail', 'mixología', 'bartender'
    ];
    
    foreach($alcohol_keywords as $kw) {
        if(strpos($title_lower, $kw) !== false) {
            return 'alcohol';
        }
    }
    
    // ADULT - Contenido para adultos (no sexual, pero no apropiado para niños)
    $adult_keywords = [
        'cuchillo profesional', 'navaja', 'katana', 'espada',
        'arma', 'pistola', 'rifle', 'escopeta',
        'tabaco', 'cigarro', 'pipa fumar', 'mechero zippo',
        'tatuaje', 'piercing', 'body mod'
    ];
    
    foreach($adult_keywords as $kw) {
        if(strpos($title_lower, $kw) !== false) {
            return 'adult';
        }
    }
    
    // KIDS - Específico para niños (opcional, para filtrar en búsquedas de adultos)
    $kids_keywords = [
        'infantil', 'bebé', 'bebe', 'baby', 'niño', 'niña',
        'juguete', 'peluche', 'muñeca', 'muñeco',
        'pañal', 'biberón', 'chupete', 'sonajero',
        'cuna', 'trona', 'cochecito'
    ];
    
    foreach($kids_keywords as $kw) {
        if(strpos($title_lower, $kw) !== false) {
            return 'kids';
        }
    }
    
    // Sin restricción específica
    return null;
}

// Permitir a otros plugins modificar vibes
$GIFTIA_VIBES = apply_filters('gf_vibes_list', $GIFTIA_VIBES);
$GIFTIA_RECIPIENTS = apply_filters('gf_recipients_list', $GIFTIA_RECIPIENTS);
$GIFTIA_BLACKLIST = apply_filters('gf_blacklist', $GIFTIA_BLACKLIST);
