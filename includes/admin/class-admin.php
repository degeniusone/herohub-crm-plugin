<?php
namespace HeroHub\CRM\Admin;

class Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            HEROHUB_CRM_PLUGIN_URL . 'admin/css/herohub-crm-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            HEROHUB_CRM_PLUGIN_URL . 'admin/js/herohub-crm-admin.js',
            array('jquery'),
            $this->version,
            false
        );
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            __('HeroHub CRM', 'herohub-crm'),
            __('HeroHub CRM', 'herohub-crm'),
            'manage_options',
            'herohub-crm',
            array($this, 'display_plugin_dashboard_page'),
            'dashicons-businessman',
            3
        );

        add_submenu_page(
            'herohub-crm',
            __('Dashboard', 'herohub-crm'),
            __('Dashboard', 'herohub-crm'),
            'manage_options',
            'herohub-crm',
            array($this, 'display_plugin_dashboard_page')
        );

        add_submenu_page(
            'herohub-crm',
            __('Settings', 'herohub-crm'),
            __('Settings', 'herohub-crm'),
            'manage_options',
            'herohub-crm-settings',
            array($this, 'display_plugin_settings_page')
        );
    }

    public function display_plugin_dashboard_page() {
        include_once HEROHUB_CRM_PLUGIN_DIR . 'admin/partials/herohub-crm-admin-dashboard.php';
    }

    public function display_plugin_settings_page() {
        include_once HEROHUB_CRM_PLUGIN_DIR . 'admin/partials/herohub-crm-admin-settings.php';
    }
}
