<?php
/**
 * API RECOMMEND GIFTIA - Sistema CSI de Perfilado Inteligente
 * 
 * Este endpoint recibe el "avatar CSI" completo del usuario y utiliza
 * Gemini para recomendar los mejores productos basándose en:
 * - Relación con el destinatario
 * - Género y edad
 * - Personalidad y pasiones
 * - Presupuesto y ocasión
 */

// Si ya está WordPress cargado (llamado desde REST API), no hacer nada
if (!defined('ABSPATH')) {
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Manejar preflight CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // === CARGAR WORDPRESS ===
    $wp_load_paths = [
        $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
        dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php',
        dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php',
    ];

    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            @require_once $path;
            if (function_exists('get_option')) {
                $wp_loaded = true;
                break;
            }
        }
    }

    if (!$wp_loaded) {
        http_response_code(500);
        die(json_encode(['error' => 'WordPress no cargado']));
    }
}

// Cargar config si no está
if (!function_exists('giftia_env') && file_exists(dirname(__FILE__) . '/includes/env-loader.php')) {
    require_once dirname(__FILE__) . '/includes/env-loader.php';
}

// === RECIBIR DATOS DEL AVATAR CSI ===
$input = file_get_contents('php://input');
$avatar = json_decode($input, true);

if (!$avatar) {
    // Intentar desde GET para testing (formato legacy)
    $avatar = [
        'relation' => sanitize_text_field($_GET['relation'] ?? 'amigo'),
        'relation_name' => sanitize_text_field($_GET['relation_name'] ?? 'Amigo'),
        'gender' => sanitize_text_field($_GET['gender'] ?? 'neutral'),
        'age_min' => (int)($_GET['age_min'] ?? 18),
        'age_max' => (int)($_GET['age_max'] ?? 35),
        'interest_tags' => [],
        'min_price' => (int)($_GET['min_price'] ?? 20),
        'max_price' => (int)($_GET['max_price'] ?? 100),
        'allow_alcohol' => ($_GET['allow_alcohol'] ?? '0') === '1',
        'occasion' => sanitize_text_field($_GET['occasion'] ?? 'general')
    ];
}

// Compatibilidad con formato antiguo (vibe -> interest_tags)
if (!empty($avatar['vibe']) && empty($avatar['interest_tags'])) {
    $vibe_to_tags = [
        'tech' => ['Tech'],
        'cocina' => ['Gourmet', 'Cocina'],
        'viajero' => ['Viajes', 'Experiencias'],
        'zen' => ['Zen', 'Wellness'],
        'fit' => ['Deporte', 'Fitness'],
        'fashion' => ['Moda', 'Belleza'],
        'friki' => ['Friki', 'Gaming'],
        'peques' => ['Peques', 'Juguetes']
    ];
    $avatar['interest_tags'] = $vibe_to_tags[$avatar['vibe']] ?? ['Friki'];
    $avatar['relation'] = $avatar['profile'] ?? 'amigo';
    $avatar['relation_name'] = $avatar['profile_name'] ?? 'Amigo';
}

// Validar que tengamos al menos algún criterio de búsqueda
if (empty($avatar['interest_tags']) && empty($avatar['csi_profile'])) {
    // Si no hay tags ni perfil, usar tags genéricos según relación
    $relation_tags = [
        'bebe' => ['Peques', 'Bebé'],
        'nino' => ['Peques', 'Juguetes', 'Friki'],
        'adolescente' => ['Tech', 'Gaming', 'Friki'],
        'pareja' => ['Moda', 'Experiencias', 'Zen'],
        'amigo' => ['Tech', 'Friki', 'Experiencias'],
        'familia' => ['Hogar', 'Experiencias'],
        'jefe' => ['Tech', 'Gourmet', 'Hogar'],
        'yo' => ['Tech', 'Friki']
    ];
    $avatar['interest_tags'] = $relation_tags[$avatar['relation']] ?? ['Friki'];
}

