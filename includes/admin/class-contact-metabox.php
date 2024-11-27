<?php
namespace HeroHub\CRM\Admin;

class Contact_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_contact', array($this, 'save_metabox'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'contact_details',
            __('Contact Details', 'herohub-crm'),
            array($this, 'render_metabox'),
            'contact',
            'normal',
            'high'
        );
    }

    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('contact_details_nonce', 'contact_details_nonce');

        // Get saved values
        $email = get_post_meta($post->ID, '_contact_email', true);
        $phone = get_post_meta($post->ID, '_contact_phone', true);
        $mobile = get_post_meta($post->ID, '_contact_mobile', true);
        $address = get_post_meta($post->ID, '_contact_address', true);
        $city = get_post_meta($post->ID, '_contact_city', true);
        $state = get_post_meta($post->ID, '_contact_state', true);
        $zip = get_post_meta($post->ID, '_contact_zip', true);
        $lead_source = get_post_meta($post->ID, '_contact_lead_source', true);
        $notes = get_post_meta($post->ID, '_contact_notes', true);
        ?>
        <div class="herohub-metabox">
            <p>
                <label for="contact_email"><?php _e('Email Address:', 'herohub-crm'); ?></label>
                <input type="email" id="contact_email" name="contact_email" 
                       value="<?php echo esc_attr($email); ?>" class="widefat">
            </p>

            <p>
                <label for="contact_phone"><?php _e('Phone Number:', 'herohub-crm'); ?></label>
                <input type="text" id="contact_phone" name="contact_phone" 
                       value="<?php echo esc_attr($phone); ?>" class="widefat herohub-phone-field">
            </p>

            <p>
                <label for="contact_mobile"><?php _e('Mobile Number:', 'herohub-crm'); ?></label>
                <input type="text" id="contact_mobile" name="contact_mobile" 
                       value="<?php echo esc_attr($mobile); ?>" class="widefat herohub-phone-field">
            </p>

            <p>
                <label for="contact_address"><?php _e('Address:', 'herohub-crm'); ?></label>
                <input type="text" id="contact_address" name="contact_address" 
                       value="<?php echo esc_attr($address); ?>" class="widefat">
            </p>

            <div class="herohub-address-group">
                <p class="herohub-city">
                    <label for="contact_city"><?php _e('City:', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_city" name="contact_city" 
                           value="<?php echo esc_attr($city); ?>">
                </p>

                <p class="herohub-state">
                    <label for="contact_state"><?php _e('State:', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_state" name="contact_state" 
                           value="<?php echo esc_attr($state); ?>">
                </p>

                <p class="herohub-zip">
                    <label for="contact_zip"><?php _e('ZIP Code:', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_zip" name="contact_zip" 
                           value="<?php echo esc_attr($zip); ?>">
                </p>
            </div>

            <p>
                <label for="contact_lead_source"><?php _e('Lead Source:', 'herohub-crm'); ?></label>
                <select id="contact_lead_source" name="contact_lead_source" class="widefat">
                    <option value=""><?php _e('Select Lead Source', 'herohub-crm'); ?></option>
                    <?php
                    $sources = array(
                        'website' => __('Website', 'herohub-crm'),
                        'referral' => __('Referral', 'herohub-crm'),
                        'cold_call' => __('Cold Call', 'herohub-crm'),
                        'social_media' => __('Social Media', 'herohub-crm'),
                        'other' => __('Other', 'herohub-crm'),
                    );
                    foreach ($sources as $value => $label) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($value),
                            selected($lead_source, $value, false),
                            esc_html($label)
                        );
                    }
                    ?>
                </select>
            </p>

            <p>
                <label for="contact_notes"><?php _e('Notes:', 'herohub-crm'); ?></label>
                <textarea id="contact_notes" name="contact_notes" class="widefat" 
                          rows="5"><?php echo esc_textarea($notes); ?></textarea>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id) {
        // Check if nonce is set
        if (!isset($_POST['contact_details_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['contact_details_nonce'], 'contact_details_nonce')) {
            return;
        }

        // If this is an autosave, don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save contact details
        $fields = array(
            '_contact_email' => 'sanitize_email',
            '_contact_phone' => 'sanitize_text_field',
            '_contact_mobile' => 'sanitize_text_field',
            '_contact_address' => 'sanitize_text_field',
            '_contact_city' => 'sanitize_text_field',
            '_contact_state' => 'sanitize_text_field',
            '_contact_zip' => 'sanitize_text_field',
            '_contact_lead_source' => 'sanitize_text_field',
            '_contact_notes' => 'sanitize_textarea_field',
        );

        foreach ($fields as $meta_key => $sanitize_callback) {
            $field_name = ltrim($meta_key, '_');
            if (isset($_POST[$field_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$field_name]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
}
