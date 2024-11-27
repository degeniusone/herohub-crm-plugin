<?php
namespace HeroHub\CRM;

class HeroHub_CRM {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = HEROHUB_CRM_VERSION;
        $this->plugin_name = 'herohub-crm';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_post_types();
        $this->register_taxonomies();
    }

    private function load_dependencies() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/class-herohub-crm-loader.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/core/class-post-types.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/core/class-taxonomies.php';
        require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/admin/class-admin.php';
        
        $this->loader = new Loader();
    }

    private function define_admin_hooks() {
        $admin = new Admin\Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $admin, 'add_plugin_admin_menu');
    }

    private function define_public_hooks() {
        // Add public-facing hooks here if needed
    }

    private function register_post_types() {
        $post_types = new Core\Post_Types();
        $post_types->register();
    }

    private function register_taxonomies() {
        $taxonomies = new Core\Taxonomies();
        $taxonomies->register();
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}