// === CONSTRUIR DESCRIPCIÓN CSI DEL AVATAR ===
function build_avatar_description($avatar) {
    // Si viene un perfil CSI pre-construido, usarlo
    if (!empty($avatar['csi_profile'])) {
        $csi = $avatar['csi_profile'];
    } else {
        // Construir descripción desde los datos
        $csi = '';
    }
    
    // === CONSTRUIR DESCRIPCIÓN DETALLADA ===
    $desc_parts = [];
    
    // RELACIÓN
    $relation = $avatar['relation_name'] ?? $avatar['profile_name'] ?? 'una persona';
    $desc_parts[] = "DESTINATARIO: Regalo para {$relation}";
    
    // GÉNERO
    if (!empty($avatar['gender']) && $avatar['gender'] !== 'neutral') {
        $gender_text = $avatar['gender'] === 'male' ? 'hombre' : 'mujer';
        $desc_parts[] = "GÉNERO: {$gender_text}";
    }
    
    // EDAD
    $age_min = $avatar['age_min'] ?? 18;
    $age_max = $avatar['age_max'] ?? 40;
    $age_text = '';
    if ($age_max <= 2) {
        $age_text = 'EDAD: Bebé (0-2 años). Necesita regalos seguros, educativos y estimulantes.';
    } elseif ($age_max <= 12) {
        $age_text = "EDAD: Niño/a ({$age_min}-{$age_max} años). Regalos divertidos, educativos o de juego.";
    } elseif ($age_max <= 17) {
        $age_text = "EDAD: Adolescente ({$age_min}-{$age_max} años). Le gusta lo moderno, tecnológico y estar a la última.";
    } elseif ($age_min >= 65) {
        $age_text = "EDAD: Senior (65+ años). Valora la calidad, comodidad y lo práctico.";
    } elseif ($age_min >= 50) {
        $age_text = "EDAD: Adulto maduro ({$age_min}-{$age_max} años). Aprecia la calidad y experiencias.";
    } elseif ($age_min >= 30) {
        $age_text = "EDAD: Adulto ({$age_min}-{$age_max} años). Busca productos de calidad y útiles.";
    } else {
        $age_text = "EDAD: Joven ({$age_min}-{$age_max} años). Le gustan las novedades y lo moderno.";
    }
    $desc_parts[] = $age_text;
    
    // PERSONALIDAD
    if (!empty($avatar['personality_name'])) {
        $personality_desc = $avatar['personality_desc'] ?? '';
        $desc_parts[] = "PERSONALIDAD: {$avatar['personality_name']}. {$personality_desc}";
    }
    
    // PASIÓN/INTERÉS
    if (!empty($avatar['passion_name'])) {
        $passion_desc = $avatar['passion_desc'] ?? '';
        $desc_parts[] = "INTERÉS PRINCIPAL: {$avatar['passion_name']}. {$passion_desc}";
    }
    
    // TAGS DE INTERÉS (para contexto)
    if (!empty($avatar['interest_tags'])) {
        $tags = implode(', ', $avatar['interest_tags']);
        $desc_parts[] = "CATEGORÍAS DE INTERÉS: {$tags}";
    }
    
    // PRESUPUESTO
    $min_p = $avatar['min_price'] ?? 20;
    $max_p = $avatar['max_price'] ?? 100;
    $budget_type = $avatar['budget_type'] ?? 'medium';
    $budget_names = [
        'low' => 'económico (detalle)',
        'medium' => 'medio (regalo estándar)',
        'high' => 'alto (regalo especial)',
        'premium' => 'premium (regalo de lujo)'
    ];
    $budget_name = $budget_names[$budget_type] ?? 'estándar';
    $desc_parts[] = "PRESUPUESTO: {$min_p}€ - {$max_p}€ (tipo: {$budget_name})";
    
    // OCASIÓN
    if (!empty($avatar['occasion']) && $avatar['occasion'] !== 'general' && $avatar['occasion'] !== 'otro') {
        $ocasiones = [
            'cumpleaños' => 'cumpleaños',
            'navidad' => 'Navidad',
            'aniversario' => 'aniversario de pareja',
            'san_valentin' => 'San Valentín',
            'dia_madre' => 'Día de la Madre',
            'dia_padre' => 'Día del Padre',
            'graduacion' => 'graduación',
            'boda' => 'boda'
        ];
        $ocasion_text = $ocasiones[$avatar['occasion']] ?? $avatar['occasion'];
        $desc_parts[] = "OCASIÓN: Regalo para {$ocasion_text}";
    }
    
    // RESTRICCIONES
    $restrictions = [];
    if (empty($avatar['allow_alcohol']) || $avatar['allow_alcohol'] === false) {
        $restrictions[] = 'NO bebidas alcohólicas';
    }
    if (!empty($avatar['is_baby'])) {
        $restrictions[] = 'Solo productos seguros para bebés';
    } elseif (!empty($avatar['is_child'])) {
        $restrictions[] = 'Solo productos aptos para niños';
    }
    
    if (!empty($restrictions)) {
        $desc_parts[] = "⚠️ RESTRICCIONES: " . implode('. ', $restrictions);
    }
    
    // Combinar todo
    $full_description = "PERFIL CSI DEL DESTINATARIO:\n" . implode("\n", $desc_parts);
    
    // Añadir perfil CSI pre-construido si existe
    if (!empty($csi)) {
        $full_description .= "\n\nRESUMEN: {$csi}";
    }
    
    return $full_description;
}


