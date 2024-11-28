<?php
namespace HeroHub\CRM\Admin;

use HeroHub\CRM\Core\Roles_Manager;

/**
 * Class Role_Management
 * Handles the role management interface in WordPress admin
 */
class Role_Management {
    /**
     * @var Roles_Manager
     */
    private $roles_manager;

    /**
     * Initialize the role management
     */
    public function __construct() {
        $this->roles_manager = new Roles_Manager();
        
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_assign_agent_to_manager', array($this, 'handle_agent_assignment'));
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
     * Render the role management page
     */
    public function render_page() {
        if (!current_user_can('manage_agents')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'herohub-crm'));
        }

        $managers = $this->roles_manager->get_all_managers();
        $agents = $this->roles_manager->get_all_agents();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Role Management', 'herohub-crm'); ?></h1>

            <div class="herohub-role-management">
                <div class="herohub-role-section">
                    <h2><?php echo esc_html__('Managers', 'herohub-crm'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Name', 'herohub-crm'); ?></th>
                                <th><?php echo esc_html__('Email', 'herohub-crm'); ?></th>
                                <th><?php echo esc_html__('Assigned Agents', 'herohub-crm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($managers as $manager): ?>
                                <tr>
                                    <td><?php echo esc_html($manager->display_name); ?></td>
                                    <td><?php echo esc_html($manager->user_email); ?></td>
                                    <td>
                                        <?php 
                                        $assigned_agents = $this->roles_manager->get_manager_agents($manager->ID);
                                        echo count($assigned_agents);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="herohub-role-section">
                    <h2><?php echo esc_html__('Agents', 'herohub-crm'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Name', 'herohub-crm'); ?></th>
                                <th><?php echo esc_html__('Email', 'herohub-crm'); ?></th>
                                <th><?php echo esc_html__('Assigned Manager', 'herohub-crm'); ?></th>
                                <th><?php echo esc_html__('Actions', 'herohub-crm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agents as $agent): 
                                $assigned_manager_id = get_user_meta($agent->ID, '_assigned_manager', true);
                                $assigned_manager = $assigned_manager_id ? get_userdata($assigned_manager_id) : null;
                            ?>
                                <tr>
                                    <td><?php echo esc_html($agent->display_name); ?></td>
                                    <td><?php echo esc_html($agent->user_email); ?></td>
                                    <td>
                                        <?php echo $assigned_manager ? esc_html($assigned_manager->display_name) : '-'; ?>
                                    </td>
                                    <td>
                                        <select class="herohub-manager-select" 
                                                data-agent-id="<?php echo esc_attr($agent->ID); ?>">
                                            <option value=""><?php echo esc_html__('Select Manager', 'herohub-crm'); ?></option>
                                            <?php foreach ($managers as $manager): ?>
                                                <option value="<?php echo esc_attr($manager->ID); ?>"
                                                    <?php selected($assigned_manager_id, $manager->ID); ?>>
                                                    <?php echo esc_html($manager->display_name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
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

        $result = $this->roles_manager->assign_agent_to_manager($agent_id, $manager_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(__('Agent assigned successfully', 'herohub-crm'));
    }
}
