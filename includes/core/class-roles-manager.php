<?php
namespace HeroHub\CRM\Core;

/**
 * Class Roles_Manager
 * Handles the creation and management of custom user roles and capabilities
 */
class Roles_Manager {
    /**
     * Initialize the roles manager
     */
    public function __construct() {
        add_action('init', array($this, 'register_roles'));
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
     * Check if user has capability for a specific post
     * 
     * @param string $capability The capability to check
     * @param int $post_id The post ID
     * @param int $user_id Optional. The user ID. Defaults to current user.
     * @return bool Whether the user has the capability
     */
    public function user_can_for_post($capability, $post_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Admins can do everything
        if (in_array('administrator', (array)$user->roles)) {
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

            // Check if user is the assigned agent
            $assigned_agent = get_post_meta($post_id, '_assigned_agent', true);
            if ($assigned_agent == $user_id && user_can($user_id, $capability)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has manager capabilities
     * 
     * @param int $user_id Optional. The user ID. Defaults to current user.
     * @return bool Whether the user is a manager
     */
    public function is_manager($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        return in_array('administrator', (array)$user->roles) || 
               in_array('real_estate_manager', (array)$user->roles);
    }

    /**
     * Check if user has agent capabilities
     * 
     * @param int $user_id Optional. The user ID. Defaults to current user.
     * @return bool Whether the user is an agent
     */
    public function is_agent($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        return in_array('real_estate_agent', (array)$user->roles);
    }

    /**
     * Get all agents
     * 
     * @return array Array of WP_User objects with agent role
     */
    public function get_all_agents() {
        return get_users(array(
            'role' => 'real_estate_agent',
        ));
    }

    /**
     * Get all managers
     * 
     * @return array Array of WP_User objects with manager role
     */
    public function get_all_managers() {
        return get_users(array(
            'role' => 'real_estate_manager',
        ));
    }

    /**
     * Get agents assigned to a manager
     * 
     * @param int $manager_id The manager's user ID
     * @return array Array of WP_User objects
     */
    public function get_manager_agents($manager_id) {
        return get_users(array(
            'role' => 'real_estate_agent',
            'meta_key' => '_assigned_manager',
            'meta_value' => $manager_id,
        ));
    }

    /**
     * Assign an agent to a manager
     * 
     * @param int $agent_id The agent's user ID
     * @param int $manager_id The manager's user ID
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function assign_agent_to_manager($agent_id, $manager_id) {
        if (!$this->is_manager($manager_id)) {
            return new \WP_Error('invalid_manager', __('Invalid manager ID', 'herohub-crm'));
        }

        if (!$this->is_agent($agent_id)) {
            return new \WP_Error('invalid_agent', __('Invalid agent ID', 'herohub-crm'));
        }

        return update_user_meta($agent_id, '_assigned_manager', $manager_id);
    }
}
