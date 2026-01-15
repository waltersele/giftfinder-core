# ✅ DIAGNOSTICO COMPLETADO - SIGUIENTE PASO

## 📊 Lo que dice verify.php

```
✓ WordPress cargado correctamente
✓ Post type 'gf_gift' registrado  
✓ Tablas de BD creadas (55 + 51 + 12 registros)
✓ Archivos del plugin en su lugar
✓ Taxonomías configuradas

❌ PROBLEMA: Faltan los TOKENS DE CONFIGURACIÓN
   - WP_API_TOKEN (vacío)
   - GEMINI_API_KEY (vacío)
   - AMAZON_TAG (vacío)
```

---

## 🎯 POR QUÉ ESTO ES UN PROBLEMA

Sin los tokens:
- Hunter.py **no puede autenticarse** en tu API
- Los productos **se rechazan** antes de guardarse
- Tu **afiliado de Amazon no se registra** en los enlaces

---

## ⚡ SOLUCIÓN (10 MINUTOS)

### PASO 1: Generar Token Seguro

Abre PowerShell y ejecuta:

```powershell
cd D:\HunterScrap
.\config-helper.ps1 generate
```

**Esto automáticamente:**
- Genera un token seguro de 32 caracteres
- Lo copia al clipboard
- Te muestra instrucciones

**Resultado:**
```
✓ Token generado exitosamente:
aB3cD9eF7gH2iJ8kL1mN4oP6qR5sTu0v

✓ Token copiado al clipboard
```

### PASO 2: Guardar en WordPress

1. **Abre WordPress Admin:**
   ```
   https://giftia.es/wp-admin
   ```

2. **Menú izquierdo → Products → ⚙️ Configuración**

3. **Campo 1: Token de API (WP_API_TOKEN)**
   - Pega el token que acabas de generar
   - Ej: `aB3cD9eF7gH2iJ8kL1mN4oP6qR5sTu0v`

4. **Campo 2: ID de Afiliado Amazon (AMAZON_TAG)**
   - Ve a: https://associates.amazon.es
   - Copia tu código (ej: `giftia0-21`)
   - Pégalo aquí

5. **Campo 3: Clave API Gemini (GEMINI_API_KEY) - OPCIONAL**
   - Ve a: https://ai.google.dev
   - Copia una clave
   - O deja vacío (descripciones genéricas)

6. **Checkbox: Modo Debug**
   - Marca: ☑️ Habilitar modo debug
   - (Lo desmarcas después)

7. **Botón azul: 💾 Guardar Configuración**

**Resultado esperado:**
```
✅ Variables de entorno guardadas correctamente.
```

### PASO 3: Verificar que Funcionó

```powershell
# En navegador, abre:
https://giftia.es/wp-content/plugins/giftfinder-core/verify.php
```

**Deberías ver ahora:**
```
✓ WP_API_TOKEN: aB3cD9...
✓ GEMINI_API_KEY: (vacío si no lo pusiste)
✓ AMAZON_TAG: giftia0-21

RESUMEN:
- Éxitos: 7 ✓
- Problemas: 0
```

### PASO 4: Ejecutar Hunter.py

Una vez que verify.php muestra todo ✓:

```powershell
cd D:\HunterScrap
python3 hunter.py
```

**Esto tardará 20-30 minutos** y mostrará:
```
[HUNTER] INICIANDO v8.0
[SEARCH] Buscando: gadgets tecnologicos...
[OK]: Apple AirPods... guardado
[OK]: Samsung Smart TV... guardado
...
```

---

## 📋 RESUMEN RÁPIDO

| Paso | Acción | Tiempo |
|------|--------|--------|
| 1 | Ejecutar config-helper.ps1 | 2 min |
| 2 | Rellenar formulario en WordPress | 5 min |
| 3 | Verificar con verify.php | 2 min |
| 4 | Ejecutar Hunter.py | 20-30 min |

**Total:** 30-40 minutos para tener productos en WordPress

---

## 🆘 SI ALGO FALLA

### verify.php sigue mostrando ✗

**Causa:** Los valores no se guardaron

**Soluciones:**
1. Verifica que **NO hay espacios en blanco** al final
2. Recarga la página (Ctrl+F5)
3. Espera 10 segundos
4. Si aún falla: Revisa `/wp-content/debug.log`

### "No encuentro ⚙️ Configuración en WordPress"

**Causa:** El plugin podría no estar activo

**Solución:**
1. WordPress Admin → Plugins
2. Busca "GiftFinder Core"
3. Si dice "Deactivate": Está activo ✓
4. Si dice "Activate": Haz clic en Activate
5. Intenta de nuevo

### Hunter.py devuelve error de autenticación

**Causa:** El token en WordPress no coincide

**Solución:**
1. Genera un token nuevo: `.\config-helper.ps1 generate`
2. Ve a WordPress → ⚙️ Configuración
3. Reemplaza el token
4. Guarda
5. Intenta Hunter.py de nuevo

---

## 📚 DOCUMENTACIÓN ADICIONAL

Si necesitas más detalles:

| Documento | Propósito |
|-----------|----------|
| `CONFIGURAR_TOKENS.md` | Explicación detallada de cada token |
| `INSTRUCCIONES_FINALES.md` | Guía completa con troubleshooting |
| `QUICK_START.md` | Checklist rápido |

**Ubicación:** `c:\webproject\giftia\giftfinder-core\`

---

## 🚀 EMPEZAR AHORA

```powershell
# 1. Generar token
cd D:\HunterScrap
.\config-helper.ps1 generate

# 2. [Ir a WordPress y guardar tokens]

# 3. Verificar
https://giftia.es/wp-content/plugins/giftfinder-core/verify.php

# 4. Ejecutar Hunter
python3 hunter.py
```

---

**Estado del Sistema:** Listo ✅  
**Siguiente Paso:** Configurar tokens ⚙️  
**Tiempo Estimado:** 10 minutos
