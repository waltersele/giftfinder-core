# 🧪 GUÍA COMPLETA DE TESTING E INSTALACIÓN - GIFTIA v8.0

## PARTE 1: INSTALACIÓN EN WORDPRESS (FileZilla Ready)

### 📋 Requisitos Previos
- WordPress 5.0+ instalado
- PHP 7.4+
- MySQL/MariaDB
- Acceso FTP/SFTP (FileZilla)
- Clave de API Google Gemini
- ID de afiliado Amazon

### 🚀 Pasos de Instalación

#### 1. Preparar el Plugin Localmente

```bash
# En tu máquina local:
cd giftfinder-core/
# Crear carpeta del plugin
mkdir -p ~/Desktop/giftfinder-core-deploy/giftfinder-core

# Copiar todos los archivos
cp -r . ~/Desktop/giftfinder-core-deploy/giftfinder-core/

# Verificar estructura
ls -la ~/Desktop/giftfinder-core-deploy/giftfinder-core/
```

**Estructura esperada:**
```
giftfinder-core/
├── giftfinder-core.php          (Plugin principal)
├── env-loader.php               (Cargador de .env)
├── api-ingest.php               (API para Hunter)
├── admin-settings.php           (Panel de configuración)
├── frontend-ui.php              (UI del selector de regalos)
├── config/
│   └── giftia-config.php       (Configuración centralizada)
├── includes/
│   └── giftia-utils.php        (Funciones utilitarias)
├── .env.example                 (Plantilla de variables)
├── GUIA_TESTING_E_INSTALACION.md
└── error_log
```

#### 2. Subir via FileZilla

1. **Abrir FileZilla**
   - Host: tu-ftp.dominio.com (SFTP recomendado)
   - Usuario: tu_usuario_ftp
   - Contraseña: tu_contraseña
   - Puerto: 21 (FTP) o 22 (SFTP)

2. **Navegar a la carpeta de plugins**
   - En FileZilla lado remoto: `/wp-content/plugins/`

3. **Subir el plugin**
   - Arrastrar `~/Desktop/giftfinder-core-deploy/giftfinder-core/` 
   - A la carpeta remota `/wp-content/plugins/`

4. **Verificar upload**
   ```
   /wp-content/plugins/giftfinder-core/
   ├── giftfinder-core.php
   ├── env-loader.php
   ├── api-ingest.php
   ├── etc...
   ```

#### 3. Configurar el Archivo .env

**Opción A: Crear manualmente vía FileZilla**

1. En FileZilla, crear nuevo archivo: `Right Click → Create File → .env`
2. Copiar contenido de `.env.example`:

```env
WP_API_TOKEN=secreto_super_seguro_aqui_32_caracteres
GEMINI_API_KEY=AIzaSyD_tu_clave_api_gemini_aqui
AMAZON_TAG=GIFTIA-21
ALLOWED_ORIGINS=["https://giftia.es","https://www.giftia.es"]
DEBUG=0
```

3. Guardar en: `/` (WordPress root) o `/wp-content/`

**Opción B: Crear vía WordPress Admin Panel (Recomendado)**

1. En WordPress Dashboard
2. Ir a: `Products → ⚙️ Configuración`
3. Completar todos los campos
4. Click: `💾 Guardar Configuración`

El sistema creará automáticamente el archivo `.env`

#### 4. Activar el Plugin

1. WordPress Admin → Plugins
2. Buscar "Giftia" o "GiftFinder"
3. Click: `Activate`
4. Ir a: `Products → ⚙️ Configuración` para verificar setup

#### 5. Permisos de Archivo

Via SSH o FileZilla, asegurar permisos:

```bash
# Conectar vía SSH
ssh usuario@tu-servidor.com

# Ajustar permisos
chmod 755 /var/www/html/wp-content/plugins/giftfinder-core/
chmod 644 /var/www/html/wp-content/plugins/giftfinder-core/*
chmod 666 /var/www/html/.env          # Si está en raíz de WP
```

