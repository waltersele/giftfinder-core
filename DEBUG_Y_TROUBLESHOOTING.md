# 🔧 GUÍA DE DEBUGGING - HUNTER NO ESTÁ GUARDANDO PRODUCTOS

## El Problema
- Hunter.py se ejecuta correctamente
- Logs de Hunter muestran búsquedas y intentos de envío
- **PERO**: Productos NO aparecen en WordPress (vacío)
- Error de Unicode en Windows con emojis

## Lo que Hemos Arreglado Hoy

### 1. ✅ ERROR UNICODE EN HUNTER.PY
**Problema**: `UnicodeEncodeError: 'charmap' codec can't encode character '\U0001f3f9'`

**Causa**: Windows PowerShell usa encoding cp1252 que no soporta emojis

**Solución aplicada**:
```python
# Force UTF-8 output on Windows
if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

# Logging con UTF-8 explícito
logging.FileHandler('hunter.log', encoding='utf-8')
```

**Resultado**: El error de logging desaparecerá. Hunter funcionará sin errores de encoding.

### 2. ✅ MEJORADO LOGGING EN HUNTER.PY
Se reemplazaron emojis con etiquetas texto para mejor legibilidad:
- `🏹` → `[HUNTER]`
- `🔍` → `[SEARCH]`
- `🚀` → `ENVIANDO`
- `✅` → `[OK]`
- `❌` → `[ERROR]`

Se añadió logging detallado en `send_to_giftia()`:
```
ENVIANDO [Score:87] AirPods Pro... vibes=['Tech']
POST a https://giftia.es/...
Token: nu27OrX...
Respuesta status: 200
OK: AirPods Pro... guardado en WordPress
```

### 3. ✅ MEJORADO install.php
**Problema**: Tablas podrían no crearse correctamente

**Solución**:
- Cambiar `CREATE TABLE` → `CREATE TABLE IF NOT EXISTS`
- Agregar índices a columnas importantes
- Ejecutar `gf_create_custom_tables()` automáticamente en `plugins_loaded`
- Verificar tabla existe cada vez que se carga el plugin

**Resultado**: Las tablas se crearán automáticamente si no existen

### 4. ✅ MEJORADO api-ingest.php
Agregado logging detallado en cada paso:

```php
// Log de solicitud
[GIFTIA-API] Solicitud recibida: POST desde 192.168.1.100

// Log de validación
[GIFTIA-API] Datos recibidos: {"title": "AirPods Pro"...

// Log de creación de post
[GIFTIA-API] Creando producto nuevo...
[GIFTIA-API] Producto creado: 1234

// Log de taxonomías
[GIFTIA-API] Asignando vibes: Tech, Friki
```

También mejorada la carga de WordPress y manejo de errores.

### 5. ✅ CREADO debug.php
Archivo para verificar estado del sistema:

```bash
curl https://tu-dominio.com/wp-content/plugins/giftfinder-core/debug.php
```

Retorna JSON con:
- ¿Post type gf_gift registrado?
- ¿Cuántos productos hay?
- ¿Existen las tablas?
- ¿Las taxonomías están creadas?
- ¿Los .env se están cargando?

## 🔍 AHORA DEBES HACER ESTO:

### Paso 1: Subir archivos actualizados a tu servidor
```
Archivos modificados:
- hunter.py (logging mejorado)
- api-ingest.php (logs detallados)
- install.php (tabla robusta)
- debug.php (NEW - para verificar)
```

### Paso 2: Activar el plugin nuevamente
```
Dashboard → Plugins → GiftFinder → Deactivate → Activate
```

Esto forzará que se creen las tablas nuevamente.

### Paso 3: Verificar estado
Accede a:
```
https://tu-dominio.com/wp-content/plugins/giftfinder-core/debug.php
```

