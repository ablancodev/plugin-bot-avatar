<?php
/**
 * Clase para manejar las peticiones API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Bot_Avatar_API {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // AJAX endpoints
        add_action('wp_ajax_bot_avatar_chat', array($this, 'handle_chat_request'));
        add_action('wp_ajax_nopriv_bot_avatar_chat', array($this, 'handle_chat_request'));

        add_action('wp_ajax_bot_avatar_text_to_speech', array($this, 'handle_text_to_speech'));
        add_action('wp_ajax_nopriv_bot_avatar_text_to_speech', array($this, 'handle_text_to_speech'));
    }

    /**
     * Manejar peticiones de chat
     */
    public function handle_chat_request() {
        // Limpiar cualquier output previo que pueda romper el JSON
        if (ob_get_level()) {
            ob_clean();
        }

        // Verificar nonce
        check_ajax_referer('bot_avatar_nonce', 'nonce');

        // Obtener mensaje
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        $conversation_history = isset($_POST['history']) ? json_decode(stripslashes($_POST['history']), true) : array();

        if (empty($message)) {
            wp_send_json_error(array('message' => __('Mensaje vacío', 'bot-avatar')));
            return;
        }

        // Obtener API key
        $api_key = get_option('bot_avatar_openai_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API key no configurada', 'bot-avatar')));
            return;
        }

        // Preparar mensajes
        $messages = array();

        // Agregar prompt del sistema
        $system_prompt = get_option('bot_avatar_system_prompt', 'Eres un asistente virtual amigable y servicial. Responde de manera clara y concisa.');
        $messages[] = array(
            'role' => 'system',
            'content' => $system_prompt
        );

        // Agregar historial de conversación (máximo 10 mensajes)
        if (!empty($conversation_history)) {
            $history_slice = array_slice($conversation_history, -10);
            foreach ($history_slice as $msg) {
                $messages[] = array(
                    'role' => $msg['role'],
                    'content' => $msg['content']
                );
            }
        }

        // Agregar mensaje actual
        $messages[] = array(
            'role' => 'user',
            'content' => $message
        );

        // Hacer petición a OpenAI
        $response = $this->call_openai_api($api_key, $messages);

        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
            return;
        }

        wp_send_json_success(array(
            'message' => $response,
            'timestamp' => current_time('timestamp')
        ));
    }

    /**
     * Manejar peticiones de text-to-speech
     */
    public function handle_text_to_speech() {
        // Limpiar cualquier output previo que pueda romper el JSON
        if (ob_get_level()) {
            ob_clean();
        }

        // Verificar nonce
        check_ajax_referer('bot_avatar_nonce', 'nonce');

        $text = isset($_POST['text']) ? sanitize_text_field($_POST['text']) : '';

        if (empty($text)) {
            wp_send_json_error(array('message' => __('Texto vacío', 'bot-avatar')));
            return;
        }

        // Obtener API key
        $api_key = get_option('bot_avatar_openai_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API key no configurada', 'bot-avatar')));
            return;
        }

        // Llamar a OpenAI TTS API
        $audio_response = $this->call_openai_tts($api_key, $text);

        if (is_wp_error($audio_response)) {
            wp_send_json_error(array(
                'message' => $audio_response->get_error_message()
            ));
            return;
        }

        wp_send_json_success(array(
            'audio' => $audio_response
        ));
    }

    /**
     * Llamar a la API de OpenAI Chat
     */
    private function call_openai_api($api_key, $messages) {
        $model = get_option('bot_avatar_openai_model', 'gpt-4o-mini');

        $body = array(
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500
        );

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode($body)
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('openai_error', $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('openai_error', __('Respuesta inválida de OpenAI', 'bot-avatar'));
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Llamar a la API de OpenAI TTS
     */
    private function call_openai_tts($api_key, $text) {
        $voice = get_option('bot_avatar_tts_voice', 'nova');

        $body = array(
            'model' => 'tts-1',
            'input' => $text,
            'voice' => $voice, // Configurable desde admin: alloy, echo, fable, onyx, nova, shimmer
            'response_format' => 'mp3'
        );

        $response = wp_remote_post('https://api.openai.com/v1/audio/speech', array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode($body)
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $audio_data = wp_remote_retrieve_body($response);

        if (empty($audio_data)) {
            return new WP_Error('tts_error', __('No se pudo generar el audio', 'bot-avatar'));
        }

        // Convertir a base64 para enviarlo al cliente
        return base64_encode($audio_data);
    }
}
