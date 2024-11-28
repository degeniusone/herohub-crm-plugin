<?php
namespace HeroHub\CRM\Admin;

class Meta_Boxes {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes'], 10, 2);
    }

    public function register_meta_boxes() {
        add_meta_box(
            'herohub_contact_personal_info',
            __('Personal Information', 'herohub-crm'),
            [$this, 'render_contact_personal_info_meta_box'],
            'contact',
            'normal',
            'high'
        );
    }

    public function render_contact_personal_info_meta_box($post) {
        wp_nonce_field('herohub_contact_personal_info', 'herohub_contact_personal_info_nonce');
        
        $first_name = get_post_meta($post->ID, '_first_name', true);
        $last_name = get_post_meta($post->ID, '_last_name', true);
        $email = get_post_meta($post->ID, '_email', true);
        $nationality = get_post_meta($post->ID, '_nationality', true);
        $mobile_phone = get_post_meta($post->ID, '_mobile_phone', true);
        $whatsapp = get_post_meta($post->ID, '_whatsapp', true);
        $interest = get_post_meta($post->ID, '_interest', true);
        $source = get_post_meta($post->ID, '_source', true);
        
        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="first_name"><?php _e('First Name', 'herohub-crm'); ?></label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($first_name); ?>">
                </div>
                <div class="herohub-field">
                    <label for="last_name"><?php _e('Last Name', 'herohub-crm'); ?></label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($last_name); ?>">
                </div>
                <div class="herohub-field">
                    <label for="email"><?php _e('Email', 'herohub-crm'); ?></label>
                    <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>">
                </div>
                <div class="herohub-field">
                    <label for="nationality"><?php _e('Nationality', 'herohub-crm'); ?></label>
                    <select name="nationality" id="nationality">
                        <option value="" <?php selected($nationality, ''); ?>><?php _e('Select Nationality', 'herohub-crm'); ?></option>
                        <option value="emirati" <?php selected($nationality, 'emirati'); ?>><?php _e('Emirati', 'herohub-crm'); ?></option>
                        <option value="indian" <?php selected($nationality, 'indian'); ?>><?php _e('Indian', 'herohub-crm'); ?></option>
                        <option value="pakistani" <?php selected($nationality, 'pakistani'); ?>><?php _e('Pakistani', 'herohub-crm'); ?></option>
                        <option value="british" <?php selected($nationality, 'british'); ?>><?php _e('British', 'herohub-crm'); ?></option>
                        <option value="egyptian" <?php selected($nationality, 'egyptian'); ?>><?php _e('Egyptian', 'herohub-crm'); ?></option>
                        <option value="russian" <?php selected($nationality, 'russian'); ?>><?php _e('Russian', 'herohub-crm'); ?></option>
                        <option value="chinese" <?php selected($nationality, 'chinese'); ?>><?php _e('Chinese', 'herohub-crm'); ?></option>
                        <option value="other" <?php selected($nationality, 'other'); ?>><?php _e('Other', 'herohub-crm'); ?></option>
                    </select>
                </div>
                <div class="herohub-field">
                    <label for="mobile_phone"><?php _e('Mobile Phone', 'herohub-crm'); ?></label>
                    <input type="tel" name="mobile_phone" id="mobile_phone" value="<?php echo esc_attr($mobile_phone); ?>">
                </div>
                <div class="herohub-field">
                    <label for="whatsapp"><?php _e('WhatsApp', 'herohub-crm'); ?></label>
                    <input type="tel" name="whatsapp" id="whatsapp" value="<?php echo esc_attr($whatsapp); ?>">
                </div>
                <div class="herohub-field">
                    <label for="interest"><?php _e('Interest', 'herohub-crm'); ?></label>
                    <select name="interest" id="interest">
                        <option value="" <?php selected($interest, ''); ?>><?php _e('Select Interest', 'herohub-crm'); ?></option>
                        <option value="buy" <?php selected($interest, 'buy'); ?>><?php _e('Buy', 'herohub-crm'); ?></option>
                        <option value="sell" <?php selected($interest, 'sell'); ?>><?php _e('Sell', 'herohub-crm'); ?></option>
                        <option value="rent" <?php selected($interest, 'rent'); ?>><?php _e('Rent', 'herohub-crm'); ?></option>
                        <option value="invest" <?php selected($interest, 'invest'); ?>><?php _e('Invest', 'herohub-crm'); ?></option>
                        <option value="commercial" <?php selected($interest, 'commercial'); ?>><?php _e('Commercial', 'herohub-crm'); ?></option>
                    </select>
                </div>
                <div class="herohub-field">
                    <label for="source"><?php _e('Source', 'herohub-crm'); ?></label>
                    <select name="source" id="source">
                        <option value="" <?php selected($source, ''); ?>><?php _e('Select Source', 'herohub-crm'); ?></option>
                        <option value="dld_list" <?php selected($source, 'dld_list'); ?>><?php _e('DLD List', 'herohub-crm'); ?></option>
                        <option value="green_list" <?php selected($source, 'green_list'); ?>><?php _e('Green List', 'herohub-crm'); ?></option>
                        <option value="contact_form" <?php selected($source, 'contact_form'); ?>><?php _e('Contact Form', 'herohub-crm'); ?></option>
                        <option value="chat_bot" <?php selected($source, 'chat_bot'); ?>><?php _e('Chat Bot', 'herohub-crm'); ?></option>
                        <option value="propertyfinder_listing" <?php selected($source, 'propertyfinder_listing'); ?>><?php _e('PropertyFinder Listing', 'herohub-crm'); ?></option>
                        <option value="website_listing" <?php selected($source, 'website_listing'); ?>><?php _e('Website Listing', 'herohub-crm'); ?></option>
                        <option value="advertisement" <?php selected($source, 'advertisement'); ?>><?php _e('Advertisement', 'herohub-crm'); ?></option>
                        <option value="social_media" <?php selected($source, 'social_media'); ?>><?php _e('Social Media', 'herohub-crm'); ?></option>
                        <option value="referrals" <?php selected($source, 'referrals'); ?>><?php _e('Referrals', 'herohub-crm'); ?></option>
                        <option value="other" <?php selected($source, 'other'); ?>><?php _e('Other', 'herohub-crm'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }

    public function save_meta_boxes($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        
        if ($post->post_type === 'contact') {
            if (!isset($_POST['herohub_contact_personal_info_nonce']) || 
                !wp_verify_nonce($_POST['herohub_contact_personal_info_nonce'], 'herohub_contact_personal_info')) {
                return;
            }
            
            $fields = [
                'first_name', 'last_name', 'email', 'nationality',
                'mobile_phone', 'whatsapp', 'interest', 'source'
            ];

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $value = $_POST[$field];
                    if ($field === 'email') {
                        $value = sanitize_email($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                    update_post_meta($post_id, '_' . $field, $value);
                }
            }
        }
    }
}
