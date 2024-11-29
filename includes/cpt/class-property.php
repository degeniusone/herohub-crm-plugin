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
        // Register CPT
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));

        // Meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta'));
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

        // Add default property types
        $this->add_default_property_types();
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
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'property_details',
            __('Property Details', 'herohub-crm'),
            array($this, 'render_details_meta_box'),
            'property',
            'normal',
            'high'
        );

        add_meta_box(
            'property_type_details',
            __('Property Type Details', 'herohub-crm'),
            array($this, 'render_type_meta_box'),
            'property',
            'normal',
            'high'
        );
    }

    /**
     * Render property details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('property_details_nonce', 'property_details_nonce');

        // Get saved values
        $price = get_post_meta($post->ID, '_property_price', true);
        $location = get_post_meta($post->ID, '_property_location', true);
        $bedrooms = get_post_meta($post->ID, '_property_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_property_bathrooms', true);
        $area = get_post_meta($post->ID, '_property_area', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <div class="herohub-field">
                    <label for="property_price"><?php _e('Price (AED)', 'herohub-crm'); ?></label>
                    <input type="number" id="property_price" name="property_price" value="<?php echo esc_attr($price); ?>">
                </div>

                <div class="herohub-field">
                    <label for="property_location"><?php _e('Location', 'herohub-crm'); ?></label>
                    <input type="text" id="property_location" name="property_location" value="<?php echo esc_attr($location); ?>">
                </div>

                <div class="herohub-field">
                    <label for="property_bedrooms"><?php _e('Bedrooms', 'herohub-crm'); ?></label>
                    <input type="number" id="property_bedrooms" name="property_bedrooms" value="<?php echo esc_attr($bedrooms); ?>">
                </div>

                <div class="herohub-field">
                    <label for="property_bathrooms"><?php _e('Bathrooms', 'herohub-crm'); ?></label>
                    <input type="number" id="property_bathrooms" name="property_bathrooms" value="<?php echo esc_attr($bathrooms); ?>">
                </div>

                <div class="herohub-field">
                    <label for="property_area"><?php _e('Area (sq ft)', 'herohub-crm'); ?></label>
                    <input type="number" id="property_area" name="property_area" value="<?php echo esc_attr($area); ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render property type meta box
     */
    public function render_type_meta_box($post) {
        wp_nonce_field('property_type_details_nonce', 'property_type_details_nonce');

        // Get saved values
        $property_type = wp_get_post_terms($post->ID, 'property_type', array('fields' => 'slugs'));
        $property_type = !empty($property_type) ? $property_type[0] : '';
        
        // Type-specific fields
        $reference_id = get_post_meta($post->ID, '_property_reference_id', true);
        $commission = get_post_meta($post->ID, '_property_commission', true);
        $source = get_post_meta($post->ID, '_property_source', true);
        $requirements = get_post_meta($post->ID, '_property_requirements', true);

        ?>
        <div class="herohub-meta-box">
            <div class="herohub-row">
                <?php if ($property_type === 'asset' || $property_type === 'listing'): ?>
                <div class="herohub-field">
                    <label for="property_reference_id"><?php _e('Reference ID', 'herohub-crm'); ?></label>
                    <input type="text" id="property_reference_id" name="property_reference_id" value="<?php echo esc_attr($reference_id); ?>">
                </div>
                <?php endif; ?>

                <?php if ($property_type === 'listing'): ?>
                <div class="herohub-field">
                    <label for="property_commission"><?php _e('Commission (%)', 'herohub-crm'); ?></label>
                    <input type="number" step="0.01" id="property_commission" name="property_commission" value="<?php echo esc_attr($commission); ?>">
                </div>
                <?php endif; ?>

                <?php if ($property_type === 'asset'): ?>
                <div class="herohub-field">
                    <label for="property_source"><?php _e('Source', 'herohub-crm'); ?></label>
                    <select id="property_source" name="property_source">
                        <option value=""><?php _e('Select Source', 'herohub-crm'); ?></option>
                        <option value="dld_list" <?php selected($source, 'dld_list'); ?>><?php _e('DLD List', 'herohub-crm'); ?></option>
                        <option value="green_list" <?php selected($source, 'green_list'); ?>><?php _e('Green List', 'herohub-crm'); ?></option>
                        <option value="database" <?php selected($source, 'database'); ?>><?php _e('Database', 'herohub-crm'); ?></option>
                        <option value="other" <?php selected($source, 'other'); ?>><?php _e('Other', 'herohub-crm'); ?></option>
                    </select>
                </div>
                <?php endif; ?>

                <?php if ($property_type === 'request'): ?>
                <div class="herohub-field">
                    <label for="property_requirements"><?php _e('Requirements', 'herohub-crm'); ?></label>
                    <textarea id="property_requirements" name="property_requirements" rows="5"><?php echo esc_textarea($requirements); ?></textarea>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save property meta
     */
    public function save_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['property_details_nonce']) || !wp_verify_nonce($_POST['property_details_nonce'], 'property_details_nonce')) {
            return;
        }

        if (!isset($_POST['property_type_details_nonce']) || !wp_verify_nonce($_POST['property_type_details_nonce'], 'property_type_details_nonce')) {
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

        // Save common fields
        $common_fields = array(
            '_property_price',
            '_property_location',
            '_property_bedrooms',
            '_property_bathrooms',
            '_property_area'
        );

        foreach ($common_fields as $field) {
            $key = str_replace('_property_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }

        // Save type-specific fields
        $type_fields = array(
            '_property_reference_id',
            '_property_commission',
            '_property_source',
            '_property_requirements'
        );

        foreach ($type_fields as $field) {
            $key = str_replace('_property_', '', $field);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
