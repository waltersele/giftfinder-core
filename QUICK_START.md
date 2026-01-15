# 🚀 GUÍA RÁPIDA DE DIAGNÓSTICO Y CORRECCIÓN - GIFTIA

## El Problema
- Hunter.py ahora funciona correctamente (sin errores Unicode)
- Los productos NO aparecen en WordPress
- El error 500 en debug.php indica un problema de carga

---

## ✅ PASO 1: VERIFICAR ESTADO DEL SISTEMA (2 minutos)

### Opción A: Verificación Simple (Recomendado)
```
Abre en tu navegador:
https://giftia.es/wp-content/plugins/giftfinder-core/verify.php
```

**Qué buscar:**
- ✓ WordPress cargado
- ✓ Post type 'gf_gift' registrado  
- ✓ Tablas de BD creadas
- ✓ Tokens configurados

Si ves ✗ en algo, el script te dice qué hacer.

---

## ✅ PASO 2: ENVIAR PRODUCTO DE PRUEBA (3 minutos)

Usa test.php para enviar un producto manualmente:
```
https://giftia.es/wp-content/plugins/giftfinder-core/test.php
```

**Instrucciones:**
1. Ve a WordPress → Admin Settings
2. Copia el token desde la sección de API
3. Pégalo en el formulario de test.php
4. Haz clic en "📤 Enviar Producto de Prueba"
5. Si ves HTTP Status 200: ¡ÉXITO! El producto debería aparecer en WordPress
6. Si ves error: lee el mensaje para saber qué está mal

---

## ✅ PASO 3: EJECUTAR HUNTER.PY (Cuando Paso 1-2 funcionen)

```bash
cd D:\HunterScrap
python3 hunter.py
```

**Monitorea:**
- Mira la salida en pantalla para ver si está enviando datos
- Revisa D:\HunterScrap\hunter.log para detalles
- Esperado: `[OK]: Producto guardado` o similar

---

## 🔍 DIAGNOSIS: ¿DÓNDE ESTÁ EL PROBLEMA?

### Si verify.php muestra ✗ WordPress cargado
**Causa:** Las rutas a wp-load.php son incorrectas  
**Solución:**
1. Verifica la estructura de carpetas de tu WordPress
2. Encuentra dónde está exactamente wp-load.php
3. Edita `api-ingest.php` línea 12-14 con las rutas correctas

### Si verify.php muestra ✗ Post type no registrado
**Causa:** El plugin no se activó correctamente  
**Solución:**
1. Ve a Plugins en WordPress Admin
2. Busca "GiftFinder Core" o "giftfinder-core"
3. Si está desactivado: clic en "Activate"
4. Si está activo: clic en "Deactivate" → "Activate"
5. Vuelve a ejecutar verify.php

### Si verify.php muestra ✗ Tablas no existen
**Causa:** Las tablas no se crearon  
**Solución:**
1. Desactiva el plugin: Plugins → Deactivate
2. Actívalo de nuevo: Plugins → Activate
3. Vuelve a ejecutar verify.php
4. Si persiste: Ve a WordPress Tools → My SQL Client y ejecuta:
```sql
-- Ver si existen las tablas
SHOW TABLES LIKE 'wp_gf_%';
```

### Si test.php devuelve HTTP 403 (Forbidden)
**Causa:** Token incorrecto  
**Solución:**
1. Ve a WordPress Admin → Settings (o similar)
2. Busca la sección API
3. Copia el token exactamente como aparece
4. Pégalo en test.php sin espacios

### Si test.php devuelve HTTP 500
**Causa:** Error en la API  
**Solución:**
1. Abre wp-content/debug.log (últimas líneas)
2. Busca errores con "[GIFTIA-API]"
3. Si dice tabla no existe: ejecuta paso 3 del diagnosis anterior
4. Si dice post type no existe: ejecuta paso 2 del diagnosis anterior

### Si test.php devuelve HTTP 200 pero el producto no aparece en WordPress
**Causa:** Post type sí existe, pero puede haber problema con categorías/taxonomías  
**Solución:**
1. Ve a WordPress Admin → Products → All Gifts
2. Si ves "Test Product - AirPods Pro": ¡El problema está casi resuelto!
3. Si no aparece: Revisa wp-content/debug.log para ver el error exacto

---

## 📋 CHECKLIST FINAL

Antes de ejecutar Hunter.py, verifica:

- [ ] verify.php muestra ✓ WordPress cargado
- [ ] verify.php muestra ✓ Post type 'gf_gift' registrado
- [ ] verify.php muestra ✓ Tablas de BD (3 tablas con ✓)
- [ ] verify.php muestra ✓ Configuración (WP_API_TOKEN configurado)
- [ ] test.php puede enviar producto (HTTP 200)
- [ ] El producto de test.php aparece en WordPress Admin

**Si todos están ✓:** Ejecuta `python3 D:\HunterScrap\hunter.py`

**Si alguno está ✗:** Sigue las soluciones en la sección "DIAGNOSIS" arriba

---

## 📞 ARCHIVOS IMPORTANTES

| Archivo | Propósito | Ubicación |
|---------|-----------|-----------|
| verify.php | Diagnóstico simple | /wp-content/plugins/giftfinder-core/verify.php |
| test.php | Enviar producto de prueba | /wp-content/plugins/giftfinder-core/test.php |
| debug.php | Diagnóstico detallado (JSON) | /wp-content/plugins/giftfinder-core/debug.php |
| api-ingest.php | Endpoint que recibe productos | /wp-content/plugins/giftfinder-core/api-ingest.php |
| hunter.py | Script de búsqueda | D:\HunterScrap\hunter.py |
| hunter.log | Log de Hunter | D:\HunterScrap\hunter.log |
| debug.log | Log de WordPress | /wp-content/debug.log |

---

## 💡 CONSEJOS ÚTILES

**¿Cómo habilitar debug.log?**
```php
// En wp-config.php, cambia esto:
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);

// A esto:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**¿Cómo ver logs en tiempo real?**
```bash
# En terminal/PowerShell:
tail -f "D:\HunterScrap\hunter.log"
# O en Windows:
Get-Content "D:\HunterScrap\hunter.log" -Tail 20 -Wait
```

**¿Cómo resetear todo?**
```bash
# 1. Desactiva el plugin en WordPress Admin
# 2. Ejecuta esto en terminal:
mysql -u user -p
use giftia;
DELETE FROM wp_gf_products_ai;
DELETE FROM wp_gf_affiliate_offers;
DELETE FROM wp_gf_price_logs;
DELETE FROM wp_posts WHERE post_type = 'gf_gift';
# 3. En WordPress: Plugins → Activate GiftFinder Core
# 4. Vuelve a ejecutar verify.php
```

---

**Última actualización:** 2024  
**Versión:** 6.1  
**Status:** Producción
