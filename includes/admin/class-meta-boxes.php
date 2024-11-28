<?php
namespace HeroHub\CRM\Admin;

class Meta_Boxes {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes'], 10, 2);
    }

    public function register_meta_boxes() {
        // Contact Meta Boxes
        add_meta_box(
            'herohub_contact_details',
            __('Contact Details', 'herohub-crm'),
            [$this, 'render_contact_meta_box'],
            'contact',
            'normal',
            'high'
        );

        // Deal Meta Boxes
        add_meta_box(
            'herohub_deal_details',
            __('Deal Information', 'herohub-crm'),
            [$this, 'render_deal_meta_box'],
            'deal',
            'normal',
            'high'
        );
    }

    public function render_contact_meta_box($post) {
        wp_nonce_field('herohub_contact_meta_box', 'herohub_contact_meta_box_nonce');
        
        $contact_type = get_post_meta($post->ID, '_contact_type', true);
        $phone_number = get_post_meta($post->ID, '_phone_number', true);
        $email = get_post_meta($post->ID, '_email', true);
        $source = get_post_meta($post->ID, '_lead_source', true);
        
        ?>
        <div class="herohub-meta-box">
            <div class="herohub-field">
                <label for="contact_type"><?php _e('Contact Type', 'herohub-crm'); ?></label>
                <select name="contact_type" id="contact_type">
                    <option value="buyer" <?php selected($contact_type, 'buyer'); ?>><?php _e('Buyer', 'herohub-crm'); ?></option>
                    <option value="seller" <?php selected($contact_type, 'seller'); ?>><?php _e('Seller', 'herohub-crm'); ?></option>
                    <option value="investor" <?php selected($contact_type, 'investor'); ?>><?php _e('Investor', 'herohub-crm'); ?></option>
                </select>
            </div>
            <div class="herohub-field">
                <label for="phone_number"><?php _e('Phone Number', 'herohub-crm'); ?></label>
                <input type="tel" name="phone_number" id="phone_number" value="<?php echo esc_attr($phone_number); ?>">
            </div>
            <div class="herohub-field">
                <label for="email"><?php _e('Email', 'herohub-crm'); ?></label>
                <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>">
            </div>
            <div class="herohub-field">
                <label for="lead_source"><?php _e('Lead Source', 'herohub-crm'); ?></label>
                <input type="text" name="lead_source" id="lead_source" value="<?php echo esc_attr($source); ?>">
            </div>
        </div>
        <?php
    }

    public function render_deal_meta_box($post) {
        wp_nonce_field('herohub_deal_meta_box', 'herohub_deal_meta_box_nonce');
        
        $deal_value = get_post_meta($post->ID, '_deal_value', true);
        $deal_status = get_post_meta($post->ID, '_deal_status', true);
        $associated_contact = get_post_meta($post->ID, '_associated_contact', true);
        
        ?>
        <div class="herohub-meta-box">
            <div class="herohub-field">
                <label for="deal_value"><?php _e('Deal Value', 'herohub-crm'); ?></label>
                <input type="number" name="deal_value" id="deal_value" value="<?php echo esc_attr($deal_value); ?>">
            </div>
            <div class="herohub-field">
                <label for="deal_status"><?php _e('Deal Status', 'herohub-crm'); ?></label>
                <select name="deal_status" id="deal_status">
                    <option value="prospecting" <?php selected($deal_status, 'prospecting'); ?>><?php _e('Prospecting', 'herohub-crm'); ?></option>
                    <option value="negotiation" <?php selected($deal_status, 'negotiation'); ?>><?php _e('Negotiation', 'herohub-crm'); ?></option>
                    <option value="closed_won" <?php selected($deal_status, 'closed_won'); ?>><?php _e('Closed Won', 'herohub-crm'); ?></option>
                    <option value="closed_lost" <?php selected($deal_status, 'closed_lost'); ?>><?php _e('Closed Lost', 'herohub-crm'); ?></option>
                </select>
            </div>
            <div class="herohub-field">
                <label for="associated_contact"><?php _e('Associated Contact', 'herohub-crm'); ?></label>
                <?php 
                $contacts = get_posts(['post_type' => 'contact', 'numberposts' => -1]);
                ?>
                <select name="associated_contact" id="associated_contact">
                    <option value=""><?php _e('Select Contact', 'herohub-crm'); ?></option>
                    <?php foreach($contacts as $contact): ?>
                        <option value="<?php echo $contact->ID; ?>" <?php selected($associated_contact, $contact->ID); ?>>
                            <?php echo esc_html($contact->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }

    public function save_meta_boxes($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        
        // Contact meta save
        if ($post->post_type === 'contact') {
            if (!isset($_POST['herohub_contact_meta_box_nonce']) || 
                !wp_verify_nonce($_POST['herohub_contact_meta_box_nonce'], 'herohub_contact_meta_box')) {
                return;
            }
            
            if (isset($_POST['contact_type'])) {
                update_post_meta($post_id, '_contact_type', sanitize_text_field($_POST['contact_type']));
            }
            if (isset($_POST['phone_number'])) {
                update_post_meta($post_id, '_phone_number', sanitize_text_field($_POST['phone_number']));
            }
            if (isset($_POST['email'])) {
                update_post_meta($post_id, '_email', sanitize_email($_POST['email']));
            }
            if (isset($_POST['lead_source'])) {
                update_post_meta($post_id, '_lead_source', sanitize_text_field($_POST['lead_source']));
            }
        }

        // Deal meta save
        if ($post->post_type === 'deal') {
            if (!isset($_POST['herohub_deal_meta_box_nonce']) || 
                !wp_verify_nonce($_POST['herohub_deal_meta_box_nonce'], 'herohub_deal_meta_box')) {
                return;
            }
            
            if (isset($_POST['deal_value'])) {
                update_post_meta($post_id, '_deal_value', floatval($_POST['deal_value']));
            }
            if (isset($_POST['deal_status'])) {
                update_post_meta($post_id, '_deal_status', sanitize_text_field($_POST['deal_status']));
            }
            if (isset($_POST['associated_contact'])) {
                update_post_meta($post_id, '_associated_contact', intval($_POST['associated_contact']));
            }
        }
    }
}
