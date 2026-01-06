# Bot Avatar 3D Chatbot

Plugin de WordPress que implementa un chatbot con avatar 3D usando Three.js y ChatGPT de OpenAI.

## Características

- 🤖 **Chatbot con avatar 3D** - Modelo GLB renderizado con Three.js
- 💬 **Integración con ChatGPT** - Usa la API de OpenAI para respuestas inteligentes
- 🎙️ **Reconocimiento de voz** - Habla con el bot usando tu micrófono (Speech-to-Text)
- 🔊 **Respuestas en audio** - El bot puede responder con voz sintetizada (Text-to-Speech)
- 🎨 **Diseño moderno** - Interfaz limpia y responsive
- ⚙️ **Totalmente configurable** - Panel de administración en WordPress

## Instalación

1. **Activar el plugin**
   - El plugin ya está en la carpeta correcta: `wp-content/plugins/bot-avatar/`
   - Ve a WordPress Admin > Plugins
   - Busca "Bot Avatar 3D Chatbot"
   - Haz clic en "Activar"

2. **Configurar OpenAI API Key**
   - Ve a WordPress Admin > Ajustes > Bot Avatar 3D
   - Obtén tu API key en [OpenAI Platform](https://platform.openai.com/api-keys)
   - Pega la API key en el campo correspondiente
   - Guarda los cambios

## Configuración

### Panel de Administración

Ve a **Ajustes > Bot Avatar 3D** para configurar:

#### Configuración General
- **Habilitar Chatbot** - Activa/desactiva el chatbot en el sitio
- **Habilitar Función de Voz** - Permite usar micrófono y audio
- **Modo de Respuesta por Defecto** - Texto o Audio
- **Idioma** - Español, English, Français, Deutsch

#### Configuración de OpenAI
- **OpenAI API Key** - Tu clave de API (requerido)
- **Modelo de OpenAI** - Elige entre GPT-4o, GPT-4o Mini, GPT-4 Turbo, o GPT-3.5 Turbo
- **Prompt del Sistema** - Define la personalidad del chatbot

## Uso

### Para los visitantes del sitio

1. **Abrir el chatbot**
   - Haz clic en el botón flotante en la esquina inferior derecha
   - Se abrirá la ventana del chatbot con el avatar 3D

2. **Escribir un mensaje**
   - Escribe tu pregunta en el campo de texto
   - Presiona Enter o el botón de enviar

3. **Usar voz**
   - Mantén presionado el botón del micrófono
   - Habla tu mensaje
   - Suelta el botón para enviar

4. **Cambiar modo de respuesta**
   - Usa el toggle "Texto/Audio" para cambiar entre respuestas escritas o habladas
   - En modo Audio, el bot responderá con voz sintetizada

## Estructura de Archivos

```
bot-avatar/
├── bot-avatar.php              # Archivo principal del plugin
├── character.glb               # Modelo 3D del avatar
├── README.md                   # Este archivo
├── assets/
│   ├── css/
│   │   └── bot-avatar.css      # Estilos del chatbot
│   └── js/
│       └── bot-avatar.js       # JavaScript principal con Three.js
├── includes/
│   ├── class-bot-avatar-settings.php  # Configuración del plugin
│   └── class-bot-avatar-api.php       # Integración con OpenAI
└── templates/
    └── chatbot.php             # Template HTML del chatbot
```

## Personalización del Avatar 3D

El plugin viene con un modelo `character.glb` por defecto. Para usar tu propio modelo:

1. Reemplaza el archivo `character.glb` con tu modelo 3D
2. Asegúrate de que el modelo esté en formato GLB (GLTF binario)
3. Para mejores resultados, incluye animaciones en el modelo con nombres como:
   - `idle` - Animación en reposo
   - `talking` - Animación al hablar
   - `thinking` - Animación al procesar

## Tecnologías Utilizadas

- **WordPress** - Framework base
- **Three.js** - Renderizado 3D
- **OpenAI GPT** - Inteligencia conversacional
- **OpenAI TTS** - Text-to-Speech
- **Web Speech API** - Speech-to-Text (navegador)
- **jQuery** - Manipulación DOM

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Navegador moderno con soporte para:
  - WebGL (para Three.js)
  - Web Speech API (para reconocimiento de voz)
  - Audio API
- Cuenta de OpenAI con créditos disponibles

## Costos

Este plugin utiliza la API de OpenAI, que es de pago. Los costos aproximados por uso son:

- **GPT-4o Mini**: ~$0.15 por cada 1M de tokens (Recomendado para producción)
- **GPT-4o**: ~$2.50 por cada 1M de tokens
- **TTS (voz)**: ~$15 por cada 1M de caracteres

Revisa los precios actuales en [OpenAI Pricing](https://openai.com/api/pricing/)

## Solución de Problemas

### El chatbot no aparece
- Verifica que el plugin esté activado
- Verifica que "Habilitar Chatbot" esté marcado en la configuración
- Revisa la consola del navegador por errores

### El bot no responde
- Verifica que la API key de OpenAI sea válida
- Asegúrate de tener créditos en tu cuenta de OpenAI
- Revisa los logs de error de WordPress

### El avatar 3D no se muestra
- Verifica que el archivo `character.glb` exista
- Revisa la consola del navegador por errores de carga
- Asegura que tu navegador soporte WebGL

### El reconocimiento de voz no funciona
- Solo funciona en navegadores compatibles (Chrome, Edge, Safari recientes)
- Asegura que el navegador tenga permisos de micrófono
- Usa HTTPS (requerido para la API de voz)

## Características Avanzadas

### Filtros de WordPress

Puedes personalizar el comportamiento del plugin usando filtros:

```php
// Deshabilitar el chatbot en páginas específicas
add_filter('bot_avatar_is_enabled', function($enabled) {
    if (is_page('contact')) {
        return false;
    }
    return $enabled;
});
```

### Modificar el diseño

Los estilos están en `assets/css/bot-avatar.css` y usan variables CSS:

```css
:root {
    --bot-primary: #6366f1;
    --bot-secondary: #8b5cf6;
    /* ... más variables */
}
```

## Soporte y Contribuciones

Este es un plugin personalizado. Para problemas o mejoras:

1. Revisa la documentación
2. Verifica los logs de error
3. Contacta al desarrollador

## Licencia

GPL v2 or later

## Créditos

- Three.js: https://threejs.org/
- OpenAI: https://openai.com/

---

**Nota**: Este plugin requiere una API key de OpenAI válida para funcionar. El uso de la API de OpenAI tiene costos asociados.
