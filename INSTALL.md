# Guía de Instalación Rápida

## Paso 1: Activar el Plugin

1. Ve a tu panel de WordPress: `http://localhost/woo/wp-admin`
2. Navega a **Plugins** → **Plugins instalados**
3. Busca **"Bot Avatar 3D Chatbot"**
4. Haz clic en **"Activar"**

## Paso 2: Configurar OpenAI API Key

1. Ve a [OpenAI Platform](https://platform.openai.com/api-keys)
2. Inicia sesión o crea una cuenta
3. Genera una nueva API Key (o usa una existente)
4. Copia la API Key

5. En WordPress, ve a **Ajustes** → **Bot Avatar 3D**
6. Pega tu API Key en el campo **"OpenAI API Key"**
7. Haz clic en **"Guardar Configuración"**

## Paso 3: Configuración Básica (Opcional)

### Modelo de IA
- **GPT-4o Mini** (Recomendado) - Más rápido y económico
- **GPT-4o** - Más potente pero más costoso
- **GPT-3.5 Turbo** - Muy rápido pero menos capaz

### Personalizar el Bot

En **Prompt del Sistema**, define la personalidad del bot. Ejemplos:

**Para una tienda online:**
```
Eres un asistente de ventas amigable de nuestra tienda online. Ayudas a los clientes a encontrar productos y respondes preguntas sobre envíos y pagos.
```

**Para soporte técnico:**
```
Eres un especialista en soporte técnico. Ayudas a resolver problemas de manera clara y paso a paso.
```

Ver más ejemplos en `PROMPTS_EXAMPLES.md`

## Paso 4: Probar el Chatbot

1. Visita cualquier página de tu sitio web
2. Verás un botón flotante en la esquina inferior derecha con el avatar 3D
3. Haz clic para abrir el chat
4. Escribe un mensaje y presiona Enter
5. El bot responderá usando ChatGPT

## Funcionalidades

### Modo Texto vs Audio

- **Modo Texto** (por defecto): El bot responde con texto
- **Modo Audio**: El bot responde con voz sintetizada

Cambia entre modos usando el toggle "Texto/Audio" en la ventana del chat.

### Usar el Micrófono

1. Mantén presionado el botón del micrófono (🎤)
2. Habla tu mensaje
3. Suelta el botón
4. El mensaje se enviará automáticamente

**Nota:** El micrófono requiere HTTPS para funcionar (excepto en localhost)

## Personalizar el Avatar 3D

Si quieres usar tu propio modelo 3D:

1. Consigue un modelo en formato GLB (puedes usar [Ready Player Me](https://readyplayer.me/) o [Mixamo](https://www.mixamo.com/))
2. Reemplaza el archivo `character.glb` en la carpeta del plugin
3. Refresca la página

Para mejores resultados, incluye animaciones en el modelo:
- `idle` - Animación en reposo
- `talking` - Animación al hablar

## Verificar que Funciona

### Checklist:

- [ ] El botón flotante aparece en la esquina inferior derecha
- [ ] El avatar 3D se muestra en el botón
- [ ] Al hacer clic, se abre la ventana del chat
- [ ] Puedes escribir y enviar mensajes
- [ ] El bot responde a tus mensajes
- [ ] (Opcional) El micrófono funciona
- [ ] (Opcional) El modo audio reproduce respuestas habladas

### Si algo no funciona:

1. **El chatbot no aparece:**
   - Verifica que el plugin esté activado
   - Verifica que "Habilitar Chatbot" esté marcado en Ajustes

2. **El bot no responde:**
   - Verifica que tu API Key de OpenAI sea válida
   - Asegúrate de tener créditos en tu cuenta de OpenAI
   - Abre la Consola del navegador (F12) y busca errores

3. **El avatar 3D no se muestra:**
   - Verifica que el archivo `character.glb` exista
   - Abre la Consola del navegador y busca errores de carga
   - Verifica que tu navegador soporte WebGL

4. **El micrófono no funciona:**
   - Solo funciona en HTTPS (o localhost)
   - Verifica los permisos del navegador
   - Solo compatible con Chrome, Edge, Safari (no todos los navegadores)

## Costos Aproximados

El plugin usa la API de OpenAI que es de pago:

- **GPT-4o Mini**: ~$0.15 por 1M de tokens ≈ $0.0001 por conversación típica
- **TTS (audio)**: ~$15 por 1M de caracteres ≈ $0.001 por respuesta

Para un sitio con 1000 visitantes/mes que usan el chat:
- Costo estimado: $5-20/mes (depende del uso)

Revisa precios actuales: [OpenAI Pricing](https://openai.com/api/pricing/)

## Seguridad

- La API Key se almacena en la base de datos de WordPress
- Las conversaciones NO se guardan en el servidor (solo en el navegador del usuario)
- Usa HTTPS en producción para proteger las comunicaciones

## Próximos Pasos

1. Lee el `README.md` completo para más detalles
2. Revisa `PROMPTS_EXAMPLES.md` para ideas de personalización
3. Personaliza los estilos en `assets/css/bot-avatar.css`
4. Ajusta el comportamiento en `assets/js/bot-avatar.js`

## Soporte

Para problemas o preguntas:
- Revisa la Consola del navegador (F12) por errores
- Verifica los logs de WordPress
- Contacta al desarrollador

---

**¡Listo!** Tu chatbot con avatar 3D ya debería estar funcionando. 🎉
