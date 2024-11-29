<?php
namespace HeroHub\CRM\CPT;

if (!defined('WPINC')) {
    die;
}

/**
 * Deal CPT Handler
 * 
 * Handles everything related to the Deal custom post type:
 * - CPT registration
 * - Meta boxes
 * - Fields
 * - Deal status and stages
 */
class Deal {
    /**
     * Initialize the deal handler
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
     * Register the Deal post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Deals', 'herohub-crm'),
            'singular_name'      => __('Deal', 'herohub-crm'),
            'menu_name'          => __('Deals', 'herohub-crm'),
            'add_new'           => __('Add New', 'herohub-crm'),
            'add_new_item'      => __('Add New Deal', 'herohub-crm'),
            'edit_item'         => __('Edit Deal', 'herohub-crm'),
            'new_item'          => __('New Deal', 'herohub-crm'),
            'view_item'         => __('View Deal', 'herohub-crm'),
            'search_items'      => __('Search Deals', 'herohub-crm'),
            'not_found'         => __('No deals found', 'herohub-crm'),
            'not_found_in_trash'=> __('No deals found in trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'deals'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 6,
            'menu_icon'           => 'dashicons-money-alt',
            'supports'            => array('title', 'editor', 'thumbnail'),
        );

        register_post_type('deal', $args);
    }

    /**
     * Register deal taxonomies
     */
    public function register_taxonomies() {
        // Deal Status Taxonomy
        register_taxonomy('deal_status', 'deal', array(
            'labels' => array(
                'name'              => __('Deal Status', 'herohub-crm'),
                'singular_name'     => __('Deal Status', 'herohub-crm'),
                'search_items'      => __('Search Deal Statuses', 'herohub-crm'),
                'all_items'         => __('All Deal Statuses', 'herohub-crm'),
                'edit_item'         => __('Edit Deal Status', 'herohub-crm'),
                'update_item'       => __('Update Deal Status', 'herohub-crm'),
                'add_new_item'      => __('Add New Deal Status', 'herohub-crm'),
                'new_item_name'     => __('New Deal Status Name', 'herohub-crm'),
                'menu_name'         => __('Deal Status', 'herohub-crm'),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'deal-status'),
        ));

        // Add default deal statuses
        $this->add_default_deal_statuses();
    }

    /**
     * Add default deal statuses
     */
    private function add_default_deal_statuses() {
        $default_statuses = array(
            'new' => __('New', 'herohub-crm'),
            'in-progress' => __('In Progress', 'herohub-crm'),
            'negotiation' => __('Negotiation', 'herohub-crm'),
            'won' => __('Won', 'herohub-crm'),
            'lost' => __('Lost', 'herohub-crm'),
        );

        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'deal_status')) {
                wp_insert_term($name, 'deal_status', array('slug' => $slug));
            }
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'deal_details',
            __('Deal Details', 'herohub-crm'),
            array($this, 'render_details_meta_box'),
            'deal',
            'normal',
            'high'
        );
    }

    /**
     * Render deal details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('deal_details_nonce', 'deal_details_nonce');

        // Get saved values
        $value = get_post_meta($post->ID, '_deal_value', true);
        $commission = get_post_meta($post->ID, '_deal_commission', true);
        $property_id = get_post_meta($post->ID, '_deal_property_id', true);
        $contact_id = get_post_meta($post->ID, '_deal_contact_id', true);
        $expected_close = get_post_meta($post->ID, '_deal_expected_close', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="deal_value"><?php _e('Deal Value (AED)', 'herohub-crm'); ?></label>
                    <input type="number" id="deal_value" name="deal_value" value="<?php echo esc_attr($value); ?>">
                </div>

                <div class="herohub-field">
                    <label for="deal_commission"><?php _e('Commission (%)', 'herohub-crm'); ?></label>
                    <input type="number" step="0.01" id="deal_commission" name="deal_commission" value="<?php echo esc_attr($commission); ?>">
                </div>

                <div class="herohub-field">
                    <label for="deal_property_id"><?php _e('Property', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'property',
                        'selected' => $property_id,
                        'name' => 'deal_property_id',
                        'show_option_none' => __('Select Property', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>

                <div class="herohub-field">
                    <label for="deal_contact_id"><?php _e('Contact', 'herohub-crm'); ?></label>
                    <?php
                    wp_dropdown_posts(array(
                        'post_type' => 'contact',
                        'selected' => $contact_id,
                        'name' => 'deal_contact_id',
                        'show_option_none' => __('Select Contact', 'herohub-crm'),
                        'option_none_value' => '',
                    ));
                    ?>
                </div>

                <div class="herohub-field">
                    <label for="deal_expected_close"><?php _e('Expected Close Date', 'herohub-crm'); ?></label>
                    <input type="date" id="deal_expected_close" name="deal_expected_close" value="<?php echo esc_attr($expected_close); ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save deal meta
     */
    public function save_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['deal_details_nonce']) || !wp_verify_nonce($_POST['deal_details_nonce'], 'deal_details_nonce')) {
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
            '_deal_value',
            '_deal_commission',
            '_deal_property_id',
            '_deal_contact_id',
            '_deal_expected_close'
        );

        foreach ($fields as $field) {
            $key = str_replace('_deal_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
