<?php
/**
 * Admin settings page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Save settings if form is submitted
if (isset($_POST['herohub_crm_settings_nonce']) && wp_verify_nonce($_POST['herohub_crm_settings_nonce'], 'herohub_crm_settings')) {
    // Lead Scoring Settings
    update_option('herohub_crm_lead_score_budget_weight', sanitize_text_field($_POST['lead_score_budget_weight']));
    update_option('herohub_crm_lead_score_interaction_weight', sanitize_text_field($_POST['lead_score_interaction_weight']));
    update_option('herohub_crm_lead_score_property_views_weight', sanitize_text_field($_POST['lead_score_property_views_weight']));
    update_option('herohub_crm_lead_score_timeline_weight', sanitize_text_field($_POST['lead_score_timeline_weight']));
    
    // Email Settings
    update_option('herohub_crm_email_from_name', sanitize_text_field($_POST['email_from_name']));
    update_option('herohub_crm_email_from_address', sanitize_email($_POST['email_from_address']));
    
    // Notification Settings
    update_option('herohub_crm_notify_new_lead', isset($_POST['notify_new_lead']));
    update_option('herohub_crm_notify_deal_status', isset($_POST['notify_deal_status']));
    
    add_settings_error('herohub_crm_messages', 'herohub_crm_message', __('Settings Saved', 'herohub-crm'), 'updated');
}

// Get current settings
$lead_score_budget_weight = get_option('herohub_crm_lead_score_budget_weight', 25);
$lead_score_interaction_weight = get_option('herohub_crm_lead_score_interaction_weight', 25);
$lead_score_property_views_weight = get_option('herohub_crm_lead_score_property_views_weight', 25);
$lead_score_timeline_weight = get_option('herohub_crm_lead_score_timeline_weight', 25);

$email_from_name = get_option('herohub_crm_email_from_name', get_bloginfo('name'));
$email_from_address = get_option('herohub_crm_email_from_address', get_bloginfo('admin_email'));

$notify_new_lead = get_option('herohub_crm_notify_new_lead', true);
$notify_deal_status = get_option('herohub_crm_notify_deal_status', true);

// Display settings errors
settings_errors('herohub_crm_messages');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('herohub_crm_settings', 'herohub_crm_settings_nonce'); ?>
        
        <div class="herohub-crm-settings-section">
            <h2><?php _e('Lead Scoring Settings', 'herohub-crm'); ?></h2>
            <p><?php _e('Configure the weights for different factors in lead scoring (total should equal 100).', 'herohub-crm'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lead_score_budget_weight"><?php _e('Budget Weight (%)', 'herohub-crm'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="lead_score_budget_weight" id="lead_score_budget_weight" 
                               value="<?php echo esc_attr($lead_score_budget_weight); ?>" min="0" max="100" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lead_score_interaction_weight"><?php _e('Interaction Weight (%)', 'herohub-crm'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="lead_score_interaction_weight" id="lead_score_interaction_weight" 
                               value="<?php echo esc_attr($lead_score_interaction_weight); ?>" min="0" max="100" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lead_score_property_views_weight"><?php _e('Property Views Weight (%)', 'herohub-crm'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="lead_score_property_views_weight" id="lead_score_property_views_weight" 
                               value="<?php echo esc_attr($lead_score_property_views_weight); ?>" min="0" max="100" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lead_score_timeline_weight"><?php _e('Timeline Weight (%)', 'herohub-crm'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="lead_score_timeline_weight" id="lead_score_timeline_weight" 
                               value="<?php echo esc_attr($lead_score_timeline_weight); ?>" min="0" max="100" required>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="herohub-crm-settings-section">
            <h2><?php _e('Email Settings', 'herohub-crm'); ?></h2>
            <p><?php _e('Configure email notification settings.', 'herohub-crm'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="email_from_name"><?php _e('From Name', 'herohub-crm'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="email_from_name" id="email_from_name" 
                               value="<?php echo esc_attr($email_from_name); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="email_from_address"><?php _e('From Email', 'herohub-crm'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="email_from_address" id="email_from_address" 
                               value="<?php echo esc_attr($email_from_address); ?>" class="regular-text" required>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="herohub-crm-settings-section">
            <h2><?php _e('Notification Settings', 'herohub-crm'); ?></h2>
            <p><?php _e('Configure when to receive notifications.', 'herohub-crm'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('New Lead Notifications', 'herohub-crm'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="notify_new_lead" value="1" <?php checked($notify_new_lead); ?>>
                            <?php _e('Notify when a new lead is created', 'herohub-crm'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Deal Status Notifications', 'herohub-crm'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="notify_deal_status" value="1" <?php checked($notify_deal_status); ?>>
                            <?php _e('Notify when a deal status changes', 'herohub-crm'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(__('Save Settings', 'herohub-crm')); ?>
    </form>
</div>
