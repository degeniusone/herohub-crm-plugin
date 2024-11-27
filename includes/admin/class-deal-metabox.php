<?php
namespace HeroHub\CRM\Admin;

class Deal_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_deal', array($this, 'save_metabox'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'deal_details',
            __('Deal Details', 'herohub-crm'),
            array($this, 'render_metabox'),
            'deal',
            'normal',
            'high'
        );
    }

    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('deal_details_nonce', 'deal_details_nonce');

        // Get saved values
        $contact_id = get_post_meta($post->ID, '_deal_contact_id', true);
        $property_id = get_post_meta($post->ID, '_deal_property_id', true);
        $value = get_post_meta($post->ID, '_deal_value', true);
        $close_date = get_post_meta($post->ID, '_deal_close_date', true);
        $probability = get_post_meta($post->ID, '_deal_probability', true);
        $notes = get_post_meta($post->ID, '_deal_notes', true);
        ?>
        <div class="herohub-metabox">
            <p>
                <label for="deal_contact_id"><?php _e('Contact:', 'herohub-crm'); ?></label>
                <?php
                wp_dropdown_posts(array(
                    'post_type' => 'contact',
                    'selected' => $contact_id,
                    'name' => 'deal_contact_id',
                    'show_option_none' => __('Select Contact', 'herohub-crm'),
                    'class' => 'widefat'
                ));
                ?>
            </p>

            <p>
                <label for="deal_property_id"><?php _e('Property:', 'herohub-crm'); ?></label>
                <?php
                wp_dropdown_posts(array(
                    'post_type' => 'property',
                    'selected' => $property_id,
                    'name' => 'deal_property_id',
                    'show_option_none' => __('Select Property', 'herohub-crm'),
                    'class' => 'widefat'
                ));
                ?>
            </p>

            <p>
                <label for="deal_value"><?php _e('Deal Value:', 'herohub-crm'); ?></label>
                <input type="text" id="deal_value" name="deal_value" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="widefat herohub-price-field">
            </p>

            <p>
                <label for="deal_close_date"><?php _e('Expected Close Date:', 'herohub-crm'); ?></label>
                <input type="text" id="deal_close_date" name="deal_close_date" 
                       value="<?php echo esc_attr($close_date); ?>" 
                       class="widefat herohub-date-picker">
            </p>

            <p>
                <label for="deal_probability"><?php _e('Probability (%):', 'herohub-crm'); ?></label>
                <select id="deal_probability" name="deal_probability" class="widefat">
                    <?php
                    $probabilities = array(10, 25, 50, 75, 90, 100);
                    foreach ($probabilities as $prob) {
                        printf(
                            '<option value="%d" %s>%d%%</option>',
                            $prob,
                            selected($probability, $prob, false),
                            $prob
                        );
                    }
                    ?>
                </select>
            </p>

            <p>
                <label for="deal_notes"><?php _e('Notes:', 'herohub-crm'); ?></label>
                <textarea id="deal_notes" name="deal_notes" class="widefat" 
                          rows="5"><?php echo esc_textarea($notes); ?></textarea>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id) {
        // Check if nonce is set
        if (!isset($_POST['deal_details_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['deal_details_nonce'], 'deal_details_nonce')) {
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

        // Save deal details
        $fields = array(
            '_deal_contact_id' => 'intval',
            '_deal_property_id' => 'intval',
            '_deal_value' => function($value) {
                return preg_replace('/[^0-9.]/', '', $value);
            },
            '_deal_close_date' => 'sanitize_text_field',
            '_deal_probability' => 'intval',
            '_deal_notes' => 'sanitize_textarea_field',
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
