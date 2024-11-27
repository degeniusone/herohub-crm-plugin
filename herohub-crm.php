<?php
/**
 * Plugin Name: HeroHub CRM
 * Plugin URI: https://herohub.com
 * Description: A robust CRM system for real estate, with a main focus on cold calling leads management for agents.
 * Version: 1.0.0
 * Author: HeroHub
 * Author URI: https://herohub.com
 * Text Domain: herohub-crm
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('HEROHUB_CRM_VERSION', '1.0.0');
define('HEROHUB_CRM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HEROHUB_CRM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'HeroHub\\CRM\\';
    $base_dir = HEROHUB_CRM_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function herohub_crm_init() {
    // Load text domain for translations
    load_plugin_textdomain('herohub-crm', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize core functionality
    require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-herohub-crm.php';
    $plugin = new HeroHub\CRM\HeroHub_CRM();
    $plugin->run();
}
add_action('plugins_loaded', 'herohub_crm_init');
