<?php
/**
 * Simple check to see if the plugin files are accessible
 */

header('Content-Type: application/json');

$checks = [];

// Check plugin directory
$plugin_dir = dirname(__FILE__);
$checks['plugin_dir'] = $plugin_dir;
$checks['plugin_dir_exists'] = is_dir($plugin_dir);
$checks['plugin_dir_writable'] = is_writable($plugin_dir);

// Check main file
$main_file = $plugin_dir . '/bot-avatar.php';
$checks['main_file_exists'] = file_exists($main_file);
$checks['main_file_readable'] = is_readable($main_file);
$checks['main_file_size'] = file_exists($main_file) ? filesize($main_file) : 0;

// Check if we can parse the PHP file
if (file_exists($main_file)) {
    $content = file_get_contents($main_file);
    $checks['has_plugin_header'] = strpos($content, 'Plugin Name:') !== false;
    $checks['has_version'] = strpos($content, 'Version:') !== false;
}

// Check assets
$js_file = $plugin_dir . '/assets/js/bot-avatar.js';
$css_file = $plugin_dir . '/assets/css/bot-avatar.css';
$model_file = $plugin_dir . '/character.glb';

$checks['js_file_exists'] = file_exists($js_file);
$checks['js_file_size'] = file_exists($js_file) ? filesize($js_file) : 0;

$checks['css_file_exists'] = file_exists($css_file);
$checks['css_file_size'] = file_exists($css_file) ? filesize($css_file) : 0;

$checks['model_file_exists'] = file_exists($model_file);
$checks['model_file_size'] = file_exists($model_file) ? filesize($model_file) : 0;

// Check templates
$template_file = $plugin_dir . '/templates/chatbot.php';
$checks['template_file_exists'] = file_exists($template_file);

// Check includes
$api_file = $plugin_dir . '/includes/class-bot-avatar-api.php';
$settings_file = $plugin_dir . '/includes/class-bot-avatar-settings.php';

$checks['api_file_exists'] = file_exists($api_file);
$checks['settings_file_exists'] = file_exists($settings_file);

// Get file permissions
$checks['permissions'] = [
    'main' => file_exists($main_file) ? substr(sprintf('%o', fileperms($main_file)), -4) : 'N/A',
    'js' => file_exists($js_file) ? substr(sprintf('%o', fileperms($js_file)), -4) : 'N/A',
    'css' => file_exists($css_file) ? substr(sprintf('%o', fileperms($css_file)), -4) : 'N/A',
    'model' => file_exists($model_file) ? substr(sprintf('%o', fileperms($model_file)), -4) : 'N/A',
];

// Calculate overall status
$all_files_exist = $checks['main_file_exists']
    && $checks['js_file_exists']
    && $checks['css_file_exists']
    && $checks['model_file_exists']
    && $checks['template_file_exists']
    && $checks['api_file_exists']
    && $checks['settings_file_exists'];

$checks['status'] = $all_files_exist ? 'OK' : 'ERROR';
$checks['message'] = $all_files_exist
    ? 'All plugin files are present and accessible'
    : 'Some plugin files are missing or not accessible';

echo json_encode($checks, JSON_PRETTY_PRINT);
