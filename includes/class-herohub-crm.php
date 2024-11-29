<?php
namespace HeroHub\CRM;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

use HeroHub\CRM\Core\Permissions_Manager as Core_Permissions_Manager;
use HeroHub\CRM\Core\Property_Manager as Core_Property_Manager;
use HeroHub\CRM\CPT as CRM_CPT;
use HeroHub\CRM\Admin\Settings as Admin_Settings;
use HeroHub\CRM\Admin\Admin as Admin_Admin;

/**
 * The main class that runs the plugin.
 *
 * @since 1.0.0
 */
class HeroHub_CRM {
    protected $loader;
    protected $version;
    protected $post_types;
    protected $taxonomies;
    protected $property_manager;
    protected $permissions_manager;
    protected $settings;
    protected $admin;

    public function __construct() {
        $this->version = HEROHUB_CRM_VERSION;
        $this->load_dependencies();
        $this->init_components();
        $this->define_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-herohub-crm-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-installer.php';

        // Load Core Classes
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/core/class-permissions-manager.php';

        // Load CPTs
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-deal.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-event.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-activity.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-contact.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-property.php';

        // Load Traits
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/trait-error-handler.php';

        // Load Admin Classes
        if (is_admin()) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin/class-admin.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin/class-settings.php';
        }

        $this->loader = new Loader();
    }

    private function init_components() {
        // Initialize core components
        $this->permissions_manager = new Core_Permissions_Manager();
        $this->settings = new Admin_Settings();
        
        // Initialize admin component
        $this->admin = new Admin_Admin($this->version);
        
        // Register activation hook
        register_activation_hook(HEROHUB_CRM_FILE, array('HeroHub\\CRM\\Installer', 'install'));
    }

    private function define_cpt_hooks() {
        // Initialize CPTs
        new CRM_CPT\Property();
        new CRM_CPT\Deal();
        new CRM_CPT\Event();
        new CRM_CPT\Activity();
        new CRM_CPT\Contact();
    }

    private function define_admin_hooks() {
        if (is_admin()) {
            $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
            $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');
        }
    }

    private function define_hooks() {
        $this->loader->add_action('plugins_loaded', $this, 'load_textdomain');
        $this->loader->add_action('admin_menu', $this->admin, 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');
        $this->loader->add_action('wp_enqueue_scripts', $this->admin, 'enqueue_frontend_styles');
        $this->loader->add_action('wp_enqueue_scripts', $this->admin, 'enqueue_frontend_scripts');
        
        $this->define_cpt_hooks();
        $this->define_admin_hooks();
        
        // Add core hooks
        $this->loader->add_action('init', $this->permissions_manager, 'register_post_type_capabilities');
        $this->loader->add_filter('map_meta_cap', $this->permissions_manager, 'map_meta_caps', 10, 4);
        
        // Register hooks
        do_action('herohub_crm_register_post_types');
        do_action('herohub_crm_register_taxonomies');
        do_action('herohub_crm_init_property_management');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_version() {
        return $this->version;
    }
}
