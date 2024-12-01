<?php
namespace HeroHub\CRM\CPT;

if (!defined('WPINC')) {
    die;
}

/**
 * Property CPT Handler
 * 
 * Handles everything related to the Property custom post type:
 * - CPT registration
 * - Meta boxes
 * - Fields
 * - Property types (Asset, Listing, Request)
 */
class Property {
    /**
     * Initialize the property handler
     */
    public function __construct() {
        // Register post type and taxonomies
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));

        // Meta boxes
        add_action('save_post_property', array($this, 'save_meta'));

        // Initialize Select2 hooks
        $this->init_select2_hooks();

        // Customize publish box
        add_action('post_submitbox_misc_actions', array($this, 'remove_publish_actions'));
        add_action('admin_head-post.php', array($this, 'hide_publishing_actions'));
        add_action('admin_head-post-new.php', array($this, 'hide_publishing_actions'));
        add_filter('gettext', array($this, 'change_publish_button'), 10, 2);
        add_filter('display_post_states', array($this, 'modify_post_states'), 10, 2);

        // Remove taxonomy metaboxes
        add_action('admin_menu', array($this, 'remove_taxonomy_metaboxes'));

        // Add property form scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add property details metabox
        add_action('add_meta_boxes', array($this, 'add_property_details_metabox'));

        // Enqueue media and gallery scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_gallery_scripts'));

        // Add property gallery metabox
        add_action('add_meta_boxes', array($this, 'add_property_gallery_metabox'));

        // Save gallery images
        add_action('save_post_property', array($this, 'save_property_gallery'));

        // Remove default editor
        add_action('admin_init', array($this, 'remove_default_editor'));
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
        global $post_type;
        if ($post_type !== 'property') {
            return;
        }

        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
    }

    /**
     * Change publish button text
     */
    public function change_publish_button($translation, $text) {
        if ($text === 'Publish') {
            return 'Add Property';
        }
        if ($text === 'Update') {
            return 'Update Property';
        }
        return $translation;
    }

    /**
     * Hide publishing actions
     */
    public function hide_publishing_actions() {
        global $post;
        if ($post && $post->post_type === 'property') {
            echo '<style>
                #major-publishing-actions {
                    display: flex;
                    flex-direction: column;
                    padding: 10px;
                }
                .button-row {
                    display: flex;
                    align-items: center;
                    margin-bottom: 10px;
                }
                #save-action {
                    margin-right: 10px;
                }
                #publishing-action {
                    display: flex;
                    align-items: center;
                }
                .submitbox #major-publishing-actions input[type="submit"] {
                    height: 36px;
                    padding: 0 10px;
                    border-radius: 4px;
                }
                #save-post {
                    background: #fff;
                    border: 1px solid #0073aa;
                    color: #0073aa;
                }
                #publish {
                    background: #0073aa;
                    border: 1px solid #0073aa;
                    color: #fff;
                }
                /* Hide all spinners by default */
                .spinner {
                    display: none !important;
                    visibility: hidden !important;
                }
                /* Only show our specific spinner */
                #publishing-action > .spinner.is-active {
                    display: inline-block !important;
                    visibility: visible !important;
                    float: none !important;
                    margin: 0 !important;
                    margin-left: 4px !important;
                }
                #delete-action {
                    display: block;
                    text-align: left;
                    padding-left: 10px;
                }
                #delete-action a {
                    color: #a00;
                    text-decoration: none;
                }
                #delete-action a:hover {
                    color: #dc3232;
                }
                /* Hide unwanted elements */
                #preview-action,
                .edit-post-status,
                #save-action:not(.button-row #save-action),
                #publishing-action + #save-action {
                    display: none !important;
                }
            </style>';

            // Add validation script
            echo '<script>
                jQuery(document).ready(function($) {
                    // Remove all spinners first
                    $(".spinner").remove();
                    
                    // Remove extra buttons and preview
                    $("#preview-action").remove();
                    $(".edit-post-status").remove();
                    $("#publishing-action + #save-action").remove();
                    $("#save-action:not(.button-row #save-action)").remove();
                    
                    // Create button row div
                    var buttonRow = $("<div>").addClass("button-row");
                    
                    // Create save draft button
                    var saveAction = $("<div>").attr("id", "save-action");
                    var saveButton = $("<input>")
                        .attr({
                            "type": "submit",
                            "name": "save",
                            "id": "save-post",
                            "value": "Save Draft",
                            "class": "button"
                        })
                        .css({
                            "height": "36px",
                            "padding": "0 10px",
                            "background": "#fff",
                            "border": "1px solid #0073aa",
                            "color": "#0073aa",
                            "border-radius": "4px",
                            "display": "inline-block"
                        });
                    saveAction.append(saveButton);
                    
                    // Create our own spinner
                    var spinner = $("<span>").addClass("spinner");
                    
                    // Move buttons into the row
                    buttonRow.append(saveAction);
                    buttonRow.append($("#publishing-action").append(spinner));
                    
                    // Move delete action after the button row
                    var deleteAction = $("#delete-action").detach();
                    
                    // Add button row and delete action to major-publishing-actions
                    $("#major-publishing-actions").empty()
                        .append(buttonRow)
                        .append(deleteAction);
                    
                    // Handle save draft click
                    $(document).on("click", "#save-post", function(e) {
                        e.preventDefault();
                        $("#original_post_status").val("draft");
                        $("#post_status").val("draft");
                        $("#hidden_post_status").val("draft");
                        $("#save-post").closest("form").submit();
                    });
                    
                    // Handle spinner visibility
                    $(document).on("click", "#publish, #save-post", function() {
                        $(this).closest("div").find(".spinner").addClass("is-active");
                    });
                    
                    // Force save-action to stay visible
                    $("#save-action").show();
                    
                    $("#publish").click(function(e) {
                        var functionValue = $("#property_function").val();
                        if (!functionValue) {
                            e.preventDefault();
                            alert("Please select a property function before saving.");
                            $("#property_function").focus();
                        }
                    });
                });
            </script>';
        }
    }

    /**
     * Remove publish box actions
     */
    public function remove_publish_actions() {
        global $post_type;
        if ($post_type === 'property') {
            // Get current property function
            $property_function = wp_get_post_terms(get_the_ID(), 'property_function', array('fields' => 'slugs'));
            $current_function = !empty($property_function) ? $property_function[0] : '';

            // Get current values
            $property_owner = get_post_meta(get_the_ID(), '_property_owner', true);
            $assigned_agent = get_post_meta(get_the_ID(), '_assigned_agent', true);

            // Get all property functions
            $functions = get_terms(array(
                'taxonomy' => 'property_function',
                'hide_empty' => false,
            ));

            // Get all contacts
            $contacts = get_posts(array(
                'post_type' => 'contact',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));

            // Get all agents
            $agents = get_posts(array(
                'post_type' => 'agent',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));

            echo '<div class="misc-pub-section property-function">';
            echo '<label for="property_function">' . __('Function', 'herohub-crm') . '</label>';
            echo '<select id="property_function" name="property_function">';
            echo '<option value="">' . __('Asset', 'herohub-crm') . '</option>';
            
            foreach ($functions as $function) {
                echo '<option value="' . esc_attr($function->slug) . '" ' . selected($current_function, $function->slug, false) . '>';
                echo esc_html($function->name);
                echo '</option>';
            }
            
            echo '</select>';
            echo '</div>';

            // Property Owner dropdown
            echo '<div class="misc-pub-section property-owner">';
            echo '<label for="property_owner">' . __('Property Owner', 'herohub-crm') . '</label>';
            echo '<select id="property_owner" name="property_owner">';
            echo '<option value="">' . __('Select Owner', 'herohub-crm') . '</option>';
            foreach ($contacts as $contact) {
                echo sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($contact->ID),
                    selected($property_owner, $contact->ID, false),
                    esc_html($contact->post_title)
                );
            }
            echo '</select>';
            echo '</div>';

            // Assigned Agent dropdown
            echo '<div class="misc-pub-section assigned-agent">';
            echo '<label for="assigned_agent">' . __('Assigned Agent', 'herohub-crm') . '</label>';
            echo '<select id="assigned_agent" name="assigned_agent">';
            echo '<option value="">' . __('Select Agent', 'herohub-crm') . '</option>';
            foreach ($agents as $agent) {
                echo sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($agent->ID),
                    selected($assigned_agent, $agent->ID, false),
                    esc_html($agent->post_title)
                );
            }
            echo '</select>';
            echo '</div>';

            echo '<style>
                #minor-publishing-actions {
                    display: none;
                }
                #delete-action {
                    display: block;
                    text-align: left;
                    padding-left: 0;
                }
                #minor-publishing {
                    padding: 10px;
                }
                .misc-pub-section {
                    padding: 10px 0;
                }
                .misc-pub-section label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                .misc-pub-section.property-function,
                .misc-pub-section.property-owner,
                .misc-pub-section.assigned-agent {
                    display: block !important;
                }
                #submitdiv .postbox-header {
                    display: none;
                }
                .handle-actions {
                    display: none !important;
                }
                #property_function,
                #property_owner,
                #assigned_agent {
                    height: 40px !important;
                    padding: 8px !important;
                    border: 1px solid #d3d3d3 !important;
                    border-radius: 4px !important;
                    background-color: #fff !important;
                    color: #858585 !important;
                    font-size: 14px !important;
                    line-height: 1.5 !important;
                    box-sizing: border-box !important;
                    width: 100% !important;
                    cursor: pointer !important;
                    appearance: none !important;
                    -webkit-appearance: none !important;
                    -moz-appearance: none !important;
                    background-image: url(\'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23858585%22%2F%3E%3C%2Fsvg%3E\') !important;
                    background-position: calc(100% - 8px) center !important;
                    background-repeat: no-repeat !important;
                    background-size: 16px !important;
                    padding-right: 30px !important;
                }
                /* Hide other sections except our custom ones */
                .misc-pub-section:not(.property-function):not(.property-owner):not(.assigned-agent) {
                    display: none !important;
                }
            </style>';
        }
    }

    /**
     * Modify post states
     */
    public function modify_post_states($post_states, $post) {
        if ($post->post_type === 'property') {
            // Keep draft status available
            add_filter('get_post_status', function($status) use ($post) {
                if ($status === 'publish') {
                    return 'draft';
                }
                return $status;
            });
        }
        return $post_states;
    }

    /**
     * Enqueue scripts for property form
     */
    public function enqueue_scripts($hook) {
        global $post_type;
        
        if ($post_type !== 'property' || !in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        // Enqueue Select2
        $this->enqueue_select2_scripts();
    }

    /**
     * Register the Property post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Properties', 'herohub-crm'),
            'singular_name'      => __('Property', 'herohub-crm'),
            'menu_name'          => __('Properties', 'herohub-crm'),
            'add_new'           => __('Add New', 'herohub-crm'),
            'add_new_item'      => __('Add New Property', 'herohub-crm'),
            'edit_item'         => __('Edit Property', 'herohub-crm'),
            'new_item'          => __('New Property', 'herohub-crm'),
            'view_item'         => __('View Property', 'herohub-crm'),
            'search_items'      => __('Search Properties', 'herohub-crm'),
            'not_found'         => __('No properties found', 'herohub-crm'),
            'not_found_in_trash'=> __('No properties found in trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'properties'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-building',
            'supports'            => array('title', 'thumbnail', 'excerpt'),
        );

        register_post_type('property', $args);
    }

    /**
     * Register property taxonomies
     */
    public function register_taxonomies() {
        // Property Type Taxonomy (Asset, Listing, Request)
        register_taxonomy('property_type', 'property', array(
            'labels' => array(
                'name'              => __('Property Types', 'herohub-crm'),
                'singular_name'     => __('Property Type', 'herohub-crm'),
                'search_items'      => __('Search Property Types', 'herohub-crm'),
                'all_items'         => __('All Property Types', 'herohub-crm'),
                'edit_item'         => __('Edit Property Type', 'herohub-crm'),
                'update_item'       => __('Update Property Type', 'herohub-crm'),
                'add_new_item'      => __('Add New Property Type', 'herohub-crm'),
                'new_item_name'     => __('New Property Type Name', 'herohub-crm'),
                'menu_name'         => __('Property Types', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-type'),
        ));

        // Property Function Taxonomy (Asset, Listing, Request)
        register_taxonomy('property_function', 'property', array(
            'labels' => array(
                'name'              => __('Property Functions', 'herohub-crm'),
                'singular_name'     => __('Property Function', 'herohub-crm'),
                'search_items'      => __('Search Property Functions', 'herohub-crm'),
                'all_items'         => __('All Property Functions', 'herohub-crm'),
                'edit_item'         => __('Edit Property Function', 'herohub-crm'),
                'update_item'       => __('Update Property Function', 'herohub-crm'),
                'add_new_item'      => __('Add New Property Function', 'herohub-crm'),
                'new_item_name'     => __('New Property Function Name', 'herohub-crm'),
                'menu_name'         => __('Property Functions', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'show_in_nav_menus' => false,
            'public'            => false,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => false,
        ));

        // Register Amenities Taxonomy
        $labels = array(
            'name'              => _x('Amenities', 'taxonomy general name', 'herohub-crm'),
            'singular_name'     => _x('Amenity', 'taxonomy singular name', 'herohub-crm'),
            'search_items'      => __('Search Amenities', 'herohub-crm'),
            'all_items'         => __('All Amenities', 'herohub-crm'),
            'parent_item'       => __('Parent Amenity', 'herohub-crm'),
            'parent_item_colon' => __('Parent Amenity:', 'herohub-crm'),
            'edit_item'         => __('Edit Amenity', 'herohub-crm'),
            'update_item'       => __('Update Amenity', 'herohub-crm'),
            'add_new_item'      => __('Add New Amenity', 'herohub-crm'),
            'new_item_name'     => __('New Amenity Name', 'herohub-crm'),
            'menu_name'         => __('Amenities', 'herohub-crm'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'publicly_queryable'=> true,
            'rewrite'           => array('slug' => 'property-amenity'),
        );

        register_taxonomy('property_amenity', array('property'), $args);

        // Add default property functions (which will serve as both types and functions)
        $this->add_default_property_functions();

        // Add default amenities
        $this->add_default_amenities();
    }

    /**
     * Add default property functions
     */
    private function add_default_property_functions() {
        $default_functions = array(
            'asset' => __('Asset', 'herohub-crm'),
            'listing' => __('Listing', 'herohub-crm'),
            'request' => __('Request', 'herohub-crm'),
        );

        foreach ($default_functions as $slug => $name) {
            // Add to property_function taxonomy
            if (!term_exists($slug, 'property_function')) {
                wp_insert_term($name, 'property_function', array('slug' => $slug));
            }
            // Add to property_type taxonomy
            if (!term_exists($slug, 'property_type')) {
                wp_insert_term($name, 'property_type', array('slug' => $slug));
            }
        }
    }

    /**
     * Add default property amenities
     */
    public function add_default_amenities() {
        $default_amenities = array(
            array(
                'name' => 'Swimming Pool',
                'icon' => 'fa-swimming-pool'
            ),
            array(
                'name' => 'Gym',
                'icon' => 'fa-dumbbell'
            ),
            array(
                'name' => 'Parking',
                'icon' => 'fa-parking'
            ),
            array(
                'name' => 'Security',
                'icon' => 'fa-shield-alt'
            ),
            array(
                'name' => 'Garden',
                'icon' => 'fa-tree'
            ),
            array(
                'name' => 'Elevator',
                'icon' => 'fa-elevator'
            )
        );

        foreach ($default_amenities as $amenity) {
            $term_exists = term_exists($amenity['name'], 'property_amenity');
            
            if (!$term_exists) {
                $term = wp_insert_term($amenity['name'], 'property_amenity');
                
                if (!is_wp_error($term)) {
                    // Add icon as term meta
                    add_term_meta($term['term_id'], 'amenity_icon', $amenity['icon'], true);
                }
            }
        }
    }

    /**
     * Remove taxonomy metaboxes
     */
    public function remove_taxonomy_metaboxes() {
        remove_meta_box('property_functiondiv', 'property', 'side');
        remove_meta_box('property_typediv', 'property', 'side');
    }

    /**
     * Save property meta
     */
    public function save_meta($post_id) {
        // Check nonce
        if (!isset($_POST['property_details_nonce']) || 
            !wp_verify_nonce($_POST['property_details_nonce'], 'property_details_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Define fields to save
        $fields = array(
            'unit' => 'text',
            'area' => 'text',
            'city' => 'text',
            'price' => 'number',
            'currency' => 'text',
            'property_type' => 'text',
            'property_function' => 'text',
            'bedrooms' => 'number',
            'bathrooms' => 'number',
            'property_size' => 'number',
            'property_status' => 'text',
            'property_description' => 'text',
        );

        // Save each field
        foreach ($fields as $field => $type) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];

                // Sanitize based on type
                switch ($type) {
                    case 'number':
                        $value = floatval($value);
                        break;
                    case 'text':
                        $value = $field === 'property_description' 
                            ? sanitize_textarea_field($value) 
                            : sanitize_text_field($value);
                        break;
                }

                // Update meta
                update_post_meta($post_id, '_' . $field, $value);
            } else {
                // Delete meta if not set
                delete_post_meta($post_id, '_' . $field);
            }
        }

        // Save price
        if (isset($_POST['price'])) {
            $price = sanitize_text_field($_POST['price']);
            update_post_meta($post_id, '_price', $price);
        }

        // Save features
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_POST['feature_' . $i])) {
                $feature = sanitize_text_field($_POST['feature_' . $i]);
                update_post_meta($post_id, '_feature_' . $i, $feature);
            }
        }

        // Save property status
        if (isset($_POST['property_status'])) {
            $property_status = sanitize_text_field($_POST['property_status']);
            update_post_meta($post_id, '_property_status', $property_status);
        }
    }

    /**
     * Add property details metabox
     */
    public function add_property_details_metabox() {
        add_meta_box(
            'property_details',
            __('Property Details', 'herohub-crm'),
            array($this, 'render_property_details_box'),
            'property',
            'advanced',
            'high'
        );
    }

    /**
     * Render property details metabox
     */
    public function render_property_details_box($post) {
        // Retrieve existing meta values
        $bedrooms = get_post_meta($post->ID, '_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_bathrooms', true);
        $property_size = get_post_meta($post->ID, '_property_size', true);
        $property_status = get_post_meta($post->ID, '_property_status', true);
        $property_description = get_post_meta($post->ID, '_property_description', true);
        $property_price = get_post_meta($post->ID, '_price', true);
        $feature_1 = get_post_meta($post->ID, '_feature_1', true);
        $feature_2 = get_post_meta($post->ID, '_feature_2', true);
        $feature_3 = get_post_meta($post->ID, '_feature_3', true);

        wp_nonce_field('property_details_nonce', 'property_details_nonce');
        ?>
        <div class="herohub-property-details-container">
            <!-- Row 1: Unit and Area -->
            <div class="form-row">
                <div class="form-group">
                    <label for="unit"><?php esc_html_e('Unit/Flat', 'herohub-crm'); ?></label>
                    <input type="text" id="unit" name="unit" value="<?php echo esc_attr(get_post_meta($post->ID, '_unit', true)); ?>" class="widefat">
                </div>
                <div class="form-group">
                    <label for="area"><?php esc_html_e('Area', 'herohub-crm'); ?></label>
                    <select id="area" name="area" class="select2-field widefat">
                        <option value=""><?php esc_html_e('Select Area', 'herohub-crm'); ?></option>
                        <?php 
                        $locations = get_terms(array(
                            'taxonomy' => 'location', 
                            'hide_empty' => false
                        ));
                        foreach ($locations as $loc) : ?>
                            <option value="<?php echo esc_attr($loc->term_id); ?>" <?php selected(get_post_meta($post->ID, '_area', true), $loc->term_id); ?>>
                                <?php echo esc_html($loc->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Row 2: City and Property Type -->
            <div class="form-row">
                <div class="form-group">
                    <label for="city"><?php esc_html_e('City', 'herohub-crm'); ?></label>
                    <select id="city" name="city" class="select2-field widefat">
                        <option value=""><?php esc_html_e('Select City', 'herohub-crm'); ?></option>
                        <option value="dubai" <?php selected(get_post_meta($post->ID, '_city', true), 'dubai'); ?>><?php esc_html_e('Dubai', 'herohub-crm'); ?></option>
                        <option value="abu_dhabi" <?php selected(get_post_meta($post->ID, '_city', true), 'abu_dhabi'); ?>><?php esc_html_e('Abu Dhabi', 'herohub-crm'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="property_type"><?php esc_html_e('Property Type', 'herohub-crm'); ?></label>
                    <?php 
                    $property_types = get_terms(array(
                        'taxonomy' => 'property_type', 
                        'hide_empty' => false
                    ));
                    $selected_type = get_post_meta($post->ID, '_property_type', true);
                    ?>
                    <select id="property_type" name="property_type" class="select2-field widefat">
                        <option value=""><?php esc_html_e('Select Property Type', 'herohub-crm'); ?></option>
                        <?php foreach ($property_types as $type) : ?>
                            <option value="<?php echo esc_attr($type->term_id); ?>" <?php selected($selected_type, $type->term_id); ?>>
                                <?php echo esc_html($type->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Row 3: Bedrooms, Bathrooms, Size, Status, Price -->
            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms"><?php esc_html_e('Bedrooms', 'herohub-crm'); ?></label>
                    <input type="number" id="bedrooms" name="bedrooms" value="<?php echo esc_attr($bedrooms); ?>" min="0" class="widefat">
                </div>
                <div class="form-group">
                    <label for="bathrooms"><?php esc_html_e('Bathrooms', 'herohub-crm'); ?></label>
                    <input type="number" id="bathrooms" name="bathrooms" value="<?php echo esc_attr($bathrooms); ?>" min="0" step="0.5" class="widefat">
                </div>
                <div class="form-group">
                    <label for="property_size"><?php esc_html_e('Size (sq.ft.)', 'herohub-crm'); ?></label>
                    <input type="number" id="property_size" name="property_size" value="<?php echo esc_attr($property_size); ?>" min="0" class="widefat">
                </div>
                <div class="form-group">
                    <label for="property_status"><?php esc_html_e('Property Status', 'herohub-crm'); ?></label>
                    <select id="property_status" name="property_status" class="select2-field widefat">
                        <option value=""><?php esc_html_e('Select Status', 'herohub-crm'); ?></option>
                        <option value="for_sale" <?php selected($property_status, 'for_sale'); ?>><?php esc_html_e('For Sale', 'herohub-crm'); ?></option>
                        <option value="for_rent" <?php selected($property_status, 'for_rent'); ?>><?php esc_html_e('For Rent', 'herohub-crm'); ?></option>
                        <option value="off_plan" <?php selected($property_status, 'off_plan'); ?>><?php esc_html_e('Off-Plan', 'herohub-crm'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price"><?php esc_html_e('Price (AED)', 'herohub-crm'); ?></label>
                    <input type="number" id="price" name="price" value="<?php echo esc_attr($property_price); ?>" min="0" class="widefat">
                </div>
            </div>

            <!-- Row 4: Features -->
            <div class="form-row">
                <div class="form-group">
                    <label for="feature_1"><?php esc_html_e('Feature 1', 'herohub-crm'); ?></label>
                    <input type="text" id="feature_1" name="feature_1" value="<?php echo esc_attr($feature_1); ?>" class="widefat">
                </div>
                <div class="form-group">
                    <label for="feature_2"><?php esc_html_e('Feature 2', 'herohub-crm'); ?></label>
                    <input type="text" id="feature_2" name="feature_2" value="<?php echo esc_attr($feature_2); ?>" class="widefat">
                </div>
                <div class="form-group">
                    <label for="feature_3"><?php esc_html_e('Feature 3', 'herohub-crm'); ?></label>
                    <input type="text" id="feature_3" name="feature_3" value="<?php echo esc_attr($feature_3); ?>" class="widefat">
                </div>
            </div>

            <!-- New Row: Property Description -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="property_description"><?php esc_html_e('Property Description', 'herohub-crm'); ?></label>
                    <textarea id="property_description" name="property_description" rows="5" class="widefat"><?php echo esc_textarea($property_description); ?></textarea>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.select2-field').select2();
        });
        </script>
        <?php
    }

    /**
     * Enqueue scripts for gallery functionality
     */
    public function enqueue_gallery_scripts($hook) {
        // Only load on property edit screen
        if (get_post_type() !== 'property' && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        // Enqueue jQuery UI Sortable for drag and drop
        wp_enqueue_script('jquery-ui-sortable');

        // Enqueue custom gallery script
        wp_enqueue_script(
            'herohub-property-gallery', 
            plugin_dir_url(__FILE__) . '../../assets/js/property-gallery.js', 
            array('jquery', 'jquery-ui-sortable'), 
            '1.0.0', 
            true
        );

        // Enqueue gallery styles
        wp_enqueue_style(
            'herohub-property-gallery', 
            plugin_dir_url(__FILE__) . '../../assets/css/property-gallery.css', 
            array(), 
            '1.0.0'
        );
    }

    /**
     * Add property gallery metabox
     */
    public function add_property_gallery_metabox() {
        add_meta_box(
            'property_gallery_metabox',
            __('Property Gallery', 'herohub-crm'),
            array($this, 'render_property_gallery_metabox'),
            'property',
            'normal',
            'high'
        );
    }

    /**
     * Render property gallery metabox
     */
    public function render_property_gallery_metabox($post) {
        // Retrieve existing gallery images
        $gallery_images = get_post_meta($post->ID, '_property_gallery_images', true);
        $gallery_images = $gallery_images ? explode(',', $gallery_images) : array();
        
        wp_nonce_field('property_gallery_nonce', 'property_gallery_nonce');
        ?>
        <div id="herohub-property-gallery-container">
            <div id="herohub-gallery-upload-button-container">
                <button type="button" id="herohub-gallery-upload-button" class="button button-primary">
                    <?php esc_html_e('Add Gallery Images', 'herohub-crm'); ?>
                </button>
            </div>
            
            <ul id="herohub-gallery-images-list" class="herohub-gallery-images">
                <?php 
                foreach ($gallery_images as $image_id) : 
                    $image = wp_get_attachment_image_src($image_id, 'thumbnail');
                    if ($image) :
                ?>
                    <li class="herohub-gallery-image" data-image-id="<?php echo esc_attr($image_id); ?>">
                        <input type="hidden" name="property_gallery_images[]" value="<?php echo esc_attr($image_id); ?>">
                        <img src="<?php echo esc_url($image[0]); ?>" alt="">
                        <a href="#" class="herohub-remove-gallery-image">&times;</a>
                    </li>
                <?php 
                    endif; 
                endforeach; 
                ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Save property gallery images
     */
    public function save_property_gallery($post_id) {
        // Check nonce
        if (!isset($_POST['property_gallery_nonce']) || 
            !wp_verify_nonce($_POST['property_gallery_nonce'], 'property_gallery_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save gallery images
        if (isset($_POST['property_gallery_images'])) {
            $gallery_images = array_map('intval', $_POST['property_gallery_images']);
            $gallery_images_string = implode(',', $gallery_images);
            update_post_meta($post_id, '_property_gallery_images', $gallery_images_string);
        } else {
            delete_post_meta($post_id, '_property_gallery_images');
        }
    }

    /**
     * Remove default editor for property post type
     */
    public function remove_default_editor() {
        remove_post_type_support('property', 'editor');
    }
}
