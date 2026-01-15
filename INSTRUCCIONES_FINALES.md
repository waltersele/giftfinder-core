# 🚀 INSTRUCCIONES FINALES - CÓMO ARREGLAR GIFTIA

## 📌 Resumen Rápido

Se han resuelto los problemas de Unicode de Hunter.py y se han creado herramientas de diagnóstico. Ahora necesitas:

1. **Verificar** que el sistema está configurado correctamente (2 minutos)
2. **Probar** que la API funciona con un producto de prueba (3 minutos)
3. **Ejecutar** Hunter.py para buscar productos (15-30 minutos)

---

## ✅ PASO 1: VERIFICACIÓN DEL SISTEMA

### Opción A: Desde el Navegador (Recomendado)

1. Abre en tu navegador:
   ```
   https://giftia.es/wp-content/plugins/giftfinder-core/verify.php
   ```

2. Deberías ver una lista de verificaciones:
   ```
   ✓ WordPress cargado
   ✓ Post type 'gf_gift' registrado
   ✓ wp_gf_products_ai (N registros)
   ✓ wp_gf_affiliate_offers (N registros)
   ✓ wp_gf_price_logs (N registros)
   ✓ WP_API_TOKEN: ...
   ```

   **Si ves todos ✓:** Continúa al PASO 2
   
   **Si ves ✗ en algo:**
   - Si "WordPress cargado" tiene ✗: El plugin no está bien instalado
   - Si "Post type no registrado" tiene ✗: Desactiva y reactiva el plugin
   - Si "Tablas no existen" tiene ✗: Reactiva el plugin (debe crear las tablas automáticamente)

### Opción B: Desde PowerShell (Si prefieres terminal)

```powershell
cd D:\HunterScrap
.\troubleshoot.ps1
# Elige opción 1 (CHECK)
```

---

## 🧪 PASO 2: PROBAR LA API

### Opción A: Desde el Navegador (Más simple)

1. Ve a:
   ```
   https://giftia.es/wp-content/plugins/giftfinder-core/test.php
   ```

2. En la página verás un formulario
3. Copia el token de WordPress Admin (Settings)
4. Pégalo en el formulario
5. Haz clic en "📤 Enviar Producto de Prueba"

**Resultado esperado:**
```
HTTP Status: 200
{"success": true, "post_id": 12345, ...}
```

**Si ves HTTP 200:**
- ✅ ¡La API funciona!
- Ahora ve a WordPress Admin → Products → All Gifts
- Deberías ver "Test Product - AirPods Pro"

**Si ves HTTP 403 (Forbidden):**
- ❌ Token incorrecto
- Solución: Ve a WordPress Admin, busca Settings, copia el token exactamente

**Si ves HTTP 500:**
- ❌ Error en el servidor
- Solución: Revisa `wp-content/debug.log` para ver el error

### Opción B: Desde PowerShell (Si tienes Python)

```powershell
cd D:\HunterScrap
python3 test_api.py --token=TU_TOKEN_AQUI
```

Reemplaza `TU_TOKEN_AQUI` con tu token real.

---

## 🐍 PASO 3: EJECUTAR HUNTER.PY

### Opción A: Desde PowerShell (Recomendado)

```powershell
cd D:\HunterScrap
python3 hunter.py
```

La ejecución debería mostrar:
```
[HUNTER] INICIANDO v8.0
[HUNTER] API Endpoint: https://giftia.es/...
[SEARCH] Buscando: gadgets tecnologicos...
[OK]: [PRODUCTO 1] guardado
[OK]: [PRODUCTO 2] guardado
...
```

### Opción B: Desde el Batch (Windows clásico)

```bash
cd D:\HunterScrap
python3 hunter.py
```

O doble-clic en un archivo `.bat` si lo creaste.

### Monitoreo de la Ejecución

Mientras Hunter.py se ejecuta, abre otra terminal y observa:

```powershell
# Terminal 1: Ver hunter.py en acción
Get-Content "D:\HunterScrap\hunter.log" -Tail 20 -Wait

# Terminal 2: Ver errores de WordPress
Get-Content "C:\webproject\giftia\wp-content\debug.log" -Tail 20 -Wait
```

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### "Hunter.py se bloquea o no envía nada"