---

## PARTE 2: TESTING DEL FLUJO COMPLETO

### 🧪 Test 1: Verificar Carga de Variables de Entorno

**Objetivo:** Confirmar que el .env se carga correctamente

**Pasos:**

1. **Editar `giftfinder-core.php` temporalmente:**

```php
// Añadir al inicio del archivo, después del header
echo '<pre>DEBUG: '; print_r(giftia_env('WP_API_TOKEN')); echo '</pre>';
echo '<pre>DEBUG: '; print_r(giftia_env('AMAZON_TAG')); echo '</pre>';
```

2. **Acceder a cualquier página de WordPress**

3. **Esperado:** Ver output como:
```
DEBUG: secreto_super_seguro_aqui_32_caracteres
DEBUG: GIFTIA-21
```

4. **Si no aparece:** Verificar:
   - Archivo `.env` existe en `/` o `/wp-content/`
   - Permisos del archivo son legibles
   - Sintaxis correcta (`KEY=value`)

### 🧪 Test 2: Verificar API Token

**Objetivo:** Confirmar que el token es accesible desde la API

**Pasos:**

1. **Desde terminal/PowerShell:**

```bash
# Obtener el token guardado
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: tu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```

2. **Esperado:** Respuesta JSON sin error de token

3. **Si error:** Verificar que `X-GIFTIA-TOKEN` header coincide con `WP_API_TOKEN` en `.env`

### 🧪 Test 3: Test de CORS

**Objetivo:** Validar whitelist de CORS

**Pasos:**

1. **Crear archivo HTML de test (`test-cors.html`):**

```html
<!DOCTYPE html>
<html>
<head>
    <title>CORS Test - Giftia</title>
</head>
<body>
<h1>Testing CORS to Giftia API</h1>
<button onclick="testCORS()">Send CORS Request</button>
<pre id="result"></pre>

<script>
async function testCORS() {
    try {
        const response = await fetch('https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-GIFTIA-TOKEN': 'tu_token_aqui'
            },
            body: JSON.stringify({
                title: 'Test Product',
                asin: '1234567890',
                price: '29.99',
                image_url: 'https://example.com/img.jpg',
                vendor: 'Amazon',
                affiliate_url: 'https://amazon.es/dp/1234567890'
            })
        });
        
        const data = await response.json();
        document.getElementById('result').textContent = JSON.stringify(data, null, 2);
    } catch(e) {
        document.getElementById('result').textContent = 'Error: ' + e.message;
    }
}
</script>
</body>
</html>
```

2. **Abrir en navegador desde tu dominio**

3. **Esperado:** Respuesta exitosa (status 200, sin CORS error)

4. **Si CORS error:** 
   - Verificar `ALLOWED_ORIGINS` en admin settings
   - Incluye tu dominio exacto: `https://tu-dominio.com`

### 🧪 Test 4: Test de Validación de Datos

**Objetivo:** Confirmar que la API valida correctamente

**Pasos:**

1. **Test ASIN inválido:**

```bash
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: tu_token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test",
    "asin": "ABC",              # ← Inválido, <10 chars
    "price": "29.99",
    "image_url": "https://example.com/img.jpg",
    "vendor": "Amazon",
    "affiliate_url": "https://amazon.es/dp/ABC"
  }'
```

**Esperado:** Error 400: "ASIN must be 10 alphanumeric characters"

2. **Test precio inválido:**

```bash
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: tu_token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test",
    "asin": "B000000000",
    "price": "not-a-price",     # ← Inválido
    "image_url": "https://example.com/img.jpg",
    "vendor": "Amazon",
    "affiliate_url": "https://amazon.es/dp/B000000000"
  }'
```

**Esperado:** Error 400: "Invalid price format"

### 🧪 Test 5: Test de Ingesta de Producto Válido

**Objetivo:** Verificar que un producto válido se crea correctamente

**Pasos:**

1. **Enviar producto válido:**