// === HELPER: DETECTAR ALCOHOL EN TÍTULO ===
function contains_alcohol_words($title) {
    $title_lower = strtolower($title);
    // Lista exhaustiva de términos de alcohol en español e inglés
    $alcohol_words = [
        // Destilados
        'whisky', 'whiskey', 'bourbon', 'scotch',
        'gin', 'ginebra',
        'vodka',
        'ron', 'rum',
        'tequila', 'mezcal',
        'brandy', 'cognac', 'coñac', 'armagnac',
        'sake', 'shochu',
        'licor', 'liquor', 'aguardiente', 'orujo', 'pacharan', 'pacharán',
        'absenta', 'absinthe',
        // Vinos
        'vino', 'wine', 'cava', 'champagne', 'champán', 
        'prosecco', 'rioja', 'ribera', 'albariño',
        'tinto', 'blanco', 'rosado', 'vermouth', 'vermut',
        // Cervezas
        'cerveza', 'beer', 'lager', 'ale', 'ipa', 'stout',
        // Genéricos
        'alcohol', 'spirit', 'bebida alcohólica', 'copa', 'cocktail', 'cóctel'
    ];
    
    foreach ($alcohol_words as $word) {
        if (strpos($title_lower, $word) !== false) {
            return true;
        }
    }
    return false;
}

// === OBTENER PRODUCTOS DEL CATÁLOGO ===
function get_catalog_products($avatar, $limit = 40) {
    global $wpdb;
    
    // Obtener tags de interés del avatar CSI
    $tags = $avatar['interest_tags'] ?? [];
    
    // Compatibilidad con formato antiguo (vibe)
    if (empty($tags) && !empty($avatar['vibe'])) {
        $vibe_to_tag = [
            'tech' => ['Tech'],
            'cocina' => ['Gourmet', 'Cocina'],
            'viajero' => ['Viajes'],
            'zen' => ['Zen', 'Wellness'],
            'fit' => ['Deporte', 'Fitness'],
            'fashion' => ['Moda', 'Belleza'],
            'friki' => ['Friki', 'Gaming'],
            'peques' => ['Peques', 'Juguetes']
        ];
        $tags = $vibe_to_tag[$avatar['vibe']] ?? ['Friki'];
    }
    
    // Si sigue vacío, usar tags genéricos
    if (empty($tags)) {
        $tags = ['Friki', 'Tech'];
    }
    
    // Query productos con esos tags
    $args = [
        'post_type' => 'gf_gift',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'gf_interest',
                'field' => 'name',
                'terms' => $tags,
                'operator' => 'IN'
            ]
        ]
    ];
    
    $query = new WP_Query($args);
    $products = [];
    
    $table_offers = $wpdb->prefix . 'gf_affiliate_offers';
    
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        
        // Obtener precio
        $offer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_offers WHERE post_id = %d AND is_active = 1 ORDER BY price ASC LIMIT 1",
            $post_id
        ));
        
        if (!$offer) continue;
        
        $price = (float)$offer->price;
        
        // Filtrar por precio
        if ($price < $avatar['min_price'] || $price > $avatar['max_price']) {
            continue;
        }
        
        // Filtrar por restricción de edad
        $age_restriction = get_post_meta($post_id, '_gf_age_restriction', true);
        if ($age_restriction === 'alcohol' && !$avatar['allow_alcohol']) {
            continue;
        }
        
        // Filtro adicional por título para alcohol
        if (!$avatar['allow_alcohol'] && contains_alcohol_words(get_the_title())) {
            continue;
        }
        
        $products[] = [
            'id' => $post_id,
            'title' => get_the_title(),
            'price' => $price,
            'description' => wp_trim_words(get_the_content(), 30, '...'),
            'image' => get_the_post_thumbnail_url($post_id, 'medium'),
            'affiliate_url' => $offer->affiliate_url,
            'vendor' => $offer->vendor_name
        ];
    }
    
    wp_reset_postdata();
    
    // Si no hay productos con el tag exacto, buscar en todo
    if (empty($products)) {
        $args['tax_query'] = [];
        $query = new WP_Query($args);
        
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $offer = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_offers WHERE post_id = %d AND is_active = 1 ORDER BY price ASC LIMIT 1",
                $post_id
            ));
            
            if (!$offer) continue;
            
            $price = (float)$offer->price;
            if ($price < $avatar['min_price'] || $price > $avatar['max_price']) continue;
            
            // Filtrar alcohol también en búsqueda ampliada
            $age_restriction = get_post_meta($post_id, '_gf_age_restriction', true);
            if ($age_restriction === 'alcohol' && !$avatar['allow_alcohol']) continue;
            if (!$avatar['allow_alcohol'] && contains_alcohol_words(get_the_title())) continue;
            
            $products[] = [
                'id' => $post_id,
                'title' => get_the_title(),
                'price' => $price,
                'description' => wp_trim_words(get_the_content(), 30, '...'),
                'image' => get_the_post_thumbnail_url($post_id, 'medium'),
                'affiliate_url' => $offer->affiliate_url,
                'vendor' => $offer->vendor_name
            ];
            
            if (count($products) >= $limit) break;
        }
        wp_reset_postdata();
    }
    
    return $products;
}