**Síntomas:**
- Hunter.py se ejecuta pero no sale "[OK]:" messages
- No hay líneas nuevas en hunter.log después de 1 minuto

**Soluciones:**
1. Asegúrate de que test.php funciona primero (HTTP 200)
2. Verifica que el token es correcto
3. Revisa hunter.log: `Get-Content D:\HunterScrap\hunter.log -Tail 30`
4. Revisa debug.log: `Get-Content C:\webproject\giftia\wp-content\debug.log -Tail 30`

### "HTTP 500 en test.php"

**Causa más probable:** WordPress no carga en api-ingest.php

**Solución:**
1. Verifica tu estructura de carpetas de WordPress
2. Edita `c:\webproject\giftia\giftfinder-core\api-ingest.php`
3. Busca línea 12-14 y ajusta las rutas a wp-load.php:
   ```php
   $wp_load_paths = [
       $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
       'C:/webproject/giftia/wp-load.php',  // Ajusta según tu estructura
   ];
   ```

### "Products no aparecen en WordPress aunque test.php dice HTTP 200"

**Causa:** Las taxonomías/categorías pueden no estar registradas

**Solución:**
1. Desactiva el plugin: WordPress Admin → Plugins → GiftFinder Core → Deactivate
2. Reactívalo: Plugins → GiftFinder Core → Activate
3. Espera 10 segundos
4. Ve a WordPress Admin → Products → All Gifts
5. Si aún no aparecen: Revisa debug.log

### "UnicodeEncodeError en Hunter.py"

**Este problema ya debería estar resuelto**, pero si aparece de nuevo:

```python
# Abre D:\HunterScrap\hunter.py
# Busca la línea ~45 y asegúrate que dice:
if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')
```

---

## 📊 HERRAMIENTAS DISPONIBLES

| Herramienta | Ubicación | Propósito |
|-------------|-----------|----------|
| **verify.php** | `/giftfinder-core/verify.php` | Verificar configuración (2 min) |
| **test.php** | `/giftfinder-core/test.php` | Probar API con producto (3 min) |
| **debug.php** | `/giftfinder-core/debug.php` | Diagnóstico detallado (JSON) |
| **test_api.py** | `D:\HunterScrap\test_api.py` | Test desde Python |
| **troubleshoot.ps1** | `D:\HunterScrap\` | Menú interactivo PowerShell |
| **troubleshoot.bat** | `D:\HunterScrap\` | Menú interactivo Batch |

---

## ✅ CHECKLIST FINAL

Antes de ejecutar Hunter.py por primera vez:

```
[ ] 1. Accedí a verify.php y todo muestra ✓
[ ] 2. Accedí a test.php y envié producto de prueba
[ ] 3. test.php devolvió HTTP 200
[ ] 4. El producto de prueba aparece en WordPress Admin
[ ] 5. Ejecuté troubleshoot.ps1 opción 1 (diagnóstico completo)
```

**Si todos los checkmarks están marcados:** 

```powershell
# ¡Estás listo para ejecutar Hunter.py!
cd D:\HunterScrap
python3 hunter.py
```

---

## 🎯 PRÓXIMAS ACCIONES

### Si todo funciona (HTTP 200 en test.php)
```
1. Ejecuta Hunter.py
2. Espera 15-30 minutos
3. Revisa WordPress Admin → Products → All Gifts
4. Deberías ver nuevos productos con nombres como "[BUSQUEDA] - Título del Producto"
```

### Si algo no funciona
```
1. Ejecuta troubleshoot.ps1 opción 1 (diagnóstico completo)
2. Copia el error exacto
3. Revisa debug.log y hunter.log
4. Busca en QUICK_START.md la solución para tu error específico
```

---

## 📞 INFORMACIÓN DE CONTACTO

Si necesitas ayuda:
1. Revisa: `QUICK_START.md` (guía completa)
2. Revisa: `RESUMEN_ACCIONES.md` (lo que se hizo)
3. Ejecuta: `verify.php` (diagnóstico automático)

---

**Versión:** 6.1  
**Última actualización:** 2024  
**Estado:** Listo para usar

Buena suerte y que funcione todo perfecto 🎉
