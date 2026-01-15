# 🔑 CÓMO CONFIGURAR LOS TOKENS DE GIFTIA

## ⚠️ PROBLEMA DETECTADO

Tu `verify.php` muestra:
```
✗ WP_API_TOKEN: (no configurado)
✗ GEMINI_API_KEY: (no configurado)
✗ AMAZON_TAG: (no configurado)
```

**Sin estos tokens, Hunter.py no puede enviar productos a WordPress.**

---

## ✅ SOLUCIÓN: Rellenar la Configuración

### PASO 1: Accede al Panel de Configuración

1. Ve a tu WordPress Admin: `https://giftia.es/wp-admin`
2. En el menú izquierdo, busca: **Products → ⚙️ Configuración**
3. Deberías ver un formulario con estos campos:
   - 🔐 Token de API (WP_API_TOKEN)
   - 🤖 Clave API Gemini (GEMINI_API_KEY)
   - 🛍️ ID de Afiliado Amazon (AMAZON_TAG)
   - 🌐 CORS - Orígenes Permitidos
   - 🐛 Modo Debug

---

## 🔐 CAMPO 1: Token de API (WP_API_TOKEN)

**¿Qué es?** Token secreto para autenticar Hunter.py cuando envía productos

**¿Cómo obtenerlo?**

Tienes dos opciones:

### Opción A: Generar uno nuevo (Recomendado)

Ejecuta este comando en PowerShell:

```powershell
# Generar token aleatorio de 32 caracteres
$token = -join ((65..90) + (97..122) + (48..57) | Get-Random -Count 32 | % {[char]$_})
$token
```

Esto generará algo como: `aB3cD9eF7gH2iJ8kL1mN4oP6qR5sTu0v`

### Opción B: Usar uno predeterminado (Para testing)

Si prefieres algo simple: `nu27OrX2t5VZQmrGXfoZk3pbcS97yiP5`

**Acción:** Copia el token y pégalo en el campo "Token de API" en WordPress Admin

---

## 🤖 CAMPO 2: Clave API Gemini (GEMINI_API_KEY)

**¿Qué es?** API key de Google Gemini para generar descripciones de productos automáticamente

**¿Es obligatorio?** NO - Si no lo configuras, usará descripciones genéricas

**¿Cómo obtenerlo?**

1. Ve a: https://ai.google.dev/
2. Haz clic en "Get API Key" 
3. Crear nuevo proyecto o usa uno existente
4. Copia la clave API
5. Pégala en el campo "Clave API Gemini" en WordPress Admin

**Si no quieres usar Gemini:**
- Deja el campo vacío
- El sistema usará descripciones básicas de los productos

---

## 🛍️ CAMPO 3: ID de Afiliado Amazon (AMAZON_TAG)

**¿Qué es?** Tu código de afiliado de Amazon Associates para ganar comisiones

**¿Es obligatorio?** Técnicamente NO, pero deberías configurarlo para ganar dinero

**¿Cómo obtenerlo?**

1. Accede a Amazon Associates: https://associates.amazon.es/
2. Ve a "Configuración" → "Identificadores de etiquetas"
3. Copia tu código (formato: `nombredominio-21`)
4. Pégalo en el campo "ID de Afiliado Amazon" en WordPress Admin

**Ejemplo:**
```
giftia0-21
midominio-21
misite-21
```

---

## 🌐 CAMPO 4: CORS - Orígenes Permitidos (ALLOWED_ORIGINS)

**¿Qué es?** Dominios que pueden enviar datos a tu API

**Por defecto incluye:**
- `https://giftia.es` (tu dominio)
- `http://localhost` (para testing local)

**¿Necesito cambiar esto?** Generalmente NO

**Si necesitas agregar otro dominio:**
Añade una línea por dominio:
```
https://example.com
https://test.example.com
```

---

## 🐛 CAMPO 5: Modo Debug (DEBUG)

**¿Qué es?** Activa logs detallados para troubleshooting

**¿Debo activarlo?** 
- ✓ SÍ mientras estés configurando (ahora)
- ✗ NO en producción

**Acciones:**
1. Marca la casilla "Habilitar modo debug"
2. Haz clic en "Guardar Configuración"
3. Los logs aparecerán en: `wp-content/giftia-debug.log`

---

## 📋 CHECKLIST DE CONFIGURACIÓN

Antes de hacer clic en "Guardar Configuración", verifica:

- [ ] **Token de API:** Relleno (32+ caracteres)
- [ ] **Clave Gemini:** Relleno (o vacío si no lo usas)
- [ ] **Amazon Tag:** Relleno con tu código (ej: `giftia0-21`)
- [ ] **CORS:** No modificado (mantienes el default)
- [ ] **Debug:** Marcado (para ahora, desmarca después)

---

## 💾 GUARDAR CONFIGURACIÓN

1. En WordPress Admin, en la página de **⚙️ Configuración**
2. Haz clic en azul: **"💾 Guardar Configuración"**
3. Deberías ver: ✅ Variables de entorno guardadas correctamente

---

## ✅ VERIFICAR QUE FUNCIONA

Después de guardar la configuración:

1. Abre en navegador:
   ```
   https://giftia.es/wp-content/plugins/giftfinder-core/verify.php
   ```

2. Deberías ver:
   ```
   ✓ WP_API_TOKEN: nu27Or...
   ✓ GEMINI_API_KEY: (si lo configuraste)
   ✓ AMAZON_TAG: giftia0-21
   ```

3. Si ves ✗ en algo: 
   - Vuelve a WordPress Admin
   - Verifica que no hay espacios en blanco
   - Guarda de nuevo

---

## 🚀 PRÓXIMOS PASOS

Una vez que todos los tokens estén ✓ en verify.php:

1. **Abre test.php:**
   ```
   https://giftia.es/wp-content/plugins/giftfinder-core/test.php
   ```

2. **Envía un producto de prueba**
   - Debería devolver HTTP 200
   - El producto debe aparecer en WordPress Admin

3. **Ejecuta Hunter.py:**
   ```powershell
   cd D:\HunterScrap
   python3 hunter.py
   ```

---

## 🆘 TROUBLESHOOTING

### "Guardar Configuración no funciona"
- Verifica que tienes permisos de administrador en WordPress
- Verifica que el servidor tiene permisos de escritura en `/wp-content/`

### "Los cambios no se guardan"
- Chequea que el archivo .env existe y es escribible
- Si no existe: El sistema lo creará automáticamente

### "verify.php aún muestra ✗ después de guardar"
- Espera 10 segundos y recarga la página
- Si persiste: Revisa `wp-content/debug.log` para errores

---

## 📊 FORMATO DE TOKENS

| Token | Formato | Ejemplo |
|-------|---------|---------|
| **WP_API_TOKEN** | 32 caracteres alfanuméricos | `aB3cD9eF7gH2iJ8kL1mN4oP6qR5sTu0v` |
| **GEMINI_API_KEY** | Clave de Google | `AIzaSyD...` |
| **AMAZON_TAG** | dominio-21 | `giftia0-21` |

---

**¿Necesitas ayuda?** Revisa el archivo `INSTRUCCIONES_FINALES.md` o ejecuta `troubleshoot.ps1`
