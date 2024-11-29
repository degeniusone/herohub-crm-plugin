<?php
namespace HeroHub\CRM\CPT;

if (!defined('WPINC')) {
    die;
}

/**
 * Contact CPT Handler
 * 
 * Handles everything related to the Contact custom post type:
 * - CPT registration
 * - Meta boxes
 * - Fields
 * - Contact types and categories
 */
class Contact {
    /**
     * Initialize the contact handler
     */
    public function __construct() {
        // Register CPT
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));

        // Meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta'));
    }

    /**
     * Register the Contact post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Contacts', 'herohub-crm'),
            'singular_name'      => __('Contact', 'herohub-crm'),
            'menu_name'          => __('Contacts', 'herohub-crm'),
            'add_new'           => __('Add New', 'herohub-crm'),
            'add_new_item'      => __('Add New Contact', 'herohub-crm'),
            'edit_item'         => __('Edit Contact', 'herohub-crm'),
            'new_item'          => __('New Contact', 'herohub-crm'),
            'view_item'         => __('View Contact', 'herohub-crm'),
            'search_items'      => __('Search Contacts', 'herohub-crm'),
            'not_found'         => __('No contacts found', 'herohub-crm'),
            'not_found_in_trash'=> __('No contacts found in trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'contacts'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 9,
            'menu_icon'           => 'dashicons-businessperson',
            'supports'            => array('title', 'editor', 'thumbnail'),
        );

        register_post_type('contact', $args);
    }

    /**
     * Register contact taxonomies
     */
    public function register_taxonomies() {
        // Contact Type Taxonomy
        register_taxonomy('contact_type', 'contact', array(
            'labels' => array(
                'name'              => __('Contact Types', 'herohub-crm'),
                'singular_name'     => __('Contact Type', 'herohub-crm'),
                'search_items'      => __('Search Contact Types', 'herohub-crm'),
                'all_items'         => __('All Contact Types', 'herohub-crm'),
                'edit_item'         => __('Edit Contact Type', 'herohub-crm'),
                'update_item'       => __('Update Contact Type', 'herohub-crm'),
                'add_new_item'      => __('Add New Contact Type', 'herohub-crm'),
                'new_item_name'     => __('New Contact Type Name', 'herohub-crm'),
                'menu_name'         => __('Contact Types', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'contact-type'),
        ));

        // Contact Category Taxonomy
        register_taxonomy('contact_category', 'contact', array(
            'labels' => array(
                'name'              => __('Contact Categories', 'herohub-crm'),
                'singular_name'     => __('Contact Category', 'herohub-crm'),
                'search_items'      => __('Search Contact Categories', 'herohub-crm'),
                'all_items'         => __('All Contact Categories', 'herohub-crm'),
                'edit_item'         => __('Edit Contact Category', 'herohub-crm'),
                'update_item'       => __('Update Contact Category', 'herohub-crm'),
                'add_new_item'      => __('Add New Contact Category', 'herohub-crm'),
                'new_item_name'     => __('New Contact Category Name', 'herohub-crm'),
                'menu_name'         => __('Contact Categories', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'contact-category'),
        ));

        // Add default contact types and categories
        $this->add_default_contact_types();
        $this->add_default_contact_categories();
    }

    /**
     * Add default contact types
     */
    private function add_default_contact_types() {
        $default_types = array(
            'lead' => __('Lead', 'herohub-crm'),
            'client' => __('Client', 'herohub-crm'),
            'agent' => __('Agent', 'herohub-crm'),
            'developer' => __('Developer', 'herohub-crm'),
            'vendor' => __('Vendor', 'herohub-crm'),
        );

        foreach ($default_types as $slug => $name) {
            if (!term_exists($slug, 'contact_type')) {
                wp_insert_term($name, 'contact_type', array('slug' => $slug));
            }
        }
    }

    /**
     * Add default contact categories
     */
    private function add_default_contact_categories() {
        $default_categories = array(
            'buyer' => __('Buyer', 'herohub-crm'),
            'seller' => __('Seller', 'herohub-crm'),
            'investor' => __('Investor', 'herohub-crm'),
            'tenant' => __('Tenant', 'herohub-crm'),
            'landlord' => __('Landlord', 'herohub-crm'),
        );

        foreach ($default_categories as $slug => $name) {
            if (!term_exists($slug, 'contact_category')) {
                wp_insert_term($name, 'contact_category', array('slug' => $slug));
            }
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'contact_details',
            __('Contact Details', 'herohub-crm'),
            array($this, 'render_details_meta_box'),
            'contact',
            'normal',
            'high'
        );

        add_meta_box(
            'contact_communication',
            __('Communication Details', 'herohub-crm'),
            array($this, 'render_communication_meta_box'),
            'contact',
            'normal',
            'high'
        );
    }

    /**
     * Render contact details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('contact_details_nonce', 'contact_details_nonce');

        // Get saved values
        $first_name = get_post_meta($post->ID, '_contact_first_name', true);
        $last_name = get_post_meta($post->ID, '_contact_last_name', true);
        $company = get_post_meta($post->ID, '_contact_company', true);
        $position = get_post_meta($post->ID, '_contact_position', true);
        $nationality = get_post_meta($post->ID, '_contact_nationality', true);
        $passport = get_post_meta($post->ID, '_contact_passport', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="contact_first_name"><?php _e('First Name', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_first_name" name="contact_first_name" value="<?php echo esc_attr($first_name); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_last_name"><?php _e('Last Name', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_last_name" name="contact_last_name" value="<?php echo esc_attr($last_name); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_company"><?php _e('Company', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_company" name="contact_company" value="<?php echo esc_attr($company); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_position"><?php _e('Position', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_position" name="contact_position" value="<?php echo esc_attr($position); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_nationality"><?php _e('Nationality', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_nationality" name="contact_nationality" value="<?php echo esc_attr($nationality); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_passport"><?php _e('Passport/ID Number', 'herohub-crm'); ?></label>
                    <input type="text" id="contact_passport" name="contact_passport" value="<?php echo esc_attr($passport); ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render communication details meta box
     */
    public function render_communication_meta_box($post) {
        // Get saved values
        $email = get_post_meta($post->ID, '_contact_email', true);
        $phone = get_post_meta($post->ID, '_contact_phone', true);
        $mobile = get_post_meta($post->ID, '_contact_mobile', true);
        $address = get_post_meta($post->ID, '_contact_address', true);
        $preferred_contact = get_post_meta($post->ID, '_contact_preferred_contact', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="contact_email"><?php _e('Email', 'herohub-crm'); ?></label>
                    <input type="email" id="contact_email" name="contact_email" value="<?php echo esc_attr($email); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_phone"><?php _e('Phone', 'herohub-crm'); ?></label>
                    <input type="tel" id="contact_phone" name="contact_phone" value="<?php echo esc_attr($phone); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_mobile"><?php _e('Mobile', 'herohub-crm'); ?></label>
                    <input type="tel" id="contact_mobile" name="contact_mobile" value="<?php echo esc_attr($mobile); ?>">
                </div>

                <div class="herohub-field">
                    <label for="contact_preferred_contact"><?php _e('Preferred Contact Method', 'herohub-crm'); ?></label>
                    <select id="contact_preferred_contact" name="contact_preferred_contact">
                        <option value=""><?php _e('Select Method', 'herohub-crm'); ?></option>
                        <option value="email" <?php selected($preferred_contact, 'email'); ?>><?php _e('Email', 'herohub-crm'); ?></option>
                        <option value="phone" <?php selected($preferred_contact, 'phone'); ?>><?php _e('Phone', 'herohub-crm'); ?></option>
                        <option value="mobile" <?php selected($preferred_contact, 'mobile'); ?>><?php _e('Mobile', 'herohub-crm'); ?></option>
                        <option value="whatsapp" <?php selected($preferred_contact, 'whatsapp'); ?>><?php _e('WhatsApp', 'herohub-crm'); ?></option>
                    </select>
                </div>

                <div class="herohub-field">
                    <label for="contact_address"><?php _e('Address', 'herohub-crm'); ?></label>
                    <textarea id="contact_address" name="contact_address" rows="3"><?php echo esc_textarea($address); ?></textarea>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save contact meta
     */
    public function save_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['contact_details_nonce']) || !wp_verify_nonce($_POST['contact_details_nonce'], 'contact_details_nonce')) {
            return;
        }

        // If this is an autosave, don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save fields
        $detail_fields = array(
            '_contact_first_name',
            '_contact_last_name',
            '_contact_company',
            '_contact_position',
            '_contact_nationality',
            '_contact_passport'
        );

        $communication_fields = array(
            '_contact_email',
            '_contact_phone',
            '_contact_mobile',
            '_contact_preferred_contact',
            '_contact_address'
        );

        $all_fields = array_merge($detail_fields, $communication_fields);

        foreach ($all_fields as $field) {
            $key = str_replace('_contact_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
