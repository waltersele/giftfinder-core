<?php
/**
 * GIFTIA GEMINI RECOMMENDER
 * 
 * Usa Gemini para seleccionar los mejores productos del stock
 * y generar explicaciones personalizadas de por qué son perfectos.
 * 
 * @since v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class GiftiaGeminiRecommender {
    
    private $api_key;
    private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    
    public function __construct() {
        $this->api_key = $this->get_api_key();
    }
    
    /**
     * Obtiene la API key de Gemini desde env o options
     */
    private function get_api_key() {
        if (function_exists('giftia_env')) {
            $key = giftia_env('GEMINI_API_KEY', '');
            if ($key) return $key;
        }
        return get_option('gf_gemini_api_key', '');
    }
    
    /**
     * Verifica si Gemini está disponible
     */
    public function is_available() {
        return !empty($this->api_key);
    }
    
    /**
     * Selecciona y rankea productos usando Gemini
     * 
     * @param array $profile Perfil del usuario (destinatario, edad, intereses, ocasion, presupuesto)
     * @param array $products Array de productos disponibles
     * @return array Productos ordenados con explicaciones
     */
    public function recommend($profile, $products) {
        if (!$this->is_available()) {
            return $this->fallback_recommend($profile, $products);
        }
        
        // Limitar a 30 productos para no exceder límites de tokens
        $products = array_slice($products, 0, 30);
        
        // Construir prompt
        $prompt = $this->build_prompt($profile, $products);
        
        // Llamar a Gemini
        $response = $this->call_gemini($prompt);
        
        if (!$response) {
            return $this->fallback_recommend($profile, $products);
        }
        
        // Parsear respuesta
        return $this->parse_response($response, $products);
    }
    
    /**
     * Construye el prompt para Gemini
     */
    private function build_prompt($profile, $products) {
        // Formatear productos para el prompt
        $products_text = "";
        foreach ($products as $idx => $p) {
            $products_text .= sprintf(
                "[%d] %s - %.2f€ - Categoría: %s\n",
                $idx,
                $p['title'],
                $p['price'],
                implode(', ', $p['categories'] ?? ['General'])
            );
        }
        
        // Traducir ocasión a español legible
        $ocasiones_map = [
            'cumple' => 'cumpleaños',
            'navidad' => 'Navidad',
            'sanvalentin' => 'San Valentín',
            'aniversario' => 'aniversario de pareja',
            'diaMadre' => 'Día de la Madre/Padre',
            'graduacion' => 'graduación',
            'boda' => 'boda',
            'gracias' => 'agradecer algo',
            'random' => 'sin motivo especial, solo porque sí'
        ];
        $ocasion_text = $ocasiones_map[$profile['ocasion']] ?? $profile['ocasion'];
        
        // Traducir destinatario
        $dest_map = [
            'pareja' => 'mi pareja',
            'padre' => 'mi padre o madre',
            'amigo' => 'un amigo/a',
            'hermano' => 'mi hermano/a',
            'abuelo' => 'mi abuelo/a',
            'jefe' => 'mi jefe o compañero de trabajo',
            'yo' => 'mí mismo/a'
        ];
        $dest_text = $dest_map[$profile['destinatario']] ?? $profile['destinatario'];
        
        // Traducir edad
        $edad_map = [
            'nino' => 'niño/a de 0-12 años',
            'teen' => 'adolescente de 13-17 años',
            'joven' => 'joven adulto de 18-30 años',
            'adulto' => 'adulto de 31-50 años',
            'senior' => 'persona de 51-70 años',
            'mayor' => 'persona mayor de 70+ años'
        ];
        $edad_text = $edad_map[$profile['edad']] ?? $profile['edad'];
        
        $prompt = <<<PROMPT
Eres un experto en regalos personalizados para giftia.es. Tu trabajo es seleccionar los MEJORES regalos de nuestro catálogo.

## PERFIL DEL DESTINATARIO:
- **Para quién:** {$dest_text}
- **Edad:** {$edad_text}
- **Presupuesto:** {$profile['precio_min']}€ - {$profile['precio_max']}€
- **Intereses:** {$profile['intereses_text']}
- **Ocasión:** {$ocasion_text}

## PRODUCTOS DISPONIBLES:
{$products_text}

## TU TAREA:
1. Selecciona los 8 MEJORES productos para esta persona (máximo 8)
2. Ordénalos del más recomendado al menos
3. Para cada uno, explica en 1-2 frases POR QUÉ es perfecto para esta persona específica

## FORMATO DE RESPUESTA (JSON estricto):
```json
{
  "recommendations": [
    {"index": 0, "reason": "Perfecto para un cumpleaños porque..."},
    {"index": 5, "reason": "Ideal para alguien que le gusta..."}
  ]
}
```

IMPORTANTE:
- Solo usa índices de productos que existan en la lista
- Las razones deben ser personalizadas al perfil, no genéricas
- Si un producto no encaja bien, NO lo incluyas
- Responde SOLO con el JSON, sin texto adicional
PROMPT;

        return $prompt;
    }
    
    /**
     * Llama a la API de Gemini
     */
    private function call_gemini($prompt) {
        $url = $this->api_url . '?key=' . $this->api_key;
        
        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2000,
            ]
        ];
        
        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($body),
            'timeout' => 30,
        ]);
        
        if (is_wp_error($response)) {
            error_log('Giftia Gemini Error: ' . $response->get_error_message());
            return null;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            error_log('Giftia Gemini HTTP Error: ' . $code);
            return null;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Extraer texto de la respuesta de Gemini
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        return $text;
    }
    
    /**
     * Parsea la respuesta de Gemini y la combina con productos
     */
    private function parse_response($response, $products) {
        // Extraer JSON de la respuesta (puede venir con markdown)
        preg_match('/\{[\s\S]*\}/', $response, $matches);
        
        if (empty($matches[0])) {
            error_log('Giftia Gemini: No JSON found in response');
            return $this->fallback_recommend([], $products);
        }
        
        $data = json_decode($matches[0], true);
        
        if (!isset($data['recommendations']) || !is_array($data['recommendations'])) {
            error_log('Giftia Gemini: Invalid JSON structure');
            return $this->fallback_recommend([], $products);
        }
        
        $result = [];
        foreach ($data['recommendations'] as $rec) {
            $idx = (int) $rec['index'];
            if (isset($products[$idx])) {
                $product = $products[$idx];
                $product['gemini_reason'] = $rec['reason'] ?? '';
                $product['ai_selected'] = true;
                $result[] = $product;
            }
        }
        
        // Si Gemini devolvió pocos resultados, completar con fallback
        if (count($result) < 5) {
            $used_ids = array_column($result, 'id');
            foreach ($products as $p) {
                if (!in_array($p['id'], $used_ids)) {
                    $p['gemini_reason'] = '';
                    $p['ai_selected'] = false;
                    $result[] = $p;
                }
                if (count($result) >= 10) break;
            }
        }
        
        return $result;
    }
    
    /**
     * Fallback cuando Gemini no está disponible
     * Usa las reglas de avatar para filtrar
     */
    private function fallback_recommend($profile, $products) {
        // Simplemente devolver los productos en orden, marcados como no-AI
        return array_map(function($p) {
            $p['gemini_reason'] = '';
            $p['ai_selected'] = false;
            return $p;
        }, array_slice($products, 0, 15));
    }
}

/**
 * Función helper para usar el recommender
 */
function giftia_get_ai_recommendations($profile, $products) {
    $recommender = new GiftiaGeminiRecommender();
    return $recommender->recommend($profile, $products);
}

/**
 * Verifica si Gemini está disponible
 */
function giftia_gemini_available() {
    $recommender = new GiftiaGeminiRecommender();
    return $recommender->is_available();
}
