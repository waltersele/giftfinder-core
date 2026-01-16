# Giftfinder Core Plugin

WordPress plugin for managing AI-generated gift recommendations and affiliate product integrations.

## Features

- **REST API Integration**: Autonomous product ingestion via `/wp-json/giftia/v1/ingest`
- **Product Management**: Store products with AI scores and price history
- **Affiliate Integration**: Amazon, Awin, and TopDrop affiliate links
- **Custom Post Type**: `gf_gift` for gift products
- **Price Tracking**: Historical price logs for price analysis

## Installation

1. Copy plugin to `wp-content/plugins/giftfinder-core/`
2. Activate plugin in WordPress admin
3. Configure API token in WordPress options:
   ```php
   update_option('gf_api_token', 'your-token-here');
   ```

## API Endpoints

### POST `/wp-json/giftia/v1/ingest`

Ingest products from Hunter.py scraper.

**Required Headers:**
```
X-GIFTIA-TOKEN: your-api-token
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Product Title",
  "description": "Product description",
  "price": 99.99,
  "currency": "EUR",
  "image_url": "https://example.com/image.jpg",
  "affiliate_url": "https://amazon.es/...",
  "source": "amazon",
  "category": "Tech",
  "gift_score": 8.5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product created",
  "post_id": 12345
}
```

## Database Tables

- `gf_products_ai`: Product data from Hunter
- `gf_affiliate_offers`: Affiliate link tracking
- `gf_price_logs`: Historical price data

## Configuration

Set via WordPress options or environment variables:

- `gf_api_token`: API token for ingest endpoint
- `gf_amazon_tag`: Amazon affiliate tag
- `gf_gemini_key`: Google Gemini API key (for content generation)

## Files

- `giftfinder-core.php`: Main plugin file with REST API
- `admin-settings.php`: WordPress admin settings page
- `api-ingest.php`: Legacy endpoint (deprecated, use REST API)
- `frontend-ui.php`: Frontend gift display component
- `install.php`: Database table installation

## Development

**Requirements:**
- WordPress 5.0+
- PHP 7.4+
- cURL support

**Testing:**
```bash
curl -X POST https://giftia.es/wp-json/giftia/v1/ingest \
  -H "X-GIFTIA-TOKEN: your-token" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","price":10.00,"source":"amazon"}'
```

## Related

