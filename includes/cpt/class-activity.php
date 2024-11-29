<?php
namespace HeroHub\CRM\CPT;

if (!defined('WPINC')) {
    die;
}

/**
 * Activity CPT Handler
 * 
 * Handles everything related to the Activity custom post type:
 * - CPT registration
 * - Meta boxes
 * - Fields
 * - Activity types and logging
 */
class Activity {
    /**
     * Initialize the activity handler
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
     * Register the Activity post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Activities', 'herohub-crm'),
            'singular_name'      => __('Activity', 'herohub-crm'),
            'menu_name'          => __('Activities', 'herohub-crm'),
            'add_new'           => __('Add New', 'herohub-crm'),
            'add_new_item'      => __('Add New Activity', 'herohub-crm'),
            'edit_item'         => __('Edit Activity', 'herohub-crm'),
            'new_item'          => __('New Activity', 'herohub-crm'),
            'view_item'         => __('View Activity', 'herohub-crm'),
            'search_items'      => __('Search Activities', 'herohub-crm'),
            'not_found'         => __('No activities found', 'herohub-crm'),
            'not_found_in_trash'=> __('No activities found in trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'activities'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 8,
            'menu_icon'           => 'dashicons-clock',
            'supports'            => array('title', 'editor'),
        );

        register_post_type('activity', $args);
    }

    /**
     * Register activity taxonomies
     */
    public function register_taxonomies() {
        // Activity Type Taxonomy
        register_taxonomy('activity_type', 'activity', array(
            'labels' => array(
                'name'              => __('Activity Types', 'herohub-crm'),
                'singular_name'     => __('Activity Type', 'herohub-crm'),
                'search_items'      => __('Search Activity Types', 'herohub-crm'),
                'all_items'         => __('All Activity Types', 'herohub-crm'),
                'edit_item'         => __('Edit Activity Type', 'herohub-crm'),
                'update_item'       => __('Update Activity Type', 'herohub-crm'),
                'add_new_item'      => __('Add New Activity Type', 'herohub-crm'),
                'new_item_name'     => __('New Activity Type Name', 'herohub-crm'),
                'menu_name'         => __('Activity Types', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'activity-type'),
        ));

        // Add default activity types
        $this->add_default_activity_types();
    }

    /**
     * Add default activity types
     */
    private function add_default_activity_types() {
        $default_types = array(
            'note' => __('Note', 'herohub-crm'),
            'email' => __('Email', 'herohub-crm'),
            'call' => __('Phone Call', 'herohub-crm'),
            'meeting' => __('Meeting', 'herohub-crm'),
            'task' => __('Task', 'herohub-crm'),
        );

        foreach ($default_types as $slug => $name) {
            if (!term_exists($slug, 'activity_type')) {
                wp_insert_term($name, 'activity_type', array('slug' => $slug));
            }
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'activity_details',
            __('Activity Details', 'herohub-crm'),
            array($this, 'render_details_meta_box'),
            'activity',
            'normal',
            'high'
        );
    }

    /**
     * Render activity details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('activity_details_nonce', 'activity_details_nonce');

        // Get saved values
        $date = get_post_meta($post->ID, '_activity_date', true);
        $contact_id = get_post_meta($post->ID, '_activity_contact_id', true);
        $property_id = get_post_meta($post->ID, '_activity_property_id', true);
        $deal_id = get_post_meta($post->ID, '_activity_deal_id', true);
        $duration = get_post_meta($post->ID, '_activity_duration', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="activity_date"><?php _e('Date & Time', 'herohub-crm'); ?></label>
                    <input type="datetime-local" id="activity_date" name="activity_date" value="<?php echo esc_attr($date); ?>">
                </div>

                <div class="herohub-field">
                    <label for="activity_duration"><?php _e('Duration (minutes)', 'herohub-crm'); ?></label>
                    <input type="number" id="activity_duration" name="activity_duration" value="<?php echo esc_attr($duration); ?>">
                </div>

                <div class="herohub-field">
                    <label for="activity_contact_id"><?php _e('Contact', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'contact',
                        'selected' => $contact_id,
                        'name' => 'activity_contact_id',
                        'show_option_none' => __('Select Contact', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>

                <div class="herohub-field">
                    <label for="activity_property_id"><?php _e('Property', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'property',
                        'selected' => $property_id,
                        'name' => 'activity_property_id',
                        'show_option_none' => __('Select Property', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>

                <div class="herohub-field">
                    <label for="activity_deal_id"><?php _e('Deal', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'deal',
                        'selected' => $deal_id,
                        'name' => 'activity_deal_id',
                        'show_option_none' => __('Select Deal', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save activity meta
     */
    public function save_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['activity_details_nonce']) || !wp_verify_nonce($_POST['activity_details_nonce'], 'activity_details_nonce')) {
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
            '_activity_date',
            '_activity_duration',
            '_activity_contact_id',
            '_activity_property_id',
            '_activity_deal_id'
        );

        foreach ($fields as $field) {
            $key = str_replace('_activity_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