Debe mostrar algo como:
```json
{
  "post_type_registered": true,
  "total_products": 0,
  "products": [],
  "tables": {
    "gf_products_ai": "gf_products_ai",
    "gf_affiliate_offers": "gf_affiliate_offers",
    "gf_price_logs": "gf_price_logs"
  },
  "taxonomies": {
    "gf_interest": true,
    "gf_recipient": true,
    "gf_occasion": true,
    "gf_budget": true
  },
  "env_vars": {
    "WP_API_TOKEN": "***set***",
    "GEMINI_API_KEY": "NOT SET",
    "AMAZON_TAG": "GIFTIA-21"
  }
}
```

**Si algo es `false` o `NOT FOUND`**: Hay un problema que necesita arreglo.

### Paso 4: Ejecutar Hunter nuevamente
```bash
python3 D:\HunterScrap\hunter.py
```

Ahora los logs serán MUCHO más detallados:
```
[HUNTER] INICIANDO v8.0 - Advanced Gift Discovery Engine
[VIBES] Selected: ['Tech', 'Friki']
[SEARCH] [Tech] gadgets tecnologicos innovadores...
...
ENVIANDO [Score:87] AirPods Pro... vibes=['Tech']
POST a https://giftia.es/wp-content/plugins/giftfinder-core/api-ingest.php
Token: nu27OrX2t5...
Datos: {"title": "AirPods Pro"...
Respuesta status: 200
OK: AirPods Pro... guardado en WordPress
...
[DONE] Session completed!
   Sent: 24
   Discarded: 18
   Success rate: 57.1%
```

### Paso 5: Verificar WordPress
```
Products → All Gifts
```

Deben aparecer nuevos productos de Amazon con:
- ✅ Título correcto
- ✅ Precio
- ✅ Imagen
- ✅ Vibes asignados (Tech, Friki, etc)
- ✅ Status: Published

### Paso 6: Ver logs en WordPress
```
wp-content/debug.log       # Errores generales WordPress
wp-content/giftia-debug.log # Logs de Giftia API
hunter.log                  # Logs de Hunter
```

## 🚨 SI SIGUE SIN FUNCIONAR:

### Check 1: ¿Token es correcto?
```bash
# En Hunter logs, debe verte:
POST a https://giftia.es/wp-content/plugins/giftfinder-core/api-ingest.php
Token: nu27OrX2t5VZQmrGXfoZk3pbcS97yiP5

# En debug.php debe ser "***set***":
"WP_API_TOKEN": "***set***"

# Si NO coinciden: copiar token de admin panel:
Products → ⚙️ Configuración → 🔐 Token de API
```

### Check 2: ¿Respuesta de API es 200?
En hunter logs, después de cada envío debe verse:
```
Respuesta status: 200
OK: [título]... guardado en WordPress
```

Si ves `403`, `400` o `500`: hay error en la API.

### Check 3: ¿Tablas existen?
```
debug.php debe mostrar:
"gf_products_ai": "gf_products_ai",
```

Si muestra `null`: tablas no se crearon. Reactivar plugin.

### Check 4: ¿Post type existe?
```
debug.php debe mostrar:
"post_type_registered": true,
```

Si es `false`: plugin no se cargó. Buscar errores en `/wp-content/debug.log`.

## 📝 NUEVOS ARCHIVOS/CAMBIOS

```
Modificados:
✅ hunter.py              (450+ líneas, mejor logging)
✅ api-ingest.php         (logs detallados en cada paso)
✅ install.php            (tablas robustas)

Nuevos:
✅ debug.php              (verificar estado del sistema)
```

## ⚠️ IMPORTANTE

Si Hunter sigue sin guardar productos después de todo esto, el problema probablemente sea:

1. **La API endpoint no es accesible**
   - Hunter no puede conectar a `https://tu-dominio.com/wp-content/plugins/giftfinder-core/api-ingest.php`
   - Verificar dominio correcto, SSL válido, archivo existe

2. **WordPress no se carga en api-ingest.php**
   - Ver error en `/wp-content/debug.log`
   - Probablemente paths incorrectos

3. **Las funciones giftia-utils.php o giftia-config.php no existen**
   - Verificar archivos están en `/config/` e `/includes/`

---

**Próximo paso**: Ejecuta Hunter nuevamente y **comparte los logs detallados** si sigue sin funcionar.
