<?php
namespace HeroHub\CRM\Admin;

class Activity_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_activity', array($this, 'save_metabox'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'activity_details',
            __('Activity Details', 'herohub-crm'),
            array($this, 'render_metabox'),
            'activity',
            'normal',
            'high'
        );
    }

    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('activity_details_nonce', 'activity_details_nonce');

        // Get saved values
        $contact_id = get_post_meta($post->ID, '_activity_contact_id', true);
        $deal_id = get_post_meta($post->ID, '_activity_deal_id', true);
        $date = get_post_meta($post->ID, '_activity_date', true);
        $time = get_post_meta($post->ID, '_activity_time', true);
        $duration = get_post_meta($post->ID, '_activity_duration', true);
        $outcome = get_post_meta($post->ID, '_activity_outcome', true);
        $follow_up_date = get_post_meta($post->ID, '_activity_follow_up_date', true);
        $notes = get_post_meta($post->ID, '_activity_notes', true);
        ?>
        <div class="herohub-metabox">
            <p>
                <label for="activity_contact_id"><?php _e('Contact:', 'herohub-crm'); ?></label>
                <?php
                wp_dropdown_posts(array(
                    'post_type' => 'contact',
                    'selected' => $contact_id,
                    'name' => 'activity_contact_id',
                    'show_option_none' => __('Select Contact', 'herohub-crm'),
                    'class' => 'widefat'
                ));
                ?>
            </p>

            <p>
                <label for="activity_deal_id"><?php _e('Related Deal:', 'herohub-crm'); ?></label>
                <?php
                wp_dropdown_posts(array(
                    'post_type' => 'deal',
                    'selected' => $deal_id,
                    'name' => 'activity_deal_id',
                    'show_option_none' => __('Select Deal', 'herohub-crm'),
                    'class' => 'widefat'
                ));
                ?>
            </p>

            <div class="herohub-activity-timing">
                <p class="herohub-date">
                    <label for="activity_date"><?php _e('Date:', 'herohub-crm'); ?></label>
                    <input type="text" id="activity_date" name="activity_date" 
                           value="<?php echo esc_attr($date); ?>" 
                           class="herohub-date-picker">
                </p>

                <p class="herohub-time">
                    <label for="activity_time"><?php _e('Time:', 'herohub-crm'); ?></label>
                    <input type="time" id="activity_time" name="activity_time" 
                           value="<?php echo esc_attr($time); ?>">
                </p>

                <p class="herohub-duration">
                    <label for="activity_duration"><?php _e('Duration (minutes):', 'herohub-crm'); ?></label>
                    <input type="number" id="activity_duration" name="activity_duration" 
                           value="<?php echo esc_attr($duration); ?>" min="0" step="5">
                </p>
            </div>

            <p>
                <label for="activity_outcome"><?php _e('Outcome:', 'herohub-crm'); ?></label>
                <select id="activity_outcome" name="activity_outcome" class="widefat">
                    <option value=""><?php _e('Select Outcome', 'herohub-crm'); ?></option>
                    <?php
                    $outcomes = array(
                        'completed' => __('Completed', 'herohub-crm'),
                        'no_answer' => __('No Answer', 'herohub-crm'),
                        'left_message' => __('Left Message', 'herohub-crm'),
                        'rescheduled' => __('Rescheduled', 'herohub-crm'),
                        'cancelled' => __('Cancelled', 'herohub-crm'),
                    );
                    foreach ($outcomes as $value => $label) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($value),
                            selected($outcome, $value, false),
                            esc_html($label)
                        );
                    }
                    ?>
                </select>
            </p>

            <p>
                <label for="activity_follow_up_date"><?php _e('Follow-up Date:', 'herohub-crm'); ?></label>
                <input type="text" id="activity_follow_up_date" name="activity_follow_up_date" 
                       value="<?php echo esc_attr($follow_up_date); ?>" 
                       class="widefat herohub-date-picker">
            </p>

            <p>
                <label for="activity_notes"><?php _e('Notes:', 'herohub-crm'); ?></label>
                <textarea id="activity_notes" name="activity_notes" class="widefat" 
                          rows="5"><?php echo esc_textarea($notes); ?></textarea>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id) {
        // Check if nonce is set
        if (!isset($_POST['activity_details_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['activity_details_nonce'], 'activity_details_nonce')) {
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

        // Save activity details
        $fields = array(
            '_activity_contact_id' => 'intval',
            '_activity_deal_id' => 'intval',
            '_activity_date' => 'sanitize_text_field',
            '_activity_time' => 'sanitize_text_field',
            '_activity_duration' => 'intval',
            '_activity_outcome' => 'sanitize_text_field',
            '_activity_follow_up_date' => 'sanitize_text_field',
            '_activity_notes' => 'sanitize_textarea_field',
        );

        foreach ($fields as $meta_key => $sanitize_callback) {
            $field_name = ltrim($meta_key, '_');
            if (isset($_POST[$field_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$field_name]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
}
