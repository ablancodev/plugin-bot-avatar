<?php
/**
 * Template del chatbot - Diseño Minimalista
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="bot-avatar-container">
    <!-- Botón flotante -->
    <button id="bot-avatar-toggle" aria-label="<?php esc_attr_e('Abrir asistente', 'bot-avatar'); ?>">
        <canvas id="bot-avatar-mini-canvas"></canvas>
    </button>

    <!-- Vista del Asistente (esquina inferior derecha) -->
    <div id="bot-avatar-hologram" class="bot-hidden">
        <!-- Avatar 3D -->
        <div class="bot-avatar-main">
            <canvas id="bot-avatar-canvas"></canvas>
        </div>

        <!-- Controles -->
        <div class="bot-floating-controls">
            <button class="bot-control-btn" id="bot-history-toggle" aria-label="<?php esc_attr_e('Ver historial', 'bot-avatar'); ?>">
                <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
            <button class="bot-control-btn" id="bot-close-btn" aria-label="<?php esc_attr_e('Cerrar', 'bot-avatar'); ?>">
                <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Input flotante -->
        <div class="bot-hologram-input">
            <div class="bot-input-wrapper">
                <button class="bot-holo-btn" id="bot-voice-btn" aria-label="<?php esc_attr_e('Grabar voz', 'bot-avatar'); ?>">
                    <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>
                <textarea
                    id="bot-message-input"
                    placeholder="<?php esc_attr_e('Pregúntame lo que quieras...', 'bot-avatar'); ?>"
                    rows="1"
                    aria-label="<?php esc_attr_e('Mensaje', 'bot-avatar'); ?>"
                ></textarea>
                <button class="bot-holo-btn bot-send-btn" id="bot-send-btn" aria-label="<?php esc_attr_e('Enviar', 'bot-avatar'); ?>">
                    <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Toggle Texto/Audio -->
        <div class="bot-mode-selector">
            <div class="bot-mode-switch" id="bot-mode-switch" role="switch" aria-checked="false">
                <span class="bot-mode-option bot-mode-active" data-mode="text">
                    <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <span><?php _e('Texto', 'bot-avatar'); ?></span>
                </span>
                <span class="bot-mode-option" data-mode="audio">
                    <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    </svg>
                    <span><?php _e('Audio', 'bot-avatar'); ?></span>
                </span>
            </div>
        </div>

        <!-- Panel de Historial -->
        <div class="bot-history-panel" id="bot-history-panel">
            <div class="bot-history-header">
                <h3><?php _e('Conversación', 'bot-avatar'); ?></h3>
                <button class="bot-history-close" id="bot-history-close">
                    <svg class="bot-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="bot-history-messages" id="bot-messages">
                <!-- Mensaje de bienvenida -->
                <div class="bot-history-msg bot-msg-assistant">
                    <div class="bot-msg-content">
                        <?php _e('¡Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte?', 'bot-avatar'); ?>
                    </div>
                    <div class="bot-msg-time"><?php echo current_time('H:i'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
