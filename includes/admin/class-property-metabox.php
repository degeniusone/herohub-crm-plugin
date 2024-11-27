<?php
namespace HeroHub\CRM\Admin;

class Property_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_property', array($this, 'save_metabox'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'property_details',
            __('Property Details', 'herohub-crm'),
            array($this, 'render_metabox'),
            'property',
            'normal',
            'high'
        );
    }

    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('property_details_nonce', 'property_details_nonce');

        // Get saved values
        $address = get_post_meta($post->ID, '_property_address', true);
        $city = get_post_meta($post->ID, '_property_city', true);
        $state = get_post_meta($post->ID, '_property_state', true);
        $zip = get_post_meta($post->ID, '_property_zip', true);
        $price = get_post_meta($post->ID, '_property_price', true);
        $bedrooms = get_post_meta($post->ID, '_property_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_property_bathrooms', true);
        $square_feet = get_post_meta($post->ID, '_property_square_feet', true);
        $lot_size = get_post_meta($post->ID, '_property_lot_size', true);
        $year_built = get_post_meta($post->ID, '_property_year_built', true);
        $features = get_post_meta($post->ID, '_property_features', true);
        ?>
        <div class="herohub-metabox">
            <p>
                <label for="property_address"><?php _e('Address:', 'herohub-crm'); ?></label>
                <input type="text" id="property_address" name="property_address" 
                       value="<?php echo esc_attr($address); ?>" class="widefat">
            </p>

            <div class="herohub-address-group">
                <p class="herohub-city">
                    <label for="property_city"><?php _e('City:', 'herohub-crm'); ?></label>
                    <input type="text" id="property_city" name="property_city" 
                           value="<?php echo esc_attr($city); ?>">
                </p>

                <p class="herohub-state">
                    <label for="property_state"><?php _e('State:', 'herohub-crm'); ?></label>
                    <input type="text" id="property_state" name="property_state" 
                           value="<?php echo esc_attr($state); ?>">
                </p>

                <p class="herohub-zip">
                    <label for="property_zip"><?php _e('ZIP Code:', 'herohub-crm'); ?></label>
                    <input type="text" id="property_zip" name="property_zip" 
                           value="<?php echo esc_attr($zip); ?>">
                </p>
            </div>

            <p>
                <label for="property_price"><?php _e('Price:', 'herohub-crm'); ?></label>
                <input type="text" id="property_price" name="property_price" 
                       value="<?php echo esc_attr($price); ?>" 
                       class="widefat herohub-price-field">
            </p>

            <div class="herohub-property-specs">
                <p>
                    <label for="property_bedrooms"><?php _e('Bedrooms:', 'herohub-crm'); ?></label>
                    <input type="number" id="property_bedrooms" name="property_bedrooms" 
                           value="<?php echo esc_attr($bedrooms); ?>" min="0" step="1">
                </p>

                <p>
                    <label for="property_bathrooms"><?php _e('Bathrooms:', 'herohub-crm'); ?></label>
                    <input type="number" id="property_bathrooms" name="property_bathrooms" 
                           value="<?php echo esc_attr($bathrooms); ?>" min="0" step="0.5">
                </p>

                <p>
                    <label for="property_square_feet"><?php _e('Square Feet:', 'herohub-crm'); ?></label>
                    <input type="number" id="property_square_feet" name="property_square_feet" 
                           value="<?php echo esc_attr($square_feet); ?>" min="0">
                </p>
            </div>

            <p>
                <label for="property_lot_size"><?php _e('Lot Size:', 'herohub-crm'); ?></label>
                <input type="text" id="property_lot_size" name="property_lot_size" 
                       value="<?php echo esc_attr($lot_size); ?>" class="widefat">
            </p>

            <p>
                <label for="property_year_built"><?php _e('Year Built:', 'herohub-crm'); ?></label>
                <input type="number" id="property_year_built" name="property_year_built" 
                       value="<?php echo esc_attr($year_built); ?>" min="1800" 
                       max="<?php echo date('Y'); ?>" class="widefat">
            </p>

            <p>
                <label for="property_features"><?php _e('Features:', 'herohub-crm'); ?></label>
                <textarea id="property_features" name="property_features" class="widefat" 
                          rows="5"><?php echo esc_textarea($features); ?></textarea>
                <span class="description"><?php _e('Enter one feature per line', 'herohub-crm'); ?></span>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id) {
        // Check if nonce is set
        if (!isset($_POST['property_details_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['property_details_nonce'], 'property_details_nonce')) {
            return;
        }

        // If this is an autosave, don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save property details
        $fields = array(
            '_property_address' => 'sanitize_text_field',
            '_property_city' => 'sanitize_text_field',
            '_property_state' => 'sanitize_text_field',
            '_property_zip' => 'sanitize_text_field',
            '_property_price' => function($value) {
                return preg_replace('/[^0-9.]/', '', $value);
            },
            '_property_bedrooms' => 'intval',
            '_property_bathrooms' => 'floatval',
            '_property_square_feet' => 'intval',
            '_property_lot_size' => 'sanitize_text_field',
            '_property_year_built' => 'intval',
            '_property_features' => 'sanitize_textarea_field',
        );

        foreach ($fields as $meta_key => $sanitize_callback) {
            $field_name = ltrim($meta_key, '_');
            if (isset($_POST[$field_name])) {
                $value = is_callable($sanitize_callback) 
                    ? $sanitize_callback($_POST[$field_name])
                    : call_user_func($sanitize_callback, $_POST[$field_name]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
}
