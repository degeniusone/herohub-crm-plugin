<?php
namespace HeroHub\CRM\Admin;

/**
 * Settings Class
 * Handles plugin settings and configuration
 */
class Settings {
    /**
     * Initialize settings
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'herohub-dashboard',
            __('Settings', 'herohub-crm'),
            __('Settings', 'herohub-crm'),
            'manage_options',
            'herohub-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('herohub_settings', 'herohub_settings');

        // General Settings
        add_settings_section(
            'herohub_general_settings',
            __('General Settings', 'herohub-crm'),
            array($this, 'render_general_section'),
            'herohub-settings'
        );

        add_settings_field(
            'currency',
            __('Currency', 'herohub-crm'),
            array($this, 'render_currency_field'),
            'herohub-settings',
            'herohub_general_settings'
        );

        add_settings_field(
            'date_format',
            __('Date Format', 'herohub-crm'),
            array($this, 'render_date_format_field'),
            'herohub-settings',
            'herohub_general_settings'
        );

        // Email Settings
        add_settings_section(
            'herohub_email_settings',
            __('Email Settings', 'herohub-crm'),
            array($this, 'render_email_section'),
            'herohub-settings'
        );

        add_settings_field(
            'enable_notifications',
            __('Enable Email Notifications', 'herohub-crm'),
            array($this, 'render_notifications_field'),
            'herohub-settings',
            'herohub_email_settings'
        );

        add_settings_field(
            'notification_email',
            __('Notification Email', 'herohub-crm'),
            array($this, 'render_notification_email_field'),
            'herohub-settings',
            'herohub_email_settings'
        );

        // Dashboard Settings
        add_settings_section(
            'herohub_dashboard_settings',
            __('Dashboard Settings', 'herohub-crm'),
            array($this, 'render_dashboard_section'),
            'herohub-settings'
        );

        add_settings_field(
            'items_per_page',
            __('Items Per Page', 'herohub-crm'),
            array($this, 'render_items_per_page_field'),
            'herohub-settings',
            'herohub_dashboard_settings'
        );

        add_settings_field(
            'refresh_interval',
            __('Dashboard Refresh Interval (seconds)', 'herohub-crm'),
            array($this, 'render_refresh_interval_field'),
            'herohub-settings',
            'herohub_dashboard_settings'
        );

        // SMS Settings
        add_settings_section(
            'herohub_crm_sms_settings',
            __('SMS Settings', 'herohub-crm'),
            array($this, 'render_sms_settings_section'),
            'herohub-settings'
        );

        // Twilio Account SID
        register_setting('herohub_settings', 'herohub_crm_twilio_account_sid');
        add_settings_field(
            'herohub_crm_twilio_account_sid',
            __('Twilio Account SID', 'herohub-crm'),
            array($this, 'render_text_field'),
            'herohub-settings',
            'herohub_crm_sms_settings',
            array(
                'label_for' => 'herohub_crm_twilio_account_sid',
                'description' => __('Your Twilio Account SID', 'herohub-crm')
            )
        );

        // Twilio Auth Token
        register_setting('herohub_settings', 'herohub_crm_twilio_auth_token');
        add_settings_field(
            'herohub_crm_twilio_auth_token',
            __('Twilio Auth Token', 'herohub-crm'),
            array($this, 'render_text_field'),
            'herohub-settings',
            'herohub_crm_sms_settings',
            array(
                'label_for' => 'herohub_crm_twilio_auth_token',
                'type' => 'password',
                'description' => __('Your Twilio Auth Token', 'herohub-crm')
            )
        );

        // Twilio Phone Number
        register_setting('herohub_settings', 'herohub_crm_twilio_phone_number');
        add_settings_field(
            'herohub_crm_twilio_phone_number',
            __('Twilio Phone Number', 'herohub-crm'),
            array($this, 'render_text_field'),
            'herohub-settings',
            'herohub_crm_sms_settings',
            array(
                'label_for' => 'herohub_crm_twilio_phone_number',
                'description' => __('Your Twilio Phone Number (with country code)', 'herohub-crm')
            )
        );

        // SMS Templates
        register_setting('herohub_settings', 'herohub_crm_sms_welcome_template');
        add_settings_field(
            'herohub_crm_sms_welcome_template',
            __('Welcome Message Template', 'herohub-crm'),
            array($this, 'render_textarea_field'),
            'herohub-settings',
            'herohub_crm_sms_settings',
            array(
                'label_for' => 'herohub_crm_sms_welcome_template',
                'description' => __('Template for welcome messages. Available variables: {name}, {agent}', 'herohub-crm')
            )
        );

        register_setting('herohub_settings', 'herohub_crm_sms_appointment_template');
        add_settings_field(
            'herohub_crm_sms_appointment_template',
            __('Appointment Reminder Template', 'herohub-crm'),
            array($this, 'render_textarea_field'),
            'herohub-settings',
            'herohub_crm_sms_settings',
            array(
                'label_for' => 'herohub_crm_sms_appointment_template',
                'description' => __('Template for appointment reminders. Available variables: {date}, {time}', 'herohub-crm')
            )
        );

        register_setting('herohub_settings', 'herohub_crm_sms_deal_update_template');
        add_settings_field(
            'herohub_crm_sms_deal_update_template',
            __('Deal Update Template', 'herohub-crm'),
            array($this, 'render_textarea_field'),
            'herohub-settings',
            'herohub_crm_sms_settings',
            array(
                'label_for' => 'herohub_crm_sms_deal_update_template',
                'description' => __('Template for deal status updates. Available variables: {status}', 'herohub-crm')
            )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=herohub-settings&tab=general" 
                   class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'herohub-crm'); ?>
                </a>
                <a href="?page=herohub-settings&tab=email" 
                   class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Email', 'herohub-crm'); ?>
                </a>
                <a href="?page=herohub-settings&tab=dashboard" 
                   class="nav-tab <?php echo $active_tab == 'dashboard' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Dashboard', 'herohub-crm'); ?>
                </a>
                <a href="?page=herohub-settings&tab=sms" 
                   class="nav-tab <?php echo $active_tab == 'sms' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('SMS', 'herohub-crm'); ?>
                </a>
            </h2>

            <form action="options.php" method="post">
                <?php
                settings_fields('herohub_settings');
                do_settings_sections('herohub-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render settings sections
     */
    public function render_general_section() {
        echo '<p>' . __('Configure general plugin settings.', 'herohub-crm') . '</p>';
    }