// === CONSULTAR A GEMINI ===
function ask_gemini_recommendations($avatar_desc, $products, $gemini_key, $avatar = []) {
    if (empty($products)) {
        return [
            'success' => false,
            'error' => 'No hay productos disponibles'
        ];
    }
    
    // Preparar lista de productos para el prompt con más detalle
    $products_list = "";
    foreach ($products as $i => $p) {
        $desc = $p['description'] ?? '';
        $products_list .= ($i + 1) . ". ID:{$p['id']} | \"{$p['title']}\" | {$p['price']}€";
        if ($desc) $products_list .= " | {$desc}";
        $products_list .= "\n";
    }
    
    // Información extra del avatar para el prompt
    $extra_context = '';
    if (!empty($avatar['gender']) && $avatar['gender'] !== 'neutral') {
        $extra_context .= $avatar['gender'] === 'male' ? 'Es HOMBRE. ' : 'Es MUJER. ';
    }
    if (!empty($avatar['is_baby'])) {
        $extra_context .= 'Es un BEBÉ, solo productos seguros y estimulantes. ';
    }
    if (!empty($avatar['is_child'])) {
        $extra_context .= 'Es un NIÑO/A, productos divertidos y seguros. ';
    }
    if (!empty($avatar['occasion']) && $avatar['occasion'] !== 'general') {
        $extra_context .= "Ocasión: {$avatar['occasion']}. ";
    }
    
    $prompt = "Eres un EXPERTO en regalos personalizados con años de experiencia. Tu trabajo es analizar en profundidad el perfil de una persona y recomendar los regalos MÁS RELEVANTES del catálogo.

=== PERFIL DEL DESTINATARIO ===
{$avatar_desc}

{$extra_context}

=== CATÁLOGO DE PRODUCTOS ===
{$products_list}

=== TU MISIÓN ===
1. ANALIZA el perfil: edad, género, personalidad, intereses, ocasión
2. FILTRA productos que NO encajan (ej: cremas para hombre si es mujer joven gamer)
3. ORDENA del MÁS al MENOS recomendado según el perfil
4. EXPLICA brevemente (máx 15 palabras) POR QUÉ cada producto encaja con ESA persona específica

=== CRITERIOS DE RANKING ===
- Relevancia con los INTERESES declarados (gaming, tech, moda, etc.)
- Adecuación a la EDAD y GÉNERO
- Precio dentro del presupuesto
- Relevancia para la OCASIÓN si aplica
- Descarta productos genéricos si hay opciones más personalizadas

=== FORMATO JSON (obligatorio) ===
{
  \"avatar_summary\": \"Descripción en 1 línea de quién es esta persona y qué busca\",
  \"recommendations\": [
    {
      \"product_id\": 123,
      \"rank\": 1,
      \"match_score\": 95,
      \"reason\": \"Razón específica de por qué es PERFECTO para esta persona\"
    }
  ]
}

