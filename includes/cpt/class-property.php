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
            'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
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

        // Add default property types
        $this->add_default_property_types();

        // Add default property functions
        $this->add_default_property_functions();
    }

    /**
     * Add default property types
     */
    private function add_default_property_types() {
        $default_types = array(
            'asset' => __('Asset', 'herohub-crm'),
            'listing' => __('Listing', 'herohub-crm'),
            'request' => __('Request', 'herohub-crm'),
        );

        foreach ($default_types as $slug => $name) {
            if (!term_exists($slug, 'property_type')) {
                wp_insert_term($name, 'property_type', array('slug' => $slug));
            }
        }
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
            if (!term_exists($slug, 'property_function')) {
                wp_insert_term($name, 'property_function', array('slug' => $slug));
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
        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save featured image
        if (isset($_POST['_thumbnail_id'])) {
            $thumbnail_id = intval($_POST['_thumbnail_id']);
            if ($thumbnail_id === -1) {
                delete_post_meta($post_id, '_thumbnail_id');
            } else {
                update_post_meta($post_id, '_thumbnail_id', $thumbnail_id);
            }
        }

        // Save property owner
        if (isset($_POST['property_owner'])) {
            update_post_meta($post_id, '_property_owner', sanitize_text_field($_POST['property_owner']));
        }

        // Save assigned agent
        if (isset($_POST['assigned_agent'])) {
            update_post_meta($post_id, '_assigned_agent', sanitize_text_field($_POST['assigned_agent']));
        }

        // Save property function
        if (isset($_POST['property_function'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['property_function']), 'property_function');
        }
    }
}
