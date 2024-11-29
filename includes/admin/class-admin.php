<?php
namespace HeroHub\CRM\Admin;

if (!defined('WPINC')) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 */
class Admin {
    private $version;

    public function __construct($version) {
        $this->version = $version;
        $this->init_hooks();
    }

    /**
     * Register all admin hooks
     */
    private function init_hooks() {
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Dashboard widgets
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'herohub-crm-admin',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/herohub-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        // Enqueue jQuery UI and its dependencies
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_style('jquery-ui-theme', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');

        wp_enqueue_script(
            'herohub-crm-admin',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-tooltip'),
            $this->version,
            false
        );

        wp_enqueue_script(
            'herohub-crm-role-management',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/role-management.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script('herohub-crm-admin', 'herohubAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('herohub_crm_nonce'),
        ));
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('HeroHub CRM', 'herohub-crm'),
            __('HeroHub CRM', 'herohub-crm'),
            'manage_options',
            'herohub-crm',
            array($this, 'render_dashboard'),
            'dashicons-businessman',
            30
        );

        add_submenu_page(
            'herohub-crm',
            __('Dashboard', 'herohub-crm'),
            __('Dashboard', 'herohub-crm'),
            'manage_options',
            'herohub-crm',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'herohub-crm',
            __('Settings', 'herohub-crm'),
            __('Settings', 'herohub-crm'),
            'manage_options',
            'herohub-crm-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'herohub-crm',
            __('Role Management', 'herohub-crm'),
            __('Role Management', 'herohub-crm'),
            'manage_options',
            'herohub-crm-role-management',
            array($this, 'render_role_management_page')
        );
    }

    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'herohub_crm_dashboard_widget',
            __('HeroHub CRM Overview', 'herohub-crm'),
            array($this, 'render_dashboard_widget')
        );
    }

    /**
     * Render dashboard based on user role
     */
    public function render_dashboard() {
        $current_user = wp_get_current_user();
        $roles = $current_user->roles;

        // Default to admin dashboard if no specific role found
        $dashboard_template = 'dashboard-admin.php';

        if (in_array('herohub_manager', $roles)) {
            $dashboard_template = 'dashboard-manager.php';
        } elseif (in_array('herohub_agent', $roles)) {
            $dashboard_template = 'dashboard-agent.php';
        }

        // Include the appropriate dashboard template
        include_once plugin_dir_path(dirname(__FILE__)) . '../templates/admin/' . $dashboard_template;
    }

    /**
     * Render the dashboard widget
     */
    public function render_dashboard_widget() {
        $stats = $this->get_crm_statistics();
        ?>
        <div class="herohub-dashboard-widget">
            <div class="today-stats">
                <h4><?php _e('CRM Overview', 'herohub-crm'); ?></h4>
                <ul>
                    <li><?php printf(__('Properties: %d', 'herohub-crm'), $stats['properties']); ?></li>
                    <li><?php printf(__('Active Deals: %d', 'herohub-crm'), $stats['deals']); ?></li>
                    <li><?php printf(__('Total Contacts: %d', 'herohub-crm'), $stats['contacts']); ?></li>
                    <li><?php printf(__('Recent Activities: %d', 'herohub-crm'), $stats['activities']); ?></li>
                </ul>
            </div>

            <div class="quick-links">
                <a href="<?php echo admin_url('admin.php?page=herohub-crm'); ?>" class="button">
                    <?php _e('Go to Dashboard', 'herohub-crm'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Get CRM statistics
     */
    private function get_crm_statistics() {
        return array(
            'properties' => wp_count_posts('property')->publish,
            'deals' => wp_count_posts('deal')->publish,
            'contacts' => wp_count_posts('contact')->publish,
            'activities' => wp_count_posts('activity')->publish
        );
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'herohub-crm'));
        }

        // Get settings
        $settings = get_option('herohub_crm_settings', array());

        // Save settings
        if (isset($_POST['herohub_crm_settings_nonce']) && wp_verify_nonce($_POST['herohub_crm_settings_nonce'], 'herohub_crm_settings')) {
            $settings['sms_enabled'] = isset($_POST['sms_enabled']);
            $settings['sms_api_key'] = sanitize_text_field($_POST['sms_api_key']);
            $settings['email_notifications'] = isset($_POST['email_notifications']);
            $settings['notification_email'] = sanitize_email($_POST['notification_email']);
            $settings['deal_stages'] = array_map('sanitize_text_field', explode("\n", $_POST['deal_stages']));
            $settings['property_types'] = array_map('sanitize_text_field', explode("\n", $_POST['property_types']));
            
            update_option('herohub_crm_settings', $settings);
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'herohub-crm') . '</p></div>';
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form method="post" action="">
                <?php wp_nonce_field('herohub_crm_settings', 'herohub_crm_settings_nonce'); ?>

                <div class="herohub-settings">
                    <h2><?php _e('Notification Settings', 'herohub-crm'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('SMS Notifications', 'herohub-crm'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="sms_enabled" value="1" 
                                           <?php checked(isset($settings['sms_enabled']) ? $settings['sms_enabled'] : false); ?>>
                                    <?php _e('Enable SMS notifications', 'herohub-crm'); ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php _e('SMS API Key', 'herohub-crm'); ?></th>
                            <td>
                                <input type="text" name="sms_api_key" class="regular-text" 
                                       value="<?php echo esc_attr(isset($settings['sms_api_key']) ? $settings['sms_api_key'] : ''); ?>">
                                <p class="description">
                                    <?php _e('Enter your SMS service API key', 'herohub-crm'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php _e('Email Notifications', 'herohub-crm'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="email_notifications" value="1" 
                                           <?php checked(isset($settings['email_notifications']) ? $settings['email_notifications'] : false); ?>>
                                    <?php _e('Enable email notifications', 'herohub-crm'); ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php _e('Notification Email', 'herohub-crm'); ?></th>
                            <td>
                                <input type="email" name="notification_email" class="regular-text" 
                                       value="<?php echo esc_attr(isset($settings['notification_email']) ? $settings['notification_email'] : ''); ?>">
                                <p class="description">
                                    <?php _e('Email address for notifications', 'herohub-crm'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h2><?php _e('Deal Settings', 'herohub-crm'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Deal Stages', 'herohub-crm'); ?></th>
                            <td>
                                <textarea name="deal_stages" rows="5" class="large-text code"><?php 
                                    echo esc_textarea(isset($settings['deal_stages']) ? implode("\n", $settings['deal_stages']) : ''); 
                                ?></textarea>
                                <p class="description">
                                    <?php _e('Enter one deal stage per line', 'herohub-crm'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h2><?php _e('Property Settings', 'herohub-crm'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Property Types', 'herohub-crm'); ?></th>
                            <td>
                                <textarea name="property_types" rows="5" class="large-text code"><?php 
                                    echo esc_textarea(isset($settings['property_types']) ? implode("\n", $settings['property_types']) : ''); 
                                ?></textarea>
                                <p class="description">
                                    <?php _e('Enter one property type per line', 'herohub-crm'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get agents assigned to a manager
     */
    private function get_manager_agents($manager_id) {
        return get_users(array(
            'role' => 'real_estate_agent',
            'meta_key' => '_manager_id',
            'meta_value' => $manager_id
        ));
    }

    /**
     * Render the role management page
     */
    public function render_role_management_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'herohub-crm'));
        }

        $managers = get_users(array('role' => 'real_estate_manager'));
        $agents = get_users(array('role' => 'real_estate_agent'));

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
                                <th><?php echo esc_html__('Actions', 'herohub-crm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($managers)) : ?>
                                <?php foreach ($managers as $manager) : 
                                    $assigned_agents = $this->get_manager_agents($manager->ID);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($manager->display_name); ?></td>
                                        <td><?php echo esc_html($manager->user_email); ?></td>
                                        <td><?php echo count($assigned_agents); ?></td>
                                        <td>
                                            <button type="button" 
                                                    class="button view-agents" 
                                                    data-manager-id="<?php echo esc_attr($manager->ID); ?>">
                                                <?php echo esc_html__('View Agents', 'herohub-crm'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4"><?php echo esc_html__('No managers found.', 'herohub-crm'); ?></td>
                                </tr>
                            <?php endif; ?>
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
                            <?php if (!empty($agents)) : ?>
                                <?php foreach ($agents as $agent) : 
                                    $manager_id = get_user_meta($agent->ID, '_manager_id', true);
                                    $manager = $manager_id ? get_user_by('id', $manager_id) : null;
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($agent->display_name); ?></td>
                                        <td><?php echo esc_html($agent->user_email); ?></td>
                                        <td><?php echo $manager ? esc_html($manager->display_name) : __('Not assigned', 'herohub-crm'); ?></td>
                                        <td>
                                            <button type="button" 
                                                    class="button assign-manager" 
                                                    data-agent-id="<?php echo esc_attr($agent->ID); ?>">
                                                <?php echo esc_html__('Assign Manager', 'herohub-crm'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4"><?php echo esc_html__('No agents found.', 'herohub-crm'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the main admin display page
     */
    public function render_admin_display() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'herohub-crm'));
        }

        // Get dashboard data
        $total_leads = wp_count_posts('herohub_lead')->publish;
        $total_deals = wp_count_posts('herohub_deal')->publish;
        $total_properties = wp_count_posts('herohub_property')->publish;

        // Get recent leads
        $recent_leads = get_posts(array(
            'post_type' => 'herohub_lead',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        // Get active deals
        $active_deals = get_posts(array(
            'post_type' => 'herohub_deal',
            'posts_per_page' => 5,
            'orderby' => 'modified',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_deal_status',
                    'value' => array('active', 'negotiation', 'pending'),
                    'compare' => 'IN'
                )
            )
        ));

        ?>
        <div class="wrap herohub-crm-dashboard">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <!-- Overview Cards -->
            <div class="herohub-crm-overview">
                <div class="herohub-crm-card">
                    <h3><?php _e('Total Leads', 'herohub-crm'); ?></h3>
                    <div class="herohub-crm-card-number"><?php echo esc_html($total_leads); ?></div>
                    <a href="<?php echo admin_url('edit.php?post_type=herohub_lead'); ?>" class="button">
                        <?php _e('View All Leads', 'herohub-crm'); ?>
                    </a>
                </div>

                <div class="herohub-crm-card">
                    <h3><?php _e('Active Deals', 'herohub-crm'); ?></h3>
                    <div class="herohub-crm-card-number"><?php echo esc_html($total_deals); ?></div>
                    <a href="<?php echo admin_url('edit.php?post_type=herohub_deal'); ?>" class="button">
                        <?php _e('View All Deals', 'herohub-crm'); ?>
                    </a>
                </div>

                <div class="herohub-crm-card">
                    <h3><?php _e('Properties', 'herohub-crm'); ?></h3>
                    <div class="herohub-crm-card-number"><?php echo esc_html($total_properties); ?></div>
                    <a href="<?php echo admin_url('edit.php?post_type=herohub_property'); ?>" class="button">
                        <?php _e('View All Properties', 'herohub-crm'); ?>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="herohub-crm-recent">
                <div class="herohub-crm-section">
                    <h2><?php _e('Recent Leads', 'herohub-crm'); ?></h2>
                    <div class="herohub-crm-table-wrapper">
                        <?php if (!empty($recent_leads)) : ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Name', 'herohub-crm'); ?></th>
                                        <th><?php _e('Email', 'herohub-crm'); ?></th>
                                        <th><?php _e('Phone', 'herohub-crm'); ?></th>
                                        <th><?php _e('Date', 'herohub-crm'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_leads as $lead) : 
                                        $lead_meta = get_post_meta($lead->ID);
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo get_edit_post_link($lead->ID); ?>">
                                                    <?php echo esc_html($lead->post_title); ?>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html($lead_meta['_lead_email'][0] ?? ''); ?></td>
                                            <td><?php echo esc_html($lead_meta['_lead_phone'][0] ?? ''); ?></td>
                                            <td><?php echo get_the_date('', $lead->ID); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p><?php _e('No recent leads found.', 'herohub-crm'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="herohub-crm-section">
                    <h2><?php _e('Active Deals', 'herohub-crm'); ?></h2>
                    <div class="herohub-crm-table-wrapper">
                        <?php if (!empty($active_deals)) : ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Deal', 'herohub-crm'); ?></th>
                                        <th><?php _e('Property', 'herohub-crm'); ?></th>
                                        <th><?php _e('Status', 'herohub-crm'); ?></th>
                                        <th><?php _e('Value', 'herohub-crm'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_deals as $deal) : 
                                        $deal_meta = get_post_meta($deal->ID);
                                        $property_id = $deal_meta['_property_id'][0] ?? '';
                                        $property = $property_id ? get_post($property_id) : null;
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo get_edit_post_link($deal->ID); ?>">
                                                    <?php echo esc_html($deal->post_title); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($property) : ?>
                                                    <a href="<?php echo get_edit_post_link($property->ID); ?>">
                                                        <?php echo esc_html($property->post_title); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo esc_html($deal_meta['_deal_status'][0] ?? ''); ?></td>
                                            <td><?php echo esc_html($deal_meta['_deal_value'][0] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p><?php _e('No active deals found.', 'herohub-crm'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
