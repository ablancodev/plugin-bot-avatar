<?php
/**
 * Clase para manejar la configuración del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Bot_Avatar_Settings {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_options_page(
            __('Bot Avatar 3D Settings', 'bot-avatar'),
            __('Bot Avatar 3D', 'bot-avatar'),
            'manage_options',
            'bot-avatar-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        // Sección general
        add_settings_section(
            'bot_avatar_general_section',
            __('Configuración General', 'bot-avatar'),
            null,
            'bot-avatar-settings'
        );

        // Sección de OpenAI
        add_settings_section(
            'bot_avatar_openai_section',
            __('Configuración de OpenAI', 'bot-avatar'),
            array($this, 'render_openai_section_description'),
            'bot-avatar-settings'
        );

        // Habilitar chatbot
        register_setting('bot_avatar_settings', 'bot_avatar_enabled');
        add_settings_field(
            'bot_avatar_enabled',
            __('Habilitar Chatbot', 'bot-avatar'),
            array($this, 'render_checkbox_field'),
            'bot-avatar-settings',
            'bot_avatar_general_section',
            array('label_for' => 'bot_avatar_enabled')
        );

        // API Key de OpenAI
        register_setting('bot_avatar_settings', 'bot_avatar_openai_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        add_settings_field(
            'bot_avatar_openai_api_key',
            __('OpenAI API Key', 'bot-avatar'),
            array($this, 'render_text_field'),
            'bot-avatar-settings',
            'bot_avatar_openai_section',
            array(
                'label_for' => 'bot_avatar_openai_api_key',
                'type' => 'password',
                'description' => __('Obtén tu API key en <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>', 'bot-avatar')
            )
        );

        // Modelo de OpenAI
        register_setting('bot_avatar_settings', 'bot_avatar_openai_model');
        add_settings_field(
            'bot_avatar_openai_model',
            __('Modelo de OpenAI', 'bot-avatar'),
            array($this, 'render_select_field'),
            'bot-avatar-settings',
            'bot_avatar_openai_section',
            array(
                'label_for' => 'bot_avatar_openai_model',
                'options' => array(
                    'gpt-4o' => 'GPT-4o (Recomendado)',
                    'gpt-4o-mini' => 'GPT-4o Mini (Más rápido)',
                    'gpt-4-turbo' => 'GPT-4 Turbo',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                ),
                'default' => 'gpt-4o-mini'
            )
        );

        // Habilitar voz
        register_setting('bot_avatar_settings', 'bot_avatar_voice_enabled');
        add_settings_field(
            'bot_avatar_voice_enabled',
            __('Habilitar Función de Voz', 'bot-avatar'),
            array($this, 'render_checkbox_field'),
            'bot-avatar-settings',
            'bot_avatar_general_section',
            array('label_for' => 'bot_avatar_voice_enabled', 'default' => true)
        );

        // Modo por defecto
        register_setting('bot_avatar_settings', 'bot_avatar_default_mode');
        add_settings_field(
            'bot_avatar_default_mode',
            __('Modo de Respuesta por Defecto', 'bot-avatar'),
            array($this, 'render_select_field'),
            'bot-avatar-settings',
            'bot_avatar_general_section',
            array(
                'label_for' => 'bot_avatar_default_mode',
                'options' => array(
                    'text' => __('Texto', 'bot-avatar'),
                    'audio' => __('Audio', 'bot-avatar'),
                ),
                'default' => 'text'
            )
        );

        // Idioma
        register_setting('bot_avatar_settings', 'bot_avatar_language');
        add_settings_field(
            'bot_avatar_language',
            __('Idioma', 'bot-avatar'),
            array($this, 'render_select_field'),
            'bot-avatar-settings',
            'bot_avatar_general_section',
            array(
                'label_for' => 'bot_avatar_language',
                'options' => array(
                    'es-ES' => __('Español', 'bot-avatar'),
                    'en-US' => __('English', 'bot-avatar'),
                    'fr-FR' => __('Français', 'bot-avatar'),
                    'de-DE' => __('Deutsch', 'bot-avatar'),
                ),
                'default' => 'es-ES'
            )
        );

        // Tamaño del Avatar 3D
        register_setting('bot_avatar_settings', 'bot_avatar_model_scale', array(
            'sanitize_callback' => 'floatval',
            'default' => 1.5
        ));
        add_settings_field(
            'bot_avatar_model_scale',
            __('Tamaño del Avatar', 'bot-avatar'),
            array($this, 'render_number_field'),
            'bot-avatar-settings',
            'bot_avatar_general_section',
            array(
                'label_for' => 'bot_avatar_model_scale',
                'type' => 'number',
                'step' => '0.01',
                'min' => '0.01',
                'max' => '10',
                'default' => 1.5,
                'description' => __('Ajusta el tamaño del modelo 3D (0.01 = muy pequeño, 10 = muy grande). Puedes usar valores como 0.01, 0.05, 0.10, etc.', 'bot-avatar')
            )
        );

        // Posición Vertical del Avatar
        register_setting('bot_avatar_settings', 'bot_avatar_model_position_y', array(
            'sanitize_callback' => 'floatval',
            'default' => 0
        ));
        add_settings_field(
            'bot_avatar_model_position_y',
            __('Posición Vertical del Avatar', 'bot-avatar'),
            array($this, 'render_number_field'),
            'bot-avatar-settings',
            'bot_avatar_general_section',
            array(
                'label_for' => 'bot_avatar_model_position_y',
                'type' => 'number',
                'step' => '0.1',
                'min' => '-10',
                'max' => '10',
                'default' => 0,
                'description' => __('Ajusta la posición vertical del modelo. Valores negativos lo bajan, positivos lo suben. Ejemplo: -1.5 para bajar, 1.5 para subir', 'bot-avatar')
            )
        );

        // Voz de OpenAI TTS
        register_setting('bot_avatar_settings', 'bot_avatar_tts_voice');
        add_settings_field(
            'bot_avatar_tts_voice',
            __('Voz del Asistente', 'bot-avatar'),
            array($this, 'render_select_field'),
            'bot-avatar-settings',
            'bot_avatar_openai_section',
            array(
                'label_for' => 'bot_avatar_tts_voice',
                'options' => array(
                    'nova' => __('Nova (Femenina)', 'bot-avatar'),
                    'alloy' => __('Alloy (Neutral)', 'bot-avatar'),
                    'echo' => __('Echo (Masculina)', 'bot-avatar'),
                    'fable' => __('Fable (Británica)', 'bot-avatar'),
                    'onyx' => __('Onyx (Masculina Profunda)', 'bot-avatar'),
                    'shimmer' => __('Shimmer (Femenina Suave)', 'bot-avatar'),
                ),
                'default' => 'nova',
                'description' => __('Selecciona la voz que usará el asistente para las respuestas en audio', 'bot-avatar')
            )
        );

        // Prompt del sistema
        register_setting('bot_avatar_settings', 'bot_avatar_system_prompt', array(
            'sanitize_callback' => 'sanitize_textarea_field'
        ));
        add_settings_field(
            'bot_avatar_system_prompt',
            __('Prompt del Sistema', 'bot-avatar'),
            array($this, 'render_textarea_field'),
            'bot-avatar-settings',
            'bot_avatar_openai_section',
            array(
                'label_for' => 'bot_avatar_system_prompt',
                'description' => __('Define la personalidad y comportamiento del chatbot', 'bot-avatar'),
                'default' => 'Eres un asistente virtual amigable y servicial. Responde de manera clara y concisa.'
            )
        );
    }

    /**
     * Renderizar página de configuración
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('bot_avatar_settings');
                do_settings_sections('bot-avatar-settings');
                submit_button(__('Guardar Configuración', 'bot-avatar'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar descripción de sección OpenAI
     */
    public function render_openai_section_description() {
        echo '<p>' . __('Configura la integración con OpenAI ChatGPT', 'bot-avatar') . '</p>';
    }

    /**
     * Renderizar campo checkbox
     */
    public function render_checkbox_field($args) {
        $option = get_option($args['label_for'], isset($args['default']) ? $args['default'] : false);
        ?>
        <input type="checkbox"
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="<?php echo esc_attr($args['label_for']); ?>"
               value="1"
               <?php checked(1, $option); ?>>
        <?php
    }

    /**
     * Renderizar campo de texto
     */
    public function render_text_field($args) {
        $option = get_option($args['label_for'], '');
        $type = isset($args['type']) ? $args['type'] : 'text';
        ?>
        <input type="<?php echo esc_attr($type); ?>"
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="<?php echo esc_attr($args['label_for']); ?>"
               value="<?php echo esc_attr($option); ?>"
               class="regular-text">
        <?php
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    /**
     * Renderizar campo select
     */
    public function render_select_field($args) {
        $option = get_option($args['label_for'], isset($args['default']) ? $args['default'] : '');
        ?>
        <select id="<?php echo esc_attr($args['label_for']); ?>"
                name="<?php echo esc_attr($args['label_for']); ?>">
            <?php foreach ($args['options'] as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"
                        <?php selected($option, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Renderizar campo textarea
     */
    public function render_textarea_field($args) {
        $option = get_option($args['label_for'], isset($args['default']) ? $args['default'] : '');
        ?>
        <textarea id="<?php echo esc_attr($args['label_for']); ?>"
                  name="<?php echo esc_attr($args['label_for']); ?>"
                  rows="5"
                  class="large-text"><?php echo esc_textarea($option); ?></textarea>
        <?php
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Renderizar campo numérico
     */
    public function render_number_field($args) {
        $option = get_option($args['label_for'], isset($args['default']) ? $args['default'] : '');
        $step = isset($args['step']) ? $args['step'] : '1';
        $min = isset($args['min']) ? $args['min'] : '';
        $max = isset($args['max']) ? $args['max'] : '';
        ?>
        <input type="number"
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="<?php echo esc_attr($args['label_for']); ?>"
               value="<?php echo esc_attr($option); ?>"
               step="<?php echo esc_attr($step); ?>"
               <?php if ($min !== '') echo 'min="' . esc_attr($min) . '"'; ?>
               <?php if ($max !== '') echo 'max="' . esc_attr($max) . '"'; ?>
               class="small-text">
        <?php
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }
}