```bash
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: tu_token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "AirPods Pro - Auriculares Bluetooth Inalámbricos",
    "asin": "B08HXVQG7K",
    "price": "229.00",
    "image_url": "https://m.media-amazon.com/images/I/71234567890.jpg",
    "vendor": "Amazon",
    "affiliate_url": "https://amazon.es/dp/B08HXVQG7K?tag=GIFTIA-21",
    "description": "Auriculares premium con cancelación de ruido",
    "vibes": ["Tech"],
    "recipients": ["Tech Lover"]
  }'
```

2. **Esperado:** 
```json
{
  "success": true,
  "message": "Product created/updated successfully",
  "post_id": 12345
}
```

3. **Verificar en WordPress:**
   - Products → All Gifts
   - Buscar "AirPods Pro"
   - Debe estar published con vibes y recipients correctos

### 🧪 Test 6: Test de Fallback IA

**Objetivo:** Verificar que si Gemini falla, usa contenido fallback

**Pasos:**

1. **Temporalmente desactivar GEMINI_API_KEY** (dejar vacío en admin settings)

2. **Enviar producto:**

```bash
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: tu_token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Smartwatch Deportivo",
    "asin": "B08X1Z2Q3R",
    "price": "99.99",
    "image_url": "https://example.com/img.jpg",
    "vendor": "Amazon",
    "affiliate_url": "https://amazon.es/dp/B08X1Z2Q3R"
  }'
```

3. **Esperado:** Producto se crea con contenido genérico (no vacío)

4. **Verificar en WordPress:**
   - Products → All Gifts
   - El producto debe tener descripción aunque sea genérica
   - NO debe estar blank

### 🧪 Test 7: Test de Rate Limiting

**Objetivo:** Verificar que rate limiting funciona

**Pasos:**

1. **Enviar 101 requests en <1 segundo desde misma IP:**

```bash
for i in {1..101}; do
  curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
    -H "X-GIFTIA-TOKEN: tu_token" \
    -H "Content-Type: application/json" \
    -d '{"title": "Test'$i'", "asin": "B000000000", "price": "10", "image_url": "https://example.com/img.jpg", "vendor": "Amazon", "affiliate_url": "https://amazon.es"}' \
    2>/dev/null
done
```

2. **Esperado:** Después del request 100:
```json
{
  "error": "Rate limit exceeded",
  "status": 429
}
```

3. **Recuperación:** Esperar 1 hora (o cambiar IP)

### 🧪 Test 8: Test del Frontend UI

**Objetivo:** Verificar que el selector de regalos funciona

**Pasos:**

1. **Crear página de test:**
   - WordPress → Pages → New Page
   - Nombre: "Test Giftia"
   - Contenido: `[giftia_app]`
   - Publish

2. **Acceder a la página**

3. **Esperado:**
   - Interface dark mode carga
   - Botones de perfil (Pareja, Familia, etc.) visibles
   - Slider de precio funciona
   - Puedo seleccionar vibe y ver feed de productos

### 🧪 Test 9: Test de Hunter.py

**Objetivo:** Verificar que Hunter busca y envía productos correctamente

**Pasos:**

1. **Instalar dependencias (en máquina local o servidor):**

```bash
pip install selenium requests webdriver-manager
```

2. **Configurar variables de entorno:**

```bash
export WP_API_TOKEN="tu_token_aqui"
export WP_API_URL="https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php"
export AMAZON_TAG="GIFTIA-21"
export DEBUG="1"
```

3. **Ejecutar Hunter.py:**

```bash
python3 hunter.py
```

4. **Esperado:**
   - Logs de búsqueda: `🔍 [Tech] Buscando: gadgets tecnologicos innovadores...`
   - Logs de captura: `🚀 Enviando [Score:XX] Product Name → ['Tech']`
   - Al final: `📤 Sent: X, 🗑️ Discarded: Y`

5. **Verificar en WordPress:**
   - Products → All Gifts
   - Deben haber nuevos productos desde Amazon
   - Con vibes correctas y descripciones IA

