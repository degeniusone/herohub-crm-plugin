<?php
namespace HeroHub\CRM\Admin;

/**
 * Class Dashboard
 * Handles the main dashboard functionality for the CRM
 */
class Dashboard {
    /**
     * Initialize the dashboard
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_dashboard_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Add dashboard menu
     */
    public function add_dashboard_menu() {
        add_menu_page(
            __('HeroHub CRM', 'herohub-crm'),
            __('HeroHub CRM', 'herohub-crm'),
            'manage_options',
            'herohub-crm',
            array($this, 'render_dashboard'),
            'dashicons-businessman',
            3
        );

        // Add submenus based on user role
        if (current_user_can('manage_options')) {
            $this->add_admin_submenus();
        } elseif (current_user_can('manage_real_estate')) {
            $this->add_manager_submenus();
        } else {
            $this->add_agent_submenus();
        }
    }

    /**
     * Add admin-specific submenus
     */
    private function add_admin_submenus() {
        add_submenu_page(
            'herohub-crm',
            __('Overview', 'herohub-crm'),
            __('Overview', 'herohub-crm'),
            'manage_options',
            'herohub-crm',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'herohub-crm',
            __('Managers', 'herohub-crm'),
            __('Managers', 'herohub-crm'),
            'manage_options',
            'herohub-managers',
            array($this, 'render_managers_page')
        );

        add_submenu_page(
            'herohub-crm',
            __('Settings', 'herohub-crm'),
            __('Settings', 'herohub-crm'),
            'manage_options',
            'herohub-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Add manager-specific submenus
     */
    private function add_manager_submenus() {
        add_submenu_page(
            'herohub-crm',
            __('My Team', 'herohub-crm'),
            __('My Team', 'herohub-crm'),
            'manage_real_estate',
            'herohub-team',
            array($this, 'render_team_page')
        );

        add_submenu_page(
            'herohub-crm',
            __('Performance', 'herohub-crm'),
            __('Performance', 'herohub-crm'),
            'manage_real_estate',
            'herohub-performance',
            array($this, 'render_performance_page')
        );
    }

    /**
     * Add agent-specific submenus
     */
    private function add_agent_submenus() {
        add_submenu_page(
            'herohub-crm',
            __('My Dashboard', 'herohub-crm'),
            __('My Dashboard', 'herohub-crm'),
            'access_real_estate',
            'herohub-crm',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'herohub-crm',
            __('My Tasks', 'herohub-crm'),
            __('My Tasks', 'herohub-crm'),
            'access_real_estate',
            'herohub-tasks',
            array($this, 'render_tasks_page')
        );
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'herohub') === false) {
            return;
        }

        wp_enqueue_style(
            'herohub-admin',
            HEROHUB_CRM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            HEROHUB_CRM_VERSION
        );

        wp_enqueue_script(
            'herohub-admin',
            HEROHUB_CRM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            HEROHUB_CRM_VERSION,
            true
        );

        wp_localize_script(
            'herohub-admin',
            'herohubAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('herohub_admin_nonce')
            )
        );
    }

    /**
     * Render the main dashboard
     */
    public function render_dashboard() {
        if (current_user_can('manage_options')) {
            $this->render_admin_dashboard();
        } elseif (current_user_can('manage_real_estate')) {
            $this->render_manager_dashboard();
        } else {
            $this->render_agent_dashboard();
        }
    }

    /**
     * Render admin dashboard
     */
    private function render_admin_dashboard() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/dashboard-admin.php';
    }

    /**
     * Render manager dashboard
     */
    private function render_manager_dashboard() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/dashboard-manager.php';
    }

    /**
     * Render agent dashboard
     */
    private function render_agent_dashboard() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/dashboard-agent.php';
    }

    /**
     * Render managers page
     */
    public function render_managers_page() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/managers.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Render team page
     */
    public function render_team_page() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/team.php';
    }

    /**
     * Render performance page
     */
    public function render_performance_page() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/performance.php';
    }

    /**
     * Render tasks page
     */
    public function render_tasks_page() {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'templates/admin/tasks.php';
    }
}
