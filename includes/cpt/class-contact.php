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

        // Initialize Select2 hooks
        $this->init_select2_hooks();
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
    }

    /**
     * Enqueue Select2 scripts and styles
     */
    public function enqueue_select2_scripts() {
        // Enqueue Select2 CSS
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        
        // Enqueue Select2 JS
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        
        // Enqueue custom script to initialize Select2
        wp_enqueue_script('herohub-select2-init', plugin_dir_url(__FILE__) . 'js/select2-init.js', array('select2-js'), '1.0.0', true);
    }

    /**
     * Render contact details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('contact_details_nonce', 'contact_details_nonce');

        // Get saved values
        $first_name = get_post_meta($post->ID, '_contact_first_name', true);
        $last_name = get_post_meta($post->ID, '_contact_last_name', true);
        $email = get_post_meta($post->ID, '_contact_email', true);
        $nationality = get_post_meta($post->ID, '_contact_nationality', true);
        $mobile_phone = get_post_meta($post->ID, '_contact_mobile_phone', true);
        $whatsapp = get_post_meta($post->ID, '_contact_whatsapp', true);
        $interest = get_post_meta($post->ID, '_contact_interest', true);
        $source = get_post_meta($post->ID, '_contact_source', true);

        // Full list of nationalities
        $nationalities = [
            'Afghan', 'Algerian', 'American', 'Angolan', 'Argentine', 'Armenian', 
            'Australian', 'Austrian', 'Azerbaijani', 'Bahraini', 'Bangladeshi', 
            'Belgian', 'Beninese', 'Bhutanese', 'Bolivian', 'Bosnian', 'Brazilian', 
            'British', 'Bulgarian', 'Burkinabe', 'Cambodian', 'Cameroonian', 
            'Canadian', 'Chadian', 'Chilean', 'Chinese', 'Colombian', 'Congolese', 
            'Croatian', 'Cuban', 'Czech', 'Danish', 'Dominican', 'Dutch', 
            'Ecuadorian', 'Egyptian', 'Emirati', 'Eritrean', 'Estonian', 
            'Ethiopian', 'Filipino', 'Finnish', 'French', 'Gabonese', 'Gambian', 
            'Georgian', 'German', 'Ghanaian', 'Greek', 'Guatemalan', 'Guinean', 
            'Haitian', 'Honduran', 'Hungarian', 'Icelander', 'Indian', 
            'Indonesian', 'Iranian', 'Iraqi', 'Irish', 'Israeli', 'Italian', 
            'Ivorian', 'Jamaican', 'Japanese', 'Jordanian', 'Kazakhstani', 
            'Kenyan', 'Korean (North)', 'Korean (South)', 'Kosovar', 'Kuwaiti', 
            'Kyrgyzstani', 'Laotian', 'Latvian', 'Lebanese', 'Liberian', 'Libyan', 
            'Lithuanian', 'Luxembourger', 'Malagasy', 'Malaysian', 'Malian', 
            'Maltese', 'Mauritanian', 'Mexican', 'Moldovan', 'Mongolian', 
            'Montenegrin', 'Moroccan', 'Mozambican', 'Myanmar (Burmese)', 
            'Namibian', 'Nepalese', 'New Zealander', 'Nicaraguan', 'Nigerien', 
            'Nigerian', 'Norwegian', 'Omani', 'Pakistani', 'Palestinian', 
            'Panamanian', 'Paraguayan', 'Peruvian', 'Polish', 'Portuguese', 
            'Qatari', 'Romanian', 'Russian', 'Rwandan', 'Salvadoran', 
            'Saudi Arabian', 'Senegalese', 'Serbian', 'Singaporean', 'Slovak', 
            'Slovenian', 'Somali', 'South African', 'Spanish', 'Sri Lankan', 
            'Sudanese', 'Surinamese', 'Swedish', 'Swiss', 'Syrian', 'Tanzanian', 
            'Thai', 'Togolese', 'Trinidadian', 'Tunisian', 'Turkish', 'Turkmen', 
            'Ugandan', 'Ukrainian', 'Uruguayan', 'Uzbekistani', 'Venezuelan', 
            'Vietnamese', 'Yemeni', 'Zambian', 'Zimbabwean'
        ];

        // Interest options
        $interest_options = [
            'Unknown', 'Buy', 'Sell', 'Rent', 'Invest', 'Commercial'
        ];

        // Source options
        $source_options = [
            'DLD List', 'Green List', 'Contact Form', 'Chat Bot', 
            'PropertyFinder Listing', 'Website Listing', 'Advertisement', 
            'Social Media', 'Referrals', 'Manual', 'Other'
        ];

        ?>
        <style>
            .herohub-meta-box {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .herohub-field {
                flex: 1;
                min-width: calc(33.333% - 10px);
                margin-bottom: 10px;
            }
            .herohub-field label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .herohub-field input, 
            .herohub-field select,
            .herohub-select2-container {
                width: 100%;
                padding: 6px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
                height: 36px;
            }
            .select2-container {
                width: 100% !important;
            }
            .herohub-select2-dropdown {
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .herohub-select2-dropdown .select2-search__field {
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 6px;
                box-sizing: border-box;
            }
            .herohub-select2-dropdown .select2-results__option {
                padding: 6px;
            }
        </style>
        <div class="herohub-meta-box">
            <div class="herohub-field">
                <label for="contact_first_name"><?php _e('First Name', 'herohub-crm'); ?></label>
                <input type="text" id="contact_first_name" name="contact_first_name" value="<?php echo esc_attr($first_name); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_last_name"><?php _e('Last Name', 'herohub-crm'); ?></label>
                <input type="text" id="contact_last_name" name="contact_last_name" value="<?php echo esc_attr($last_name); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_email"><?php _e('Email', 'herohub-crm'); ?></label>
                <input type="email" id="contact_email" name="contact_email" value="<?php echo esc_attr($email); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_nationality"><?php _e('Nationality', 'herohub-crm'); ?></label>
                <select id="contact_nationality" name="contact_nationality" class="herohub-select2">
                    <option value=""><?php _e('Select Nationality', 'herohub-crm'); ?></option>
                    <?php foreach ($nationalities as $nat): ?>
                        <option value="<?php echo esc_attr($nat); ?>" <?php selected($nationality, $nat); ?>><?php echo esc_html($nat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="herohub-field">
                <label for="contact_mobile_phone"><?php _e('Mobile Phone', 'herohub-crm'); ?></label>
                <input type="tel" id="contact_mobile_phone" name="contact_mobile_phone" value="<?php echo esc_attr($mobile_phone); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_whatsapp"><?php _e('WhatsApp', 'herohub-crm'); ?></label>
                <input type="tel" id="contact_whatsapp" name="contact_whatsapp" value="<?php echo esc_attr($whatsapp); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_interest"><?php _e('Interest', 'herohub-crm'); ?></label>
                <select id="contact_interest" name="contact_interest">
                    <option value=""><?php _e('Select Interest', 'herohub-crm'); ?></option>
                    <?php foreach ($interest_options as $opt): ?>
                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($interest, $opt); ?>><?php echo esc_html($opt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="herohub-field">
                <label for="contact_source"><?php _e('Source', 'herohub-crm'); ?></label>
                <select id="contact_source" name="contact_source">
                    <option value=""><?php _e('Select Source', 'herohub-crm'); ?></option>
                    <?php foreach ($source_options as $src): ?>
                        <option value="<?php echo esc_attr($src); ?>" <?php selected($source, $src); ?>><?php echo esc_html($src); ?></option>
                    <?php endforeach; ?>
                </select>
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
        $fields = array(
            '_contact_first_name',
            '_contact_last_name',
            '_contact_email',
            '_contact_nationality',
            '_contact_mobile_phone',
            '_contact_whatsapp',
            '_contact_interest',
            '_contact_source'
        );

        foreach ($fields as $field) {
            $key = str_replace('_contact_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }
    }

    /**
     * Initialize hooks for Select2
     */
    public function init_select2_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_select2_scripts'));
    }
}
