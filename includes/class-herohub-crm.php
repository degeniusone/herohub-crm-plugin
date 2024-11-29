<?php
namespace HeroHub\CRM;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

use HeroHub\CRM\Core\Post_Types;
use HeroHub\CRM\Core\Taxonomies;
use HeroHub\CRM\Core\Roles_Manager;
use HeroHub\CRM\Core\Permissions_Manager;
use HeroHub\CRM\Core\Property_Manager;
use HeroHub\CRM\Core\SMS_Manager;
use HeroHub\CRM\Core\Settings;
use HeroHub\CRM\Admin\Meta_Boxes;
use HeroHub\CRM\Loader;

class HeroHub_CRM {
    protected $loader;
    protected $version;
    protected $post_types;
    protected $taxonomies;
    protected $property_manager;
    protected $roles_manager;
    protected $permissions_manager;
    protected $sms_manager;
    protected $settings;

    public function __construct() {
        $this->version = HEROHUB_CRM_VERSION;
        $this->load_dependencies();
        $this->init_components();
        $this->define_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-herohub-crm-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-installer.php';

        // Load CPTs
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-property.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-deal.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-event.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-activity.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpt/class-contact.php';

        // Load Core Classes
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/core/class-roles-manager.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/core/class-permissions-manager.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/core/class-sms-manager.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/core/class-settings.php';

        // Load Admin Classes
        if (is_admin()) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin/class-admin.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin/class-settings.php';
        }

        $this->loader = new HeroHub\CRM\Loader();
    }

    private function init_components() {
        // Initialize core components
        $this->roles_manager = new Core\Roles_Manager();
        $this->permissions_manager = new Core\Permissions_Manager();
        $this->sms_manager = new Core\SMS_Manager();
        $this->settings = new Core\Settings();
        
        // Initialize post types and taxonomies
        $this->post_types = new Post_Types();
        $this->taxonomies = new Taxonomies();
        $this->property_manager = new Property_Manager();
        
        // Register activation hook
        register_activation_hook(HEROHUB_CRM_FILE, array('HeroHub\CRM\Installer', 'install'));
    }

    private function define_cpt_hooks() {
        // Initialize CPTs
        new HeroHub\CRM\CPT\Property();
        new HeroHub\CRM\CPT\Deal();
        new HeroHub\CRM\CPT\Event();
        new HeroHub\CRM\CPT\Activity();
        new HeroHub\CRM\CPT\Contact();
    }

    private function define_admin_hooks() {
        if (is_admin()) {
            $admin = new Admin\Admin($this->version);
            $meta_boxes = new Admin\Meta_Boxes();

            $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
            $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        }
    }

    private function define_hooks() {
        $this->define_cpt_hooks();
        $this->define_admin_hooks();
        
        // Add core hooks
        $this->loader->add_action('init', $this->roles_manager, 'register_roles');
        $this->loader->add_action('init', $this->permissions_manager, 'register_post_type_capabilities');
        $this->loader->add_filter('map_meta_cap', $this->permissions_manager, 'map_meta_caps', 10, 4);
        
        // Register hooks
        add_action('init', array($this->post_types, 'register'));
        add_action('init', array($this->taxonomies, 'register'));
    }

    public function run() {
        $this->loader->run();
    }

    public function get_version() {
        return $this->version;
    }
}
