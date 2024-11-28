<?php
namespace HeroHub\CRM;

use HeroHub\CRM\Admin\Dashboard;
use HeroHub\CRM\Admin\Settings;
use HeroHub\CRM\Core\Lead_Scoring;
use HeroHub\CRM\Core\Post_Types;
use HeroHub\CRM\Core\Taxonomies;
use HeroHub\CRM\Core\Roles_Manager;
use HeroHub\CRM\Admin\Meta_Boxes;
use HeroHub\CRM\Loader;
use HeroHub\CRM\Logger;
use HeroHub\CRM\Ajax;
use HeroHub\CRM\Exporter;
use HeroHub\CRM\Admin\Admin;

class HeroHub_CRM {
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $post_types;
    protected $taxonomies;

    public function __construct() {
        $this->version = HEROHUB_CRM_VERSION;
        $this->plugin_name = 'herohub-crm';
        
        $this->load_dependencies();
        $this->includes();
        $this->init();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-herohub-crm-loader.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/core/class-post-types.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/core/class-taxonomies.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/admin/class-admin.php';
        
        $this->loader = new Loader();
    }

    private function includes() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-installer.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-ajax.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-exporter.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-logger.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/trait-error-handler.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/admin/class-dashboard.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/admin/class-dashboard-functions.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/admin/class-settings.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/admin/class-contact-metabox.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/core/class-sms-manager.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/core/class-lead-scoring.php';
    }

    public function init() {
        // Initialize Logger
        new Logger();
        
        // Initialize AJAX handler
        new Ajax();
        
        // Initialize Settings
        new Settings();
        
        // Initialize Exporter
        new Exporter();
        
        // Initialize lead scoring
        new Lead_Scoring();
        
        // Initialize post types and taxonomies
        $this->post_types = new Post_Types();
        $this->taxonomies = new Taxonomies();
        
        // Register activation hook
        register_activation_hook(\HEROHUB_CRM_FILE, array('HeroHub\CRM\Installer', 'install'));
        
        // Register uninstall hook
        register_uninstall_hook(\HEROHUB_CRM_FILE, array('HeroHub\CRM\Installer', 'uninstall'));
        
        // Register post types and taxonomies on init
        add_action('init', array($this->post_types, 'register'));
        add_action('init', array($this->taxonomies, 'register'));
    }

    private function define_admin_hooks() {
        $admin = new Admin($this->get_plugin_name(), $this->get_version());
        $meta_boxes = new Meta_Boxes();
        
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $admin, 'add_plugin_admin_menu');
    }

    private function define_public_hooks() {
        // Add public-facing hooks here if needed
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function run() {
        $this->loader->run();
    }
}