    public function render_email_section() {
        echo '<p>' . __('Configure email notification settings.', 'herohub-crm') . '</p>';
    }

    public function render_dashboard_section() {
        echo '<p>' . __('Configure dashboard display settings.', 'herohub-crm') . '</p>';
    }

    public function render_sms_settings_section() {
        echo '<p>' . __('Configure your SMS settings and message templates. SMS functionality is powered by Twilio.', 'herohub-crm') . '</p>';
    }

    /**
     * Render settings fields
     */
    public function render_currency_field() {
        $options = get_option('herohub_settings');
        $currency = isset($options['currency']) ? $options['currency'] : 'USD';
        ?>
        <select name="herohub_settings[currency]">
            <option value="USD" <?php selected($currency, 'USD'); ?>>USD ($)</option>
            <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR (€)</option>
            <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP (£)</option>
        </select>
        <?php
    }

    public function render_date_format_field() {
        $options = get_option('herohub_settings');
        $format = isset($options['date_format']) ? $options['date_format'] : 'Y-m-d';
        ?>
        <select name="herohub_settings[date_format]">
            <option value="Y-m-d" <?php selected($format, 'Y-m-d'); ?>>YYYY-MM-DD</option>
            <option value="m/d/Y" <?php selected($format, 'm/d/Y'); ?>>MM/DD/YYYY</option>
            <option value="d/m/Y" <?php selected($format, 'd/m/Y'); ?>>DD/MM/YYYY</option>
        </select>
        <?php
    }

    public function render_notifications_field() {
        $options = get_option('herohub_settings');
        $enabled = isset($options['enable_notifications']) ? $options['enable_notifications'] : 1;
        ?>
        <label>
            <input type="checkbox" name="herohub_settings[enable_notifications]" value="1" <?php checked($enabled, 1); ?>>
            <?php _e('Enable email notifications for important events', 'herohub-crm'); ?>
        </label>
        <?php
    }

    public function render_notification_email_field() {
        $options = get_option('herohub_settings');
        $email = isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email');
        ?>
        <input type="email" name="herohub_settings[notification_email]" value="<?php echo esc_attr($email); ?>" class="regular-text">
        <?php
    }

    public function render_items_per_page_field() {
        $options = get_option('herohub_settings');
        $items = isset($options['items_per_page']) ? $options['items_per_page'] : 10;
        ?>
        <input type="number" name="herohub_settings[items_per_page]" value="<?php echo esc_attr($items); ?>" min="5" max="100" step="5">
        <?php
    }

    public function render_refresh_interval_field() {
        $options = get_option('herohub_settings');
        $interval = isset($options['refresh_interval']) ? $options['refresh_interval'] : 30;
        ?>
        <input type="number" name="herohub_settings[refresh_interval]" value="<?php echo esc_attr($interval); ?>" min="10" max="300" step="5">
        <p class="description"><?php _e('Set to 0 to disable auto-refresh', 'herohub-crm'); ?></p>
        <?php
    }

    public function render_text_field($args) {
        $option = get_option($args['label_for']);
        ?>
        <input 
            type="<?php echo isset($args['type']) ? $args['type'] : 'text'; ?>" 
            id="<?php echo esc_attr($args['label_for']); ?>" 
            name="<?php echo esc_attr($args['label_for']); ?>" 
            value="<?php echo esc_attr($option); ?>" 
            class="regular-text"
        />
        <?php
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function render_textarea_field($args) {
        $option = get_option($args['label_for']);
        ?>
        <textarea 
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr($args['label_for']); ?>"
            rows="3"
            class="large-text"
        ><?php echo esc_textarea($option); ?></textarea>
        <?php
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
}
