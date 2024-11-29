<?php
namespace HeroHub\CRM\CPT;

if (!defined('WPINC')) {
    die;
}

/**
 * Event CPT Handler
 * 
 * Handles everything related to the Event custom post type:
 * - CPT registration
 * - Meta boxes
 * - Fields
 * - Event types and statuses
 */
class Event {
    /**
     * Initialize the event handler
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
     * Register the Event post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Events', 'herohub-crm'),
            'singular_name'      => __('Event', 'herohub-crm'),
            'menu_name'          => __('Events', 'herohub-crm'),
            'add_new'           => __('Add New', 'herohub-crm'),
            'add_new_item'      => __('Add New Event', 'herohub-crm'),
            'edit_item'         => __('Edit Event', 'herohub-crm'),
            'new_item'          => __('New Event', 'herohub-crm'),
            'view_item'         => __('View Event', 'herohub-crm'),
            'search_items'      => __('Search Events', 'herohub-crm'),
            'not_found'         => __('No events found', 'herohub-crm'),
            'not_found_in_trash'=> __('No events found in trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'events'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 7,
            'menu_icon'           => 'dashicons-calendar-alt',
            'supports'            => array('title', 'editor'),
        );

        register_post_type('event', $args);
    }

    /**
     * Register event taxonomies
     */
    public function register_taxonomies() {
        // Event Type Taxonomy
        register_taxonomy('event_type', 'event', array(
            'labels' => array(
                'name'              => __('Event Types', 'herohub-crm'),
                'singular_name'     => __('Event Type', 'herohub-crm'),
                'search_items'      => __('Search Event Types', 'herohub-crm'),
                'all_items'         => __('All Event Types', 'herohub-crm'),
                'edit_item'         => __('Edit Event Type', 'herohub-crm'),
                'update_item'       => __('Update Event Type', 'herohub-crm'),
                'add_new_item'      => __('Add New Event Type', 'herohub-crm'),
                'new_item_name'     => __('New Event Type Name', 'herohub-crm'),
                'menu_name'         => __('Event Types', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'event-type'),
        ));

        // Add default event types
        $this->add_default_event_types();
    }

    /**
     * Add default event types
     */
    private function add_default_event_types() {
        $default_types = array(
            'meeting' => __('Meeting', 'herohub-crm'),
            'viewing' => __('Property Viewing', 'herohub-crm'),
            'call' => __('Phone Call', 'herohub-crm'),
            'follow-up' => __('Follow Up', 'herohub-crm'),
            'other' => __('Other', 'herohub-crm'),
        );

        foreach ($default_types as $slug => $name) {
            if (!term_exists($slug, 'event_type')) {
                wp_insert_term($name, 'event_type', array('slug' => $slug));
            }
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'event_details',
            __('Event Details', 'herohub-crm'),
            array($this, 'render_details_meta_box'),
            'event',
            'normal',
            'high'
        );
    }

    /**
     * Render event details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('event_details_nonce', 'event_details_nonce');

        // Get saved values
        $start_date = get_post_meta($post->ID, '_event_start_date', true);
        $end_date = get_post_meta($post->ID, '_event_end_date', true);
        $location = get_post_meta($post->ID, '_event_location', true);
        $contact_id = get_post_meta($post->ID, '_event_contact_id', true);
        $property_id = get_post_meta($post->ID, '_event_property_id', true);
        $deal_id = get_post_meta($post->ID, '_event_deal_id', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="event_start_date"><?php _e('Start Date & Time', 'herohub-crm'); ?></label>
                    <input type="datetime-local" id="event_start_date" name="event_start_date" value="<?php echo esc_attr($start_date); ?>">
                </div>

                <div class="herohub-field">
                    <label for="event_end_date"><?php _e('End Date & Time', 'herohub-crm'); ?></label>
                    <input type="datetime-local" id="event_end_date" name="event_end_date" value="<?php echo esc_attr($end_date); ?>">
                </div>

                <div class="herohub-field">
                    <label for="event_location"><?php _e('Location', 'herohub-crm'); ?></label>
                    <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($location); ?>">
                </div>

                <div class="herohub-field">
                    <label for="event_contact_id"><?php _e('Contact', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'contact',
                        'selected' => $contact_id,
                        'name' => 'event_contact_id',
                        'show_option_none' => __('Select Contact', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>

                <div class="herohub-field">
                    <label for="event_property_id"><?php _e('Property', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'property',
                        'selected' => $property_id,
                        'name' => 'event_property_id',
                        'show_option_none' => __('Select Property', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>

                <div class="herohub-field">
                    <label for="event_deal_id"><?php _e('Deal', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'deal',
                        'selected' => $deal_id,
                        'name' => 'event_deal_id',
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
     * Save event meta
     */
    public function save_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['event_details_nonce']) || !wp_verify_nonce($_POST['event_details_nonce'], 'event_details_nonce')) {
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
            '_event_start_date',
            '_event_end_date',
            '_event_location',
            '_event_contact_id',
            '_event_property_id',
            '_event_deal_id'
        );

        foreach ($fields as $field) {
            $key = str_replace('_event_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