IMPORTANTE: 
- Solo devuelve JSON válido, nada más
- La razón debe ser ESPECÍFICA al perfil (no genérica)
- Ordena por relevancia REAL, no por precio
- Si un producto no encaja NADA, ponle score bajo (<50)


Devuelve máximo 10 productos ordenados. Solo responde con el JSON, sin explicaciones adicionales.";

    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$gemini_key",
        [
            'body' => json_encode([
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048
                ]
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 30
        ]
    );
    
    if (is_wp_error($response)) {
        error_log('[GIFTIA-RECOMMEND] Error Gemini: ' . $response->get_error_message());
        return ['success' => false, 'error' => 'Error conectando con Gemini'];
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
        error_log('[GIFTIA-RECOMMEND] Respuesta Gemini inválida: ' . print_r($body, true));
        return ['success' => false, 'error' => 'Respuesta Gemini inválida'];
    }
    
    $ai_text = $body['candidates'][0]['content']['parts'][0]['text'];
    
    // Limpiar JSON
    $ai_text = preg_replace('/```json\s*/', '', $ai_text);
    $ai_text = preg_replace('/```\s*/', '', $ai_text);
    $ai_text = trim($ai_text);
    
    $ai_data = json_decode($ai_text, true);
    
    if (!$ai_data || !isset($ai_data['recommendations'])) {
        error_log('[GIFTIA-RECOMMEND] JSON inválido de Gemini: ' . $ai_text);
        return ['success' => false, 'error' => 'JSON inválido', 'raw' => $ai_text];
    }
    
    return [
        'success' => true,
        'avatar_summary' => $ai_data['avatar_summary'] ?? '',
        'recommendations' => $ai_data['recommendations']
    ];
}

// === EJECUTAR ===
$avatar_desc = build_avatar_description($avatar);
$products = get_catalog_products($avatar);

// Obtener API key de Gemini
$gemini_key = giftia_env('GEMINI_API_KEY');
if (empty($gemini_key)) {
    $gemini_key = get_option('gf_gemini_api_key');
}

if (empty($gemini_key)) {
    // Sin Gemini, devolver productos ordenados por precio
    usort($products, function($a, $b) {
        return $a['price'] <=> $b['price'];
    });
    
    echo json_encode([
        'success' => true,
        'ai_powered' => false,
        'avatar_summary' => 'Recomendaciones basadas en filtros',
        'products' => array_slice($products, 0, 10)
    ]);
    exit;
}

// Consultar a Gemini con el avatar completo
$gemini_result = ask_gemini_recommendations($avatar_desc, $products, $gemini_key, $avatar);

if (!$gemini_result['success']) {
    // Fallback: devolver productos sin ordenar por AI
    echo json_encode([
        'success' => true,
        'ai_powered' => false,
        'avatar_summary' => 'Recomendaciones disponibles',
        'products' => array_slice($products, 0, 10),
        'gemini_error' => $gemini_result['error'] ?? 'Error desconocido'
    ]);
    exit;
}

// Ordenar productos según recomendaciones de Gemini
$products_by_id = [];
foreach ($products as $p) {
    $products_by_id[$p['id']] = $p;
}

$final_products = [];
foreach ($gemini_result['recommendations'] as $rec) {
    $pid = $rec['product_id'];
    if (isset($products_by_id[$pid])) {
        $product = $products_by_id[$pid];
        $product['match_score'] = $rec['match_score'] ?? 80;
        $product['ai_reason'] = $rec['reason'] ?? '';
        $product['rank'] = $rec['rank'] ?? 0;
        $final_products[] = $product;
    }
}

// Respuesta final
echo json_encode([
    'success' => true,
    'ai_powered' => true,
    'avatar_summary' => $gemini_result['avatar_summary'],
    'avatar_details' => $avatar,
    'products' => $final_products,
    'total_in_catalog' => count($products)
]);