### 🧪 Test 10: Test de Seguridad

**Objetivo:** Validar todas las medidas de seguridad

**Test 10.1: Token inválido**
```bash
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: token_invalido" \
  -H "Content-Type: application/json" \
  -d '{"title": "Test", ...}'
# Esperado: Error 403 "Token inválido"
```

**Test 10.2: CORS origin no permitido**
- Abrir `test-cors.html` desde dominio diferente
- Esperado: Error CORS (navegador lo bloquea)

**Test 10.3: SQL Injection**
```bash
curl -X POST https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php \
  -H "X-GIFTIA-TOKEN: tu_token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test\" OR 1=1--",
    "asin": "B000000000",
    ...
  }'
# Esperado: Se sanitiza, no causa SQL injection
```

---

## PARTE 3: TROUBLESHOOTING

### ❌ Problema: `.env` no se carga

**Síntomas:** Variables vacías, fallback a valores hardcoded

**Solución:**

1. Verificar archivo existe: `/` o `/wp-content/`
2. Verificar permisos: `chmod 644 .env`
3. Verificar sintaxis: `KEY=value` (sin espacios)
4. Check logs: `wp-content/giftia-debug.log`

### ❌ Problema: CORS error en frontend

**Síntomas:** "Access to XMLHttpRequest blocked by CORS policy"

**Solución:**

1. Admin Panel → Configuración
2. Añadir tu dominio exacto a "CORS - Orígenes Permitidos"
3. Guardar
4. Reload página en navegador (Ctrl+Shift+R para hard refresh)

### ❌ Problema: Hunter.py error "Token inválido"

**Síntomas:** `❌ API returned 403: Token inválido`

**Solución:**

1. Verificar `WP_API_TOKEN` en `.env` es correcto
2. Copiar token de Admin Panel → Configuración → Token de API
3. Pegar en `.env` o como variable de entorno

### ❌ Problema: No hay productos después de Hunter

**Síntomas:** Ejecuta sin errores pero WordPress no muestra productos

**Solución:**

1. Verificar en logs: `hunter.log`
2. Buscar líneas con "🚀 Enviando"
3. Si no hay: blacklist es muy agresivo
4. Si hay: comprobar scoring (línea de debug)

### ❌ Problema: IA no genera descripciones

**Síntomas:** Productos creados con descripción vacía

**Solución:**

1. Verificar `GEMINI_API_KEY` en Admin Panel
2. Test clave en: https://aistudio.google.com
3. Si falla: esperar fallback (debe crear contenido genérico)

---

## PARTE 4: CHECKLIST FINAL

- [ ] Plugin subido vía FileZilla a `/wp-content/plugins/giftfinder-core/`
- [ ] Plugin activado en WordPress
- [ ] Archivo `.env` creado con todas las variables
- [ ] Test 1: Variables .env se cargan ✅
- [ ] Test 2: Token API funciona ✅
- [ ] Test 3: CORS valida correctamente ✅
- [ ] Test 4: Validación de datos rechaza inválidos ✅
- [ ] Test 5: Producto válido se ingesta correctamente ✅
- [ ] Test 6: Fallback IA funciona ✅
- [ ] Test 7: Rate limiting funciona ✅
- [ ] Test 8: Frontend UI carga y funciona ✅
- [ ] Test 9: Hunter.py encuentra y envía productos ✅
- [ ] Test 10: Seguridad validada ✅

---

## 📞 SOPORTE RÁPIDO

**Log locations:**
- API: `wp-content/giftia-debug.log`
- Hunter: `hunter.log` (en carpeta de ejecución)
- WordPress: `wp-content/debug.log`

**Verificar config:**
```bash
wp option get gf_gemini_api_key       # Desde WP CLI
wp option get gf_amazon_tag
wp option get gf_ingest_secret_token
```

**Limpiar cache:**
```bash
# WordPress transients
wp transient delete-all

# Rate limiting cache
wp transient delete gf_rate_limit_*
```

