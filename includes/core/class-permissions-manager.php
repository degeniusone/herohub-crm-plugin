<?php
namespace HeroHub\CRM\Core;

if (!defined('WPINC')) {
    die;
}

/**
 * Permissions Manager
 * 
 * Handles all permission-related functionality including:
 * - Custom role creation and management
 * - Post type capabilities
 * - User role permissions
 * - Access control
 * - Agent-Manager relationships
 */
class Permissions_Manager {
    /**
     * Initialize the permissions manager
     */
    public function __construct() {
        // Core permissions
        add_action('init', array($this, 'register_roles'));
        add_action('init', array($this, 'register_post_type_capabilities'));
        add_filter('map_meta_cap', array($this, 'map_meta_caps'), 10, 4);

        // Admin interface
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_menu_page'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_assign_agent_to_manager', array($this, 'handle_agent_assignment'));
        }
    }

    /**
     * Register custom roles and capabilities
     */
    public function register_roles() {
        // Add Real Estate Manager role
        add_role(
            'real_estate_manager',
            __('Real Estate Manager', 'herohub-crm'),
            array(
                // WordPress core capabilities
                'read' => true,
                'edit_posts' => true,
                'upload_files' => true,

                // Custom capabilities for manager
                'manage_agents' => true,
                'view_all_contacts' => true,
                'edit_all_contacts' => true,
                'delete_all_contacts' => true,
                'view_all_deals' => true,
                'edit_all_deals' => true,
                'delete_all_deals' => true,
                'view_all_properties' => true,
                'edit_all_properties' => true,
                'delete_all_properties' => true,
                'view_all_events' => true,
                'edit_all_events' => true,
                'delete_all_events' => true,
                'view_all_activities' => true,
                'edit_all_activities' => true,
                'delete_all_activities' => true,
                'import_contacts' => true,
                'export_contacts' => true,
                'view_reports' => true,
                'toggle_agent_manager' => true,
            )
        );

        // Add Real Estate Agent role
        add_role(
            'real_estate_agent',
            __('Real Estate Agent', 'herohub-crm'),
            array(
                // WordPress core capabilities
                'read' => true,
                'edit_posts' => true,
                'upload_files' => true,

                // Custom capabilities for agent
                'view_own_contacts' => true,
                'edit_own_contacts' => true,
                'delete_own_contacts' => true,
                'view_own_deals' => true,
                'edit_own_deals' => true,
                'delete_own_deals' => true,
                'view_own_properties' => true,
                'edit_own_properties' => true,
                'delete_own_properties' => true,
                'view_own_events' => true,
                'edit_own_events' => true,
                'delete_own_events' => true,
                'view_own_activities' => true,
                'edit_own_activities' => true,
                'delete_own_activities' => true,
            )
        );

        // Add capabilities to administrator role
        $admin = get_role('administrator');
        if ($admin) {
            $admin_capabilities = array(
                'manage_agents',
                'manage_managers',
                'view_all_contacts',
                'edit_all_contacts',
                'delete_all_contacts',
                'view_all_deals',
                'edit_all_deals',
                'delete_all_deals',
                'view_all_properties',
                'edit_all_properties',
                'delete_all_properties',
                'view_all_events',
                'edit_all_events',
                'delete_all_events',
                'view_all_activities',
                'edit_all_activities',
                'delete_all_activities',
                'import_contacts',
                'export_contacts',
                'view_reports',
                'manage_crm_settings',
                'toggle_agent_manager',
            );

            foreach ($admin_capabilities as $cap) {
                $admin->add_cap($cap);
            }
        }
    }

    /**
     * Register capabilities for custom post types
     */
    public function register_post_type_capabilities() {
        $post_types = array('property', 'deal', 'event', 'activity', 'contact');
        
        foreach ($post_types as $post_type) {
            $this->add_post_type_caps($post_type);
        }
    }

    /**
     * Add capabilities for a post type
     */
    private function add_post_type_caps($post_type) {
        // Get roles that should have access to this post type
        $roles = array('administrator', 'real_estate_manager', 'real_estate_agent');
        
        // Define capabilities
        $caps = array(
            'edit_post'              => "edit_{$post_type}",
            'read_post'              => "read_{$post_type}",
            'delete_post'            => "delete_{$post_type}",
            'edit_posts'             => "edit_{$post_type}s",
            'edit_others_posts'      => "edit_others_{$post_type}s",
            'publish_posts'          => "publish_{$post_type}s",
            'read_private_posts'     => "read_private_{$post_type}s",
            'delete_posts'           => "delete_{$post_type}s",
            'delete_private_posts'   => "delete_private_{$post_type}s",
            'delete_published_posts' => "delete_published_{$post_type}s",
            'delete_others_posts'    => "delete_others_{$post_type}s",
            'edit_private_posts'     => "edit_private_{$post_type}s",
            'edit_published_posts'   => "edit_published_{$post_type}s",
        );

        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if (!$role) {
                continue;
            }

            // Add all capabilities to administrators and managers
            if ($role_name === 'administrator' || $role_name === 'real_estate_manager') {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
                continue;
            }

            // Add limited capabilities to agents
            $role->add_cap("edit_{$post_type}s");
            $role->add_cap("edit_{$post_type}");
            $role->add_cap("read_{$post_type}");
            $role->add_cap("delete_{$post_type}");
            $role->add_cap("publish_{$post_type}s");
        }
    }

    /**
     * Map meta capabilities
     */
    public function map_meta_caps($caps, $cap, $user_id, $args) {
        $post_types = array('property', 'deal', 'event', 'activity', 'contact');
        
        foreach ($post_types as $post_type) {
            if (strpos($cap, $post_type) !== false) {
                return $this->map_post_type_meta_caps($caps, $cap, $user_id, $args, $post_type);
            }
        }

        return $caps;
    }

    /**
     * Map meta capabilities for a specific post type
     */
    private function map_post_type_meta_caps($caps, $cap, $user_id, $args, $post_type) {
        $post = get_post($args[0]);
        
        if (!$post) {
            return $caps;
        }

        // If user is not logged in
        if (!$user_id) {
            $caps[] = 'do_not_allow';
            return $caps;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return $caps;
        }

        // Administrators and managers can do everything
        if (array_intersect(array('administrator', 'real_estate_manager'), (array)$user->roles)) {
            return array();
        }

        // Get the author of the post
        $post_author = $post->post_author;

        // Basic reading
        if ($cap === "read_{$post_type}") {
            if ('private' === $post->post_status) {
                $caps[] = "read_private_{$post_type}s";
            }
            return $caps;
        }

        // Editing
        if ($cap === "edit_{$post_type}") {
            // Agents can only edit their own posts
            if ($user_id !== $post_author && in_array('real_estate_agent', (array)$user->roles)) {
                $caps[] = 'do_not_allow';
                return $caps;
            }

            if ($user_id === $post_author) {
                $caps[] = "edit_{$post_type}s";
            } else {
                $caps[] = "edit_others_{$post_type}s";
            }
            
            if ('private' === $post->post_status) {
                $caps[] = "edit_private_{$post_type}s";
            }
            return $caps;
        }

        // Deleting
        if ($cap === "delete_{$post_type}") {
            // Agents can only delete their own posts
            if ($user_id !== $post_author && in_array('real_estate_agent', (array)$user->roles)) {
                $caps[] = 'do_not_allow';
                return $caps;
            }

            if ($user_id === $post_author) {
                $caps[] = "delete_{$post_type}s";
            } else {
                $caps[] = "delete_others_{$post_type}s";
            }
            
            if ('private' === $post->post_status) {
                $caps[] = "delete_private_{$post_type}s";
            }
            return $caps;
        }

        return $caps;
    }

    /**
     * Check if user has capability for a specific post
     */
    public function user_can_for_post($capability, $post_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Admins and managers can do everything
        if (array_intersect(array('administrator', 'real_estate_manager'), (array)$user->roles)) {
            return true;
        }

        // Check if user has the 'all' version of the capability
        $all_capability = str_replace('own_', 'all_', $capability);
        if (user_can($user_id, $all_capability)) {
            return true;
        }

        // For 'own' capabilities, check if the user owns the post
        if (strpos($capability, 'own_') === 0) {
            $post = get_post($post_id);
            if (!$post) {
                return false;
            }

            return $post->post_author == $user_id && user_can($user_id, $capability);
        }

        return user_can($user_id, $capability);
    }

    /**
     * Get all users with manager role
     */
    public function get_all_managers() {
        return get_users(array('role' => 'real_estate_manager'));
    }

    /**
     * Get all users with agent role
     */
    public function get_all_agents() {
        return get_users(array('role' => 'real_estate_agent'));
    }

    /**
     * Get agents assigned to a manager
     */
    public function get_manager_agents($manager_id) {
        return get_users(array(
            'role' => 'real_estate_agent',
            'meta_key' => '_assigned_manager',
            'meta_value' => $manager_id
        ));
    }

    /**
     * Add the role management page to WordPress admin
     */
    public function add_menu_page() {
        add_submenu_page(
            'herohub-crm',
            __('Role Management', 'herohub-crm'),
            __('Role Management', 'herohub-crm'),
            'manage_agents',
            'herohub-role-management',
            array($this, 'render_page')
        );
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ('herohub-crm_page_herohub-role-management' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'herohub-role-management',
            plugins_url('assets/css/role-management.css', HEROHUB_PLUGIN_FILE),
            array(),
            HEROHUB_VERSION
        );

        wp_enqueue_script(
            'herohub-role-management',
            plugins_url('assets/js/role-management.js', HEROHUB_PLUGIN_FILE),
            array('jquery'),
            HEROHUB_VERSION,
            true
        );

        wp_localize_script('herohub-role-management', 'heroHubRoles', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('herohub_role_management'),
            'messages' => array(
                'assignSuccess' => __('Agent assigned successfully', 'herohub-crm'),
                'assignError' => __('Error assigning agent', 'herohub-crm'),
            ),
        ));
    }

    /**
     * Handle AJAX request to assign agent to manager
     */
    public function handle_agent_assignment() {
        check_ajax_referer('herohub_role_management', 'nonce');

        if (!current_user_can('manage_agents')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'herohub-crm'));
        }

        $agent_id = isset($_POST['agent_id']) ? intval($_POST['agent_id']) : 0;
        $manager_id = isset($_POST['manager_id']) ? intval($_POST['manager_id']) : 0;

        if (!$agent_id || !$manager_id) {
            wp_send_json_error(__('Invalid agent or manager ID.', 'herohub-crm'));
        }

        $agent = get_userdata($agent_id);
        $manager = get_userdata($manager_id);

        if (!$agent || !in_array('real_estate_agent', (array)$agent->roles)) {
            wp_send_json_error(__('Invalid agent.', 'herohub-crm'));
        }

        if (!$manager || !in_array('real_estate_manager', (array)$manager->roles)) {
            wp_send_json_error(__('Invalid manager.', 'herohub-crm'));
        }

        update_user_meta($agent_id, '_assigned_manager', $manager_id);
        wp_send_json_success(__('Agent assigned successfully.', 'herohub-crm'));
    }

    /**
     * Render the role management page
     */
    public function render_page() {
        if (!current_user_can('manage_agents')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'herohub-crm'));
        }

        $managers = $this->get_all_managers();
        $agents = $this->get_all_agents();
        
        include plugin_dir_path(HEROHUB_PLUGIN_FILE) . 'admin/partials/role-management-page.php';
    }
}
