<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Save settings if form is submitted
if (isset($_POST['herohub_crm_settings_nonce']) && wp_verify_nonce($_POST['herohub_crm_settings_nonce'], 'herohub_crm_settings')) {
    // Sanitize and save settings
    $settings = array(
        'company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
        'company_email' => sanitize_email($_POST['company_email'] ?? ''),
        'company_phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
        'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? '1' : '0',
    );
    
    update_option('herohub_crm_settings', $settings);
    add_settings_error('herohub_crm_messages', 'herohub_crm_message', __('Settings Saved', 'herohub-crm'), 'updated');
}

// Get current settings
$settings = get_option('herohub_crm_settings', array(
    'company_name' => '',
    'company_email' => '',
    'company_phone' => '',
    'enable_email_notifications' => '0',
));

// Show settings errors/notices
settings_errors('herohub_crm_messages');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('herohub_crm_settings', 'herohub_crm_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="company_name"><?php _e('Company Name', 'herohub-crm'); ?></label>
                </th>
                <td>
                    <input type="text" id="company_name" name="company_name" 
                           value="<?php echo esc_attr($settings['company_name']); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="company_email"><?php _e('Company Email', 'herohub-crm'); ?></label>
                </th>
                <td>
                    <input type="email" id="company_email" name="company_email" 
                           value="<?php echo esc_attr($settings['company_email']); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="company_phone"><?php _e('Company Phone', 'herohub-crm'); ?></label>
                </th>
                <td>
                    <input type="text" id="company_phone" name="company_phone" 
                           value="<?php echo esc_attr($settings['company_phone']); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <?php _e('Email Notifications', 'herohub-crm'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_email_notifications" value="1" 
                               <?php checked($settings['enable_email_notifications'], '1'); ?>>
                        <?php _e('Enable email notifications for new activities', 'herohub-crm'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
