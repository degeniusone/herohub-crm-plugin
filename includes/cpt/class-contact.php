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

        // Meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta'));

        // Initialize Select2 hooks
        $this->init_select2_hooks();

        // Customize publish box
        add_action('post_submitbox_misc_actions', array($this, 'remove_publish_actions'));
        add_action('admin_head-post.php', array($this, 'hide_publishing_actions'));
        add_action('admin_head-post-new.php', array($this, 'hide_publishing_actions'));
        add_filter('gettext', array($this, 'change_publish_button'), 10, 2);

        // Add contact form scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Handle redirect after save
        add_filter('redirect_post_location', array($this, 'redirect_after_save'), 10, 2);

        // Add custom admin notices
        add_action('admin_notices', array($this, 'admin_notices'));

        // Custom update messages
        add_filter('post_updated_messages', array($this, 'custom_updated_messages'));

        // Track old values
        add_action('post_updated', array($this, 'handle_post_update'), 10, 3);
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
            'supports'            => array('title'),
        );

        register_post_type('contact', $args);
    }

    /**
     * Register contact taxonomies
     */
    public function register_taxonomies() {
        // Intentionally empty - taxonomies removed
    }

    /**
     * Add default contact types
     */
    public function add_default_contact_types() {
        // Intentionally empty - default types removed
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'contact_details',
            __('Contact Details', 'herohub-crm'),
            array($this, 'display_contact_details_meta_box'),
            'contact',
            'normal',
            'high'
        );
    }

    /**
     * Display Contact Details meta box
     */
    public function display_contact_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('contact_details_meta_box', 'contact_details_meta_box_nonce');
        
        // Add styles
        ?>
        <style>
            .herohub-meta-box {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .herohub-field {
                flex: 1;
                min-width: calc(50% - 10px);
                margin-bottom: 10px;
            }
            .herohub-field label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #858585;
            }

            /* Reset all form elements */
            .herohub-field input,
            .herohub-field select,
            .select2-container--default .select2-selection--single {
                margin: 0 !important;
                max-width: none !important;
                min-width: 0 !important;
                width: 100% !important;
                height: 40px !important;
                padding: 8px !important;
                border: 1px solid #d3d3d3 !important;
                border-radius: 4px !important;
                background-color: #fff !important;
                color: #858585 !important;
                font-size: 14px !important;
                line-height: 1.5 !important;
                box-sizing: border-box !important;
            }

            /* Specific select styles */
            .herohub-field select {
                cursor: pointer !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23858585%22%2F%3E%3C%2Fsvg%3E') !important;
                background-position: calc(100% - 8px) center !important;
                background-repeat: no-repeat !important;
                background-size: 16px !important;
                padding-right: 30px !important;
            }

            /* Select2 specific styles */
            .select2-container {
                width: 100% !important;
            }

            .select2-container--default .select2-selection--single {
                padding: 0 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                height: 38px !important;
                line-height: 38px !important;
                padding-left: 8px !important;
                padding-right: 30px !important;
                color: #858585 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 38px !important;
                width: 30px !important;
                right: 1px !important;
                top: 1px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow b {
                border-color: #858585 transparent transparent transparent !important;
                border-width: 6px 4px 0 4px !important;
                margin-left: -4px !important;
                margin-top: -3px !important;
            }

            .select2-dropdown {
                border: 1px solid #d3d3d3 !important;
                border-radius: 4px !important;
            }

            .select2-search--dropdown .select2-search__field {
                height: 40px !important;
                padding: 8px !important;
                border: 1px solid #d3d3d3 !important;
                border-radius: 4px !important;
                color: #858585 !important;
            }

            .select2-results__option {
                padding: 8px !important;
                color: #858585 !important;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #f5f5f5 !important;
                color: #858585 !important;
            }
        </style>

        <?php
        // Get saved values
        $first_name = get_post_meta($post->ID, 'contact_first_name', true);
        $last_name = get_post_meta($post->ID, 'contact_last_name', true);
        $email = get_post_meta($post->ID, 'contact_email', true);
        $nationality = get_post_meta($post->ID, 'contact_nationality', true);
        $mobile = get_post_meta($post->ID, 'contact_mobile', true);
        $whatsapp = get_post_meta($post->ID, 'contact_whatsapp', true);
        $interest = get_post_meta($post->ID, 'contact_interest', true);
        $source = get_post_meta($post->ID, 'contact_source', true);

        ?>
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
                <label for="contact_nationality">Nationality:</label>
                <select name="contact_nationality" id="contact_nationality" class="herohub-select2">
                    <option value="">Select Nationality</option>
                    <?php
                    $nationalities = $this->get_nationalities();
                    foreach ($nationalities as $key => $value) {
                        $selected = ($nationality === $key) ? 'selected' : '';
                        echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($value) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="herohub-field">
                <label for="contact_mobile"><?php _e('Mobile Phone', 'herohub-crm'); ?></label>
                <input type="tel" id="contact_mobile" name="contact_mobile" value="<?php echo esc_attr($mobile); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_whatsapp"><?php _e('WhatsApp', 'herohub-crm'); ?></label>
                <input type="tel" id="contact_whatsapp" name="contact_whatsapp" value="<?php echo esc_attr($whatsapp); ?>">
            </div>

            <div class="herohub-field">
                <label for="contact_interest">Interest:</label>
                <select name="contact_interest" id="contact_interest">
                    <option value="">Select Interest</option>
                    <?php
                    $interests = $this->get_interests();
                    foreach ($interests as $key => $value) {
                        $selected = ($interest === $key) ? 'selected' : '';
                        echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($value) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="herohub-field">
                <label for="contact_source">Source:</label>
                <select name="contact_source" id="contact_source">
                    <option value="">Select Source</option>
                    <?php
                    $sources = $this->get_sources();
                    foreach ($sources as $key => $value) {
                        $selected = ($source === $key) ? 'selected' : '';
                        echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($value) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Save contact details
     */
    public function save_meta($post_id) {
        // Security checks
        if (!isset($_POST['contact_details_meta_box_nonce'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['contact_details_meta_box_nonce'], 'contact_details_meta_box')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save the fields
        $fields = array(
            'contact_first_name',
            'contact_last_name',
            'contact_email',
            'contact_nationality',
            'contact_mobile',
            'contact_whatsapp',
            'contact_interest',
            'contact_source'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                    $post_id,
                    $field,
                    sanitize_text_field($_POST[$field])
                );
            }
        }

        // Update the post title to be First Name + Last Name
        $first_name = sanitize_text_field($_POST['contact_first_name']);
        $last_name = sanitize_text_field($_POST['contact_last_name']);
        $full_name = trim($first_name . ' ' . $last_name);
        
        if (!empty($full_name)) {
            remove_action('save_post', array($this, 'save_meta'));
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $full_name
            ));
            add_action('save_post', array($this, 'save_meta'));
        }
    }

    /**
     * Get list of nationalities
     */
    private function get_nationalities() {
        return array(
            'Afghan' => 'Afghan', 'Albanian' => 'Albanian', 'Algerian' => 'Algerian', 
            'American' => 'American', 'Andorran' => 'Andorran', 'Angolan' => 'Angolan', 
            'Antiguan' => 'Antiguan', 'Argentine' => 'Argentine', 'Armenian' => 'Armenian', 
            'Australian' => 'Australian', 'Austrian' => 'Austrian', 'Azerbaijani' => 'Azerbaijani', 
            'Bahamian' => 'Bahamian', 'Bahraini' => 'Bahraini', 'Bangladeshi' => 'Bangladeshi', 
            'Barbadian' => 'Barbadian', 'Belarusian' => 'Belarusian', 'Belgian' => 'Belgian', 
            'Belizean' => 'Belizean', 'Beninese' => 'Beninese', 'Bhutanese' => 'Bhutanese', 
            'Bolivian' => 'Bolivian', 'Bosnian' => 'Bosnian', 'Brazilian' => 'Brazilian', 
            'British' => 'British', 'Bruneian' => 'Bruneian', 'Bulgarian' => 'Bulgarian', 
            'Burkinabe' => 'Burkinabe', 'Burmese' => 'Burmese', 'Burundian' => 'Burundian', 
            'Cambodian' => 'Cambodian', 'Cameroonian' => 'Cameroonian', 'Canadian' => 'Canadian', 
            'Cape Verdean' => 'Cape Verdean', 'Central African' => 'Central African', 
            'Chadian' => 'Chadian', 'Chilean' => 'Chilean', 'Chinese' => 'Chinese', 
            'Colombian' => 'Colombian', 'Comoran' => 'Comoran', 'Congolese' => 'Congolese', 
            'Costa Rican' => 'Costa Rican', 'Croatian' => 'Croatian', 'Cuban' => 'Cuban', 
            'Cypriot' => 'Cypriot', 'Czech' => 'Czech', 'Danish' => 'Danish', 
            'Djibouti' => 'Djibouti', 'Dominican' => 'Dominican', 'Dutch' => 'Dutch', 
            'East Timorese' => 'East Timorese', 'Ecuadorean' => 'Ecuadorean', 'Egyptian' => 'Egyptian', 
            'Emirian' => 'Emirian', 'Equatorial Guinean' => 'Equatorial Guinean', 
            'Eritrean' => 'Eritrean', 'Estonian' => 'Estonian', 'Ethiopian' => 'Ethiopian', 
            'Fijian' => 'Fijian', 'Filipino' => 'Filipino', 'Finnish' => 'Finnish', 
            'French' => 'French', 'Gabonese' => 'Gabonese', 'Gambian' => 'Gambian', 
            'Georgian' => 'Georgian', 'German' => 'German', 'Ghanaian' => 'Ghanaian', 
            'Greek' => 'Greek', 'Grenadian' => 'Grenadian', 'Guatemalan' => 'Guatemalan', 
            'Guinean' => 'Guinean', 'Guyanese' => 'Guyanese', 'Haitian' => 'Haitian', 
            'Honduran' => 'Honduran', 'Hungarian' => 'Hungarian', 'Icelander' => 'Icelander', 
            'Indian' => 'Indian', 'Indonesian' => 'Indonesian', 'Iranian' => 'Iranian', 
            'Iraqi' => 'Iraqi', 'Irish' => 'Irish', 'Israeli' => 'Israeli', 
            'Italian' => 'Italian', 'Ivorian' => 'Ivorian', 'Jamaican' => 'Jamaican', 
            'Japanese' => 'Japanese', 'Jordanian' => 'Jordanian', 'Kazakhstani' => 'Kazakhstani', 
            'Kenyan' => 'Kenyan', 'Kiribati' => 'Kiribati', 'Korean' => 'Korean', 
            'Kuwaiti' => 'Kuwaiti', 'Kyrgyz' => 'Kyrgyz', 'Laotian' => 'Laotian', 
            'Latvian' => 'Latvian', 'Lebanese' => 'Lebanese', 'Liberian' => 'Liberian', 
            'Libyan' => 'Libyan', 'Liechtensteiner' => 'Liechtensteiner', 
            'Lithuanian' => 'Lithuanian', 'Luxembourger' => 'Luxembourger', 
            'Macedonian' => 'Macedonian', 'Malagasy' => 'Malagasy', 'Malawian' => 'Malawian', 
            'Malaysian' => 'Malaysian', 'Maldivian' => 'Maldivian', 'Malian' => 'Malian', 
            'Maltese' => 'Maltese', 'Marshallese' => 'Marshallese', 'Mauritanian' => 'Mauritanian', 
            'Mauritian' => 'Mauritian', 'Mexican' => 'Mexican', 'Micronesian' => 'Micronesian', 
            'Moldovan' => 'Moldovan', 'Monacan' => 'Monacan', 'Mongolian' => 'Mongolian', 
            'Moroccan' => 'Moroccan', 'Mozambican' => 'Mozambican', 'Namibian' => 'Namibian', 
            'Nauruan' => 'Nauruan', 'Nepalese' => 'Nepalese', 'New Zealander' => 'New Zealander', 
            'Nicaraguan' => 'Nicaraguan', 'Nigerian' => 'Nigerian', 'Nigerien' => 'Nigerien', 
            'Norwegian' => 'Norwegian', 'Omani' => 'Omani', 'Pakistani' => 'Pakistani', 
            'Palauan' => 'Palauan', 'Panamanian' => 'Panamanian', 'Papua New Guinean' => 'Papua New Guinean', 
            'Paraguayan' => 'Paraguayan', 'Peruvian' => 'Peruvian', 'Polish' => 'Polish', 
            'Portuguese' => 'Portuguese', 'Qatari' => 'Qatari', 'Romanian' => 'Romanian', 
            'Russian' => 'Russian', 'Rwandan' => 'Rwandan', 'Saint Lucian' => 'Saint Lucian', 
            'Salvadoran' => 'Salvadoran', 'Samoan' => 'Samoan', 'San Marinese' => 'San Marinese', 
            'Sao Tomean' => 'Sao Tomean', 'Saudi' => 'Saudi', 'Senegalese' => 'Senegalese', 
            'Serbian' => 'Serbian', 'Seychellois' => 'Seychellois', 'Sierra Leonean' => 'Sierra Leonean', 
            'Singaporean' => 'Singaporean', 'Slovak' => 'Slovak', 'Slovenian' => 'Slovenian', 
            'Solomon Islander' => 'Solomon Islander', 'Somali' => 'Somali', 
            'South African' => 'South African', 'Spanish' => 'Spanish', 'Sri Lankan' => 'Sri Lankan', 
            'Sudanese' => 'Sudanese', 'Surinamer' => 'Surinamer', 'Swazi' => 'Swazi', 
            'Swedish' => 'Swedish', 'Swiss' => 'Swiss', 'Syrian' => 'Syrian', 
            'Taiwanese' => 'Taiwanese', 'Tajik' => 'Tajik', 'Tanzanian' => 'Tanzanian', 
            'Thai' => 'Thai', 'Togolese' => 'Togolese', 'Tongan' => 'Tongan', 
            'Trinidadian' => 'Trinidadian', 'Tunisian' => 'Tunisian', 'Turkish' => 'Turkish', 
            'Tuvaluan' => 'Tuvaluan', 'Ugandan' => 'Ugandan', 'Ukrainian' => 'Ukrainian', 
            'Uruguayan' => 'Uruguayan', 'Uzbekistani' => 'Uzbekistani', 'Venezuelan' => 'Venezuelan', 
            'Vietnamese' => 'Vietnamese', 'Yemeni' => 'Yemeni', 'Zambian' => 'Zambian', 
            'Zimbabwean' => 'Zimbabwean'
        );
    }

    /**
     * Get list of interests
     */
    private function get_interests() {
        return array(
            'Unknown' => 'Unknown',
            'Buy' => 'Buy',
            'Sell' => 'Sell',
            'Rent' => 'Rent',
            'Invest' => 'Invest',
            'Commercial' => 'Commercial'
        );
    }

    /**
     * Get list of sources
     */
    private function get_sources() {
        return array(
            'DLD List' => 'DLD List',
            'Green List' => 'Green List',
            'Contact Form' => 'Contact Form',
            'Chat Bot' => 'Chat Bot',
            'PropertyFinder Listing' => 'PropertyFinder Listing',
            'Website Listing' => 'Website Listing',
            'Advertisement' => 'Advertisement',
            'Social Media' => 'Social Media',
            'Referrals' => 'Referrals',
            'Manual' => 'Manual',
            'Other' => 'Other'
        );
    }

    /**
     * Initialize Select2 hooks
     */
    public function init_select2_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_select2_scripts'));
    }

    /**
     * Enqueue Select2 scripts and styles
     */
    public function enqueue_select2_scripts() {
        global $post_type, $post;
        if ($post_type !== 'contact') {
            return;
        }

        // Enqueue Select2 CSS
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        
        // Enqueue Select2 JS
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        
        // Get old values from transient if they exist
        $old_values = array();
        if ($post && $post->ID) {
            $old_values = get_transient('contact_old_values_' . $post->ID);
            delete_transient('contact_old_values_' . $post->ID);
        }

        // Localize script with old values
        wp_localize_script('select2-js', 'contactData', array(
            'oldValues' => $old_values ? $old_values : array()
        ));

        // Inline script to initialize Select2
        wp_add_inline_script('select2-js', '
            jQuery(document).ready(function($) {
                $("#contact_nationality").select2({
                    width: "100%",
                    minimumResultsForSearch: 6,
                    allowClear: false,
                    dropdownCssClass: "herohub-select2-dropdown"
                });

                // Update button text
                $("#publish").val(window.location.href.includes("post-new.php") ? "Add Contact" : "Update Contact");

                // Ensure proper styling
                $(".select2-container--default .select2-selection--single").css({
                    "border": "1px solid #8c8f94",
                    "border-radius": "4px",
                    "height": "30px",
                    "padding": "0 4px",
                    "min-width": "200px",
                    "max-width": "400px"
                });

                $(".select2-container--default .select2-selection--single .select2-selection__rendered").css({
                    "line-height": "28px"
                });

                $(".select2-container--default .select2-selection--single .select2-selection__arrow").css({
                    "height": "28px"
                });
            });
        ');
    }

    /**
     * Change publish button text
     */
    public function change_publish_button($translation, $text) {
        global $post;
        if ($post && $post->post_type === 'contact') {
            if ($text === 'Publish') {
                return 'Add Contact';
            }
            if ($text === 'Update') {
                return 'Edit Contact';
            }
        }
        return $translation;
    }

    /**
     * Hide unnecessary publishing actions
     */
    public function hide_publishing_actions() {
        global $post;
        if ($post && $post->post_type === 'contact') {
            echo '<style>
                #misc-publishing-actions .misc-pub-post-status,
                #misc-publishing-actions .misc-pub-visibility,
                #preview-action,
                #save-action {display: none !important;}
                .edit-post-status,
                .edit-visibility,
                .edit-timestamp {display: none !important;}
                #publishing-action {
                    width: 100% !important;
                    float: none !important;
                }
                #publishing-action .button-primary {
                    width: 100% !important;
                    text-align: center !important;
                    padding: 10px !important;
                    height: auto !important;
                    line-height: 1 !important;
                }
            </style>';
        }
    }

    /**
     * Remove publish box actions
     */
    public function remove_publish_actions() {
        global $post;
        if ($post && $post->post_type === 'contact') {
            echo '<style>
                #minor-publishing {display: none !important;}
            </style>';
        }
    }

    /**
     * Enqueue scripts for contact form
     */
    public function enqueue_scripts($hook) {
        global $post;
        
        if (!$post || $post->post_type !== 'contact' || !in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        // Enqueue our custom script
        wp_enqueue_script(
            'contact-form-js',
            plugins_url('/assets/js/contact-form.js', dirname(dirname(__FILE__))),
            array('jquery'),
            '1.0.0',
            true
        );

        // Enqueue Select2
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);

        // Pass data to JavaScript
        wp_localize_script('contact-form-js', 'contactFormData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('contact_form_nonce')
        ));
    }

    /**
     * Handle redirect after saving contact
     */
    public function redirect_after_save($location, $post_id) {
        if (isset($_POST['contact_confirmed']) && get_post_type($post_id) === 'contact') {
            // Remove the "Post published" or "Post updated" message
            $location = remove_query_arg('message', $location);
            
            // Add our custom message
            $is_new = !wp_is_post_revision($post_id) && get_post_status($post_id) === 'publish';
            $message = $is_new ? '98' : '99'; // Custom message codes
            $location = add_query_arg('message', $message, $location);
        }
        return $location;
    }

    /**
     * Add custom admin notices
     */
    public function admin_notices() {
        global $post, $pagenow;

        if ($pagenow === 'post.php' && isset($_GET['message']) && $post && $post->post_type === 'contact') {
            if ($_GET['message'] === '98') {
                echo '<div class="notice notice-success is-dismissible"><p>Contact added successfully!</p></div>';
            } elseif ($_GET['message'] === '99') {
                echo '<div class="notice notice-success is-dismissible"><p>Contact updated successfully!</p></div>';
            }
        }
    }

    /**
     * Custom update messages
     */
    public function custom_updated_messages($messages) {
        global $post;

        $messages['contact'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => 'Contact updated.',
            2 => 'Custom field updated.',
            3 => 'Custom field deleted.',
            4 => 'Contact updated.',
            5 => isset($_GET['revision']) ? sprintf('Contact restored to revision from %s', wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => 'Contact published.',
            7 => 'Contact saved.',
            8 => 'Contact submitted.',
            9 => sprintf('Contact scheduled for: <strong>%1$s</strong>.', date_i18n('M j, Y @ G:i', strtotime($post->post_date))),
            10 => 'Contact draft updated.'
        );

        return $messages;
    }

    /**
     * Handle post update
     */
    public function handle_post_update($post_id, $post_after, $post_before) {
        if ($post_after->post_type !== 'contact') {
            return;
        }

        // Get old values
        $old_values = array();
        $old_values['contact_nationality'] = get_post_meta($post_id, 'contact_nationality', true);
        $old_values['contact_interest'] = get_post_meta($post_id, 'contact_interest', true);
        $old_values['contact_source'] = get_post_meta($post_id, 'contact_source', true);

        // Add to WordPress transient
        set_transient('contact_old_values_' . $post_id, $old_values, 60);
    }
}
