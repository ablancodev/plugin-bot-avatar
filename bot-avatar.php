<?php
/**
 * Plugin Name: Bot Avatar 3D Chatbot
 * Plugin URI: https://ablancodev.com
 * Description: Chatbot con avatar 3D usando Three.js y ChatGPT con respuestas en texto o audio
 * Version: 1.0.0
 * Author: ablancodev
 * Author URI: https://ablancodev.com
 * License: GPL v2 or later
 * Text Domain: bot-avatar
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('BOT_AVATAR_VERSION', '1.0.0');
define('BOT_AVATAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BOT_AVATAR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once BOT_AVATAR_PLUGIN_DIR . 'includes/class-bot-avatar-settings.php';
require_once BOT_AVATAR_PLUGIN_DIR . 'includes/class-bot-avatar-api.php';

/**
 * Clase principal del plugin
 */
class Bot_Avatar {

    private static $instance = null;
    private static $chatbot_rendered = false;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Inicializar hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 999);

        // Renderizar chatbot con múltiples hooks para máxima compatibilidad
        // Prioridad 999 para asegurar que se carga al final
        add_action('wp_footer', array($this, 'render_chatbot'), 999);

        // Fallback para temas que no llaman wp_footer correctamente
        add_action('shutdown', array($this, 'render_chatbot_fallback'), 0);

        add_action('init', array($this, 'init'));

        // Inicializar componentes
        Bot_Avatar_Settings::get_instance();
        Bot_Avatar_API::get_instance();
    }

    /**
     * Inicialización
     */
    public function init() {
        load_plugin_textdomain('bot-avatar', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Encolar scripts y estilos
     */
    public function enqueue_scripts() {
        // Verificar si está habilitado
        if (!$this->is_chatbot_enabled()) {
            return;
        }

        // Three.js desde CDN
        wp_enqueue_script(
            'threejs',
            'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
            array(),
            '128',
            true
        );

        // GLTFLoader global
        wp_enqueue_script(
            'gltf-loader',
            'https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/loaders/GLTFLoader.js',
            array('threejs'),
            '128',
            true
        );

        // Script principal del chatbot
        wp_enqueue_script(
            'bot-avatar-main',
            BOT_AVATAR_PLUGIN_URL . 'assets/js/bot-avatar.js',
            array('jquery', 'threejs', 'gltf-loader'),
            BOT_AVATAR_VERSION,
            true
        );

        // Estilos
        wp_enqueue_style(
            'bot-avatar-style',
            BOT_AVATAR_PLUGIN_URL . 'assets/css/bot-avatar.css',
            array(),
            BOT_AVATAR_VERSION
        );

        // Pasar datos a JavaScript
        wp_localize_script('bot-avatar-main', 'botAvatarData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bot_avatar_nonce'),
            'modelUrl' => BOT_AVATAR_PLUGIN_URL . 'character.glb',
            'settings' => array(
                'voiceEnabled' => get_option('bot_avatar_voice_enabled', true),
                'defaultMode' => get_option('bot_avatar_default_mode', 'text'),
                'language' => get_option('bot_avatar_language', 'es-ES'),
                'modelScale' => floatval(get_option('bot_avatar_model_scale', 1.5)),
                'modelPositionY' => floatval(get_option('bot_avatar_model_position_y', 0)),
            ),
            'strings' => array(
                'placeholder' => __('Escribe tu mensaje...', 'bot-avatar'),
                'send' => __('Enviar', 'bot-avatar'),
                'listening' => __('Escuchando...', 'bot-avatar'),
                'thinking' => __('Pensando...', 'bot-avatar'),
                'errorMessage' => __('Lo siento, ha ocurrido un error.', 'bot-avatar'),
            )
        ));
    }

    /**
     * Renderizar el chatbot en el footer
     */
    public function render_chatbot() {
        if (!$this->is_chatbot_enabled()) {
            return;
        }

        if (self::$chatbot_rendered) {
            return; // Ya se renderizó, no duplicar
        }

        // NO renderizar en peticiones AJAX o REST
        if ($this->is_non_html_request()) {
            return;
        }

        // Debug comment
        echo '<!-- Bot Avatar: render_chatbot() called via wp_footer -->' . PHP_EOL;

        self::$chatbot_rendered = true;
        include BOT_AVATAR_PLUGIN_DIR . 'templates/chatbot.php';
    }

    /**
     * Fallback para renderizar si wp_footer no se llamó
     * Solo para temas FSE o temas mal codificados
     */
    public function render_chatbot_fallback() {
        // NO renderizar en peticiones AJAX o REST
        if ($this->is_non_html_request()) {
            return;
        }

        // Solo renderizar si NO se ha renderizado aún
        if (!self::$chatbot_rendered && $this->is_chatbot_enabled()) {
            // Inyectar directamente al final del buffer de salida
            echo '<!-- Bot Avatar: Renderizado via fallback (shutdown hook) -->';
            $this->render_chatbot();
        }
    }

    /**
     * Detectar si la petición NO es HTML normal del frontend
     */
    private function is_non_html_request() {
        // Peticiones AJAX de WordPress
        if (wp_doing_ajax()) {
            return true;
        }

        // Peticiones REST API
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        // Admin area
        if (is_admin()) {
            return true;
        }

        // Verificar headers HTTP que indican AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        // Verificar si espera respuesta JSON
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }

        // Verificar Content-Type de la petición
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($content_type, 'application/json') !== false) {
            return true;
        }

        // WooCommerce AJAX
        if (isset($_GET['wc-ajax'])) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el chatbot está habilitado
     */
    private function is_chatbot_enabled() {
        $enabled = get_option('bot_avatar_enabled', true);
        return apply_filters('bot_avatar_is_enabled', $enabled);
    }
}

// Inicializar el plugin
function bot_avatar_init() {
    return Bot_Avatar::get_instance();
}
add_action('plugins_loaded', 'bot_avatar_init');

// Hook de activación
register_activation_hook(__FILE__, 'bot_avatar_activate');
function bot_avatar_activate() {
    // Configuración por defecto
    add_option('bot_avatar_enabled', true);
    add_option('bot_avatar_voice_enabled', true);
    add_option('bot_avatar_default_mode', 'text');
    add_option('bot_avatar_language', 'es-ES');
}

// Hook de desactivación
register_deactivation_hook(__FILE__, 'bot_avatar_deactivate');
function bot_avatar_deactivate() {
    // Limpiar si es necesario
}