- **Hunter**: Autonomous Amazon scraper - [giftia-hunter](https://github.com/waltersele/giftia-hunter)
- **Website**: https://giftia.es

---

# 🧠 SISTEMA DE RECOMENDACIÓN INTELIGENTE

## Visión del Producto

GIFTIA no es un buscador de productos. Es un **asesor de regalos** que entiende:
- A QUIÉN va dirigido el regalo
- QUÉ tipo de persona es el destinatario (avatar)
- CUÁNDO lo necesita (urgencia)
- POR QUÉ ocasión (contexto emocional)

El objetivo: **El regalo perfecto con las mínimas preguntas necesarias**.

---

## 📐 Arquitectura del Sistema de Perfilado

### Concepto: Avatar del Destinatario

Cada respuesta del usuario construye un **AVATAR** con atributos que filtran y ponderan productos:

```php
$AVATAR = [
    'edad_range'          => [min, max],           // Rango de edad estimado
    'genero_hint'         => null|'M'|'F'|'N',     // Pista de género (opcional)
    'categorias_permitidas' => [...],              // Solo estos intereses
    'categorias_prohibidas' => [...],              // NUNCA estos
    'precio_sugerido'     => [min, max],           // Rango recomendado
    'relacion'            => 'cercana'|'formal'|'casual',
    'ocasion'             => 'cumple'|'navidad'|...,
    'urgencia'            => 'inmediata'|'semana'|'tiempo'
];
```

### Matriz de Perfiles → Restricciones

| Perfil | Edad Est. | Intereses VÁLIDOS | Intereses PROHIBIDOS | Precio Sugerido |
|--------|-----------|-------------------|---------------------|-----------------|
| **Hijo/a pequeño** | 0-12 | Juguetes, LEGO, Peluches, Libros infantiles, STEM | Vino, Perfume, Joyería, Tech caro, Moda adulta | 20-80€ |
| **Hijo/a teen** | 13-17 | Gaming, Tech, Moda joven, Música, Fandom, Deporte | Vino, Licores, Joyería cara, Hogar | 30-150€ |
| **Pareja** | 18-60 | TODO excepto infantil | Juguetes, Infantil | 50-200€ |
| **Padre** | 40-70 | Gourmet, Tech útil, Deporte, Hogar, Herramientas | Gaming hardcore, Juguetes, Moda joven | 40-150€ |
| **Madre** | 40-70 | Bienestar, Belleza, Hogar, Gourmet, Libros, Joyería | Gaming, Herramientas, Tech complejo | 40-150€ |
| **Abuelo** | 65-90 | Bienestar, Libros, Gourmet clásico, Hogar práctico | Gaming, Tech complejo, Deportes extremos, Moda joven | 30-100€ |
| **Abuela** | 65-90 | Bienestar, Hogar, Plantas, Libros, Belleza suave | Gaming, Tech complejo, Deportes | 30-100€ |
| **Amigo/a** | Variable | TODO (siguiente pregunta filtra) | Nada fijo, depende de edad | 20-80€ |
| **Jefe/Colega** | 25-60 | Gourmet, Tech elegante, Libros, Accesorios neutros | Muy personal, Juguetes, Fandom | 30-100€ |

### Intereses por Categoría de Edad

```php
$INTERESES_POR_EDAD = [
    'nino' => [      // 0-12
        'permitidos' => ['juguetes', 'lego', 'peluches', 'libros_infantiles', 'stem', 'aire_libre_nino'],
        'prohibidos' => ['vino', 'licores', 'perfume_adulto', 'joyeria', 'tech_caro', 'moda_adulta']
    ],
    'teen' => [      // 13-17
        'permitidos' => ['gaming', 'tech', 'moda_joven', 'musica', 'fandom', 'deporte', 'libros', 'belleza_joven'],
        'prohibidos' => ['vino', 'licores', 'joyeria_cara', 'hogar', 'herramientas']
    ],
    'joven' => [     // 18-35
        'permitidos' => ['ALL'],  // Todo permitido
        'prohibidos' => ['juguetes_infantiles']
    ],
    'adulto' => [    // 36-55
        'permitidos' => ['ALL'],
        'prohibidos' => ['juguetes_infantiles', 'gaming_hardcore']
    ],
    'senior' => [    // 56-70
        'permitidos' => ['gourmet', 'bienestar', 'hogar', 'libros', 'tech_simple', 'jardin'],
        'prohibidos' => ['gaming', 'tech_complejo', 'deportes_extremos', 'moda_joven']
    ],
    'mayor' => [     // 70+
        'permitidos' => ['bienestar', 'hogar_practico', 'libros', 'jardin', 'gourmet_clasico'],
        'prohibidos' => ['gaming', 'tech_complejo', 'deportes', 'moda_joven', 'joyeria_moderna']
    ]
];
```

---

## 🔄 Flujo de Usuario Inteligente

```
┌─────────────────────────────────────────────────────────────┐
│  PASO 1: ¿Para quién es el regalo?                          │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐     │
│  │Pareja│ │Padre │ │Madre │ │Hijo/a│ │Abuelo│ │Amigo │ ... │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘     │
│                                                             │
│  → Define: edad_estimada, genero_hint, relacion             │
│  → Si es "Amigo" o "Hijo" → Paso 1B (edad)                  │
│  → Si es "Abuelo/Padre/Madre" → Salta al Paso 2             │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  PASO 1B (Condicional): ¿Qué edad tiene?                    │
│  Solo aparece si seleccionó: Hijo, Amigo, Hermano, Primo    │
│                                                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│  │ Niño     │ │ Teen     │ │ Joven    │ │ Adulto   │       │
│  │ (0-12)   │ │ (13-17)  │ │ (18-35)  │ │ (36-55)  │       │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  PASO 2: ¿Qué le interesa?                                  │
│  [Solo muestra opciones VÁLIDAS según avatar construido]    │
│  [Multi-select, máximo 3-4 opciones]                        │
│                                                             │
│  Ejemplo para "Abuelo":                                     │
│  ✓ Libros  ✓ Jardín  ✓ Gourmet  ✓ Bienestar  ✓ Hogar       │
│  ✗ Gaming  ✗ Tech    ✗ Deportes extremos  [NO APARECEN]    │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  PASO 3: ¿Cuánto quieres gastar?                            │
│  [Slider PRE-AJUSTADO según perfil + relación]              │
│                                                             │
│  Abuelo → 30€ ════════●════ 100€                            │
│  Pareja → 50€ ════════════●════ 200€                        │
│                                                             │
│  + Presets: "Detalle" | "Estándar" | "Especial"             │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  PASO 4: ¿Para qué ocasión?                                 │
│  🎂 Cumpleaños  🎄 Navidad  💝 San Valentín  💍 Aniversario │
│  🎓 Graduación  💐 Día M/P  🥂 Boda  🙏 Gracias  🎁 Random  │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  PASO 5: ¿Cuándo lo necesitas?                              │
│  ⚡ Ya (digital)  🚀 Esta semana  📦 Tengo tiempo           │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  🎁 RESULTADOS con SCORING INTELIGENTE                      │
│                                                             │
│  Score = (match_intereses × 0.4) +                          │
│          (match_precio × 0.3) +                             │
│          (match_ocasion × 0.2) +                            │
│          (popularidad × 0.1)                                │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│  📧 CAPTURA DE LEAD (Contextual)                            │
│                                                             │
│  Aparece DESPUÉS de ver resultados, mensaje personalizado:  │
│                                                             │
│  [Si Abuelo + Cumpleaños]:                                  │
│  "¿Quieres que te recordemos el cumple de tu abuelo         │
│   el próximo año? Te avisaremos con tiempo para que         │
│   no tengas que buscar a última hora."                      │
│                                                             │
│  [Si envío express]:                                        │
│  "La próxima vez te avisamos con tiempo.                    │
│   ¿Cuándo es la fecha especial?"                            │
│                                                             │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │ 📧 tu@email.com  │  │ 📅 15/03/2027    │                 │
│  └──────────────────┘  └──────────────────┘                 │
│              [ 🔔 RECORDARME ]                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📧 Sistema de Captura de Leads

### Tabla: `gf_reminders`

```sql
CREATE TABLE gf_reminders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(100),        -- "mi abuelo", "mamá", etc.
    recipient_profile VARCHAR(50),      -- 'abuelo', 'madre', 'pareja'
    occasion VARCHAR(50),               -- 'cumple', 'navidad', 'aniversario'
    reminder_date DATE,                 -- Fecha a recordar
    days_before INT DEFAULT 7,          -- Días antes para avisar
    interests TEXT,                     -- JSON de intereses guardados
    price_range VARCHAR(20),            -- "30-100"
    created_at TIMESTAMP DEFAULT NOW(),
    last_sent_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_reminder (email, recipient_profile, reminder_date)
);
```

### Mensajes Contextuales por Escenario

| Escenario | Mensaje de Captura | Valor para Usuario |
|-----------|-------------------|-------------------|
| **Familiar + Cumpleaños** | "¿Te recordamos el cumple de tu [X] el próximo año?" | No olvidar fechas |
| **Envío Express** | "La próxima vez te avisamos con tiempo. ¿Cuándo es?" | Evitar estrés |
| **San Valentín** | "¿Te recordamos San Valentín 2027 con ideas para tu pareja?" | Nunca fallar |
| **Navidad** | "¿Te avisamos en Noviembre con ideas para [X]?" | Comprar sin prisas |
| **Cualquiera** | "¿Guardamos este perfil para búsquedas rápidas?" | Comodidad |

### Cron de Recordatorios

```php
// Ejecutar diariamente
function gf_send_reminders() {
    global $wpdb;
    $table = $wpdb->prefix . 'gf_reminders';
    
    // Buscar recordatorios que toca enviar
    $reminders = $wpdb->get_results("
        SELECT * FROM $table 
        WHERE is_active = 1 
        AND DATE_SUB(reminder_date, INTERVAL days_before DAY) <= CURDATE()
        AND (last_sent_at IS NULL OR YEAR(last_sent_at) < YEAR(CURDATE()))
    ");
    
    foreach($reminders as $r) {
        gf_send_reminder_email($r);
    }
}
add_action('gf_daily_reminders', 'gf_send_reminders');
```

---

## 🏷️ Sistema de Tags en Productos

### Tags Requeridos por Producto (Hunter)

Cada producto debe tener:

```php
$producto = [
    'title' => '...',
    'price' => 99.99,
    
    // TAXONOMÍAS EXISTENTES
    'gf_interest' => ['Tech', 'Gaming'],     // Interés principal
    'gf_recipient' => ['Joven', 'Teen'],     // Para quién es apropiado
    'gf_budget' => 'Premium',                // Rango de precio
    
    // NUEVOS TAGS NECESARIOS
    'gf_age_range' => ['teen', 'joven', 'adulto'],  // Edades apropiadas
    'gf_occasion' => ['cumple', 'navidad'],         // Ocasiones ideales
    'gf_gender' => 'neutral',                       // M, F, neutral
];
```

### Clasificación Automática por Keywords (Hunter)

```python
AGE_KEYWORDS = {
    'nino': ['infantil', 'niño', 'niña', 'kids', 'juguete', 'peluche', 'lego duplo'],
    'teen': ['gaming', 'gamer', 'fortnite', 'tiktok', 'teen', 'juvenil'],
    'joven': ['trendy', 'streetwear', 'festival', 'university'],
    'adulto': ['premium', 'profesional', 'ejecutivo', 'elegante'],
    'senior': ['fácil uso', 'letras grandes', 'clásico', 'tradicional'],
}

GENDER_KEYWORDS = {
    'M': ['hombre', 'masculino', 'él', 'caballero', 'barba', 'corbata'],
    'F': ['mujer', 'femenino', 'ella', 'dama', 'bolso', 'maquillaje'],
    'neutral': []  # Default
}
```

---

## 🎯 Algoritmo de Scoring de Resultados

```php
function gf_calculate_score($product, $avatar) {
    $score = 0;
    
    // 1. Match de Intereses (40%)
    $product_interests = wp_get_post_terms($product->ID, 'gf_interest', ['fields' => 'slugs']);
    $match_count = count(array_intersect($product_interests, $avatar['intereses']));
    $score += ($match_count / count($avatar['intereses'])) * 40;
    
    // 2. Match de Precio (30%)
    $price = get_product_price($product->ID);
    if($price >= $avatar['precio_min'] && $price <= $avatar['precio_max']) {
        $score += 30;
    } elseif($price < $avatar['precio_min'] * 0.8 || $price > $avatar['precio_max'] * 1.2) {
        $score += 10; // Penalización leve
    } else {
        $score += 20;
    }
    
    // 3. Match de Edad (20%)
    $product_ages = wp_get_post_terms($product->ID, 'gf_age_range', ['fields' => 'slugs']);
    if(in_array($avatar['edad_slug'], $product_ages) || in_array('all', $product_ages)) {
        $score += 20;
    }
    
    // 4. Popularidad/Calidad (10%)
    $gift_score = get_post_meta($product->ID, 'gift_score', true);
    $score += min(10, $gift_score);
    
    // PENALIZACIONES
    // Si el producto está en categorías prohibidas
    if(array_intersect($product_interests, $avatar['categorias_prohibidas'])) {
        $score = 0; // Descartado totalmente
    }
    
    return $score;
}
```

---

## 📋 TODO - Implementación Pendiente

### Fase 1: Frontend Inteligente
- [ ] Refactorizar frontend-ui.php con flujo condicional
- [ ] Implementar lógica de avatar en JavaScript
- [ ] Mostrar solo intereses válidos según perfil
- [ ] Pre-ajustar slider de precio según relación

### Fase 2: Backend de Perfilado
- [ ] Crear taxonomía `gf_age_range`
- [ ] Crear taxonomía `gf_occasion`
- [ ] Actualizar Hunter para clasificar edad/ocasión
- [ ] Implementar scoring en búsqueda

### Fase 3: Sistema de Leads
- [ ] Crear tabla `gf_reminders`
- [ ] Implementar formulario de captura contextual
- [ ] Crear cron de envío de recordatorios
- [ ] Diseñar emails de recordatorio

### Fase 4: Optimización
- [ ] A/B testing de flujo
- [ ] Analytics de conversión
- [ ] Refinamiento de matriz perfil→intereses

---

## License

Private
