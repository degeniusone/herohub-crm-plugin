<?php
namespace HeroHub\CRM\Admin;

class Event_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_event', array($this, 'save_metabox'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'event_details',
            __('Event Details', 'herohub-crm'),
            array($this, 'render_metabox'),
            'event',
            'normal',
            'high'
        );
    }

    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('event_details_nonce', 'event_details_nonce');

        // Get saved values
        $start_date = get_post_meta($post->ID, '_event_start_date', true);
        $end_date = get_post_meta($post->ID, '_event_end_date', true);
        $start_time = get_post_meta($post->ID, '_event_start_time', true);
        $end_time = get_post_meta($post->ID, '_event_end_time', true);
        $location = get_post_meta($post->ID, '_event_location', true);
        $address = get_post_meta($post->ID, '_event_address', true);
        $attendees = get_post_meta($post->ID, '_event_attendees', true);
        $description = get_post_meta($post->ID, '_event_description', true);
        $reminder = get_post_meta($post->ID, '_event_reminder', true);
        ?>
        <div class="herohub-metabox">
            <div class="herohub-event-timing">
                <p class="herohub-start-date">
                    <label for="event_start_date"><?php _e('Start Date:', 'herohub-crm'); ?></label>
                    <input type="text" id="event_start_date" name="event_start_date" 
                           value="<?php echo esc_attr($start_date); ?>" 
                           class="herohub-date-picker">
                </p>

                <p class="herohub-start-time">
                    <label for="event_start_time"><?php _e('Start Time:', 'herohub-crm'); ?></label>
                    <input type="time" id="event_start_time" name="event_start_time" 
                           value="<?php echo esc_attr($start_time); ?>">
                </p>

                <p class="herohub-end-date">
                    <label for="event_end_date"><?php _e('End Date:', 'herohub-crm'); ?></label>
                    <input type="text" id="event_end_date" name="event_end_date" 
                           value="<?php echo esc_attr($end_date); ?>" 
                           class="herohub-date-picker">
                </p>

                <p class="herohub-end-time">
                    <label for="event_end_time"><?php _e('End Time:', 'herohub-crm'); ?></label>
                    <input type="time" id="event_end_time" name="event_end_time" 
                           value="<?php echo esc_attr($end_time); ?>">
                </p>
            </div>

            <p>
                <label for="event_location"><?php _e('Location Name:', 'herohub-crm'); ?></label>
                <input type="text" id="event_location" name="event_location" 
                       value="<?php echo esc_attr($location); ?>" class="widefat">
            </p>

            <p>
                <label for="event_address"><?php _e('Address:', 'herohub-crm'); ?></label>
                <textarea id="event_address" name="event_address" class="widefat" 
                          rows="3"><?php echo esc_textarea($address); ?></textarea>
            </p>

            <p>
                <label for="event_attendees"><?php _e('Attendees:', 'herohub-crm'); ?></label>
                <?php
                $contacts = get_posts(array(
                    'post_type' => 'contact',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));

                if ($contacts) {
                    echo '<select id="event_attendees" name="event_attendees[]" multiple class="widefat">';
                    foreach ($contacts as $contact) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($contact->ID),
                            in_array($contact->ID, (array)$attendees) ? 'selected' : '',
                            esc_html($contact->post_title)
                        );
                    }
                    echo '</select>';
                    echo '<span class="description">' . __('Hold Ctrl/Cmd to select multiple contacts', 'herohub-crm') . '</span>';
                } else {
                    echo '<p>' . __('No contacts found', 'herohub-crm') . '</p>';
                }
                ?>
            </p>

            <p>
                <label for="event_description"><?php _e('Description:', 'herohub-crm'); ?></label>
                <textarea id="event_description" name="event_description" class="widefat" 
                          rows="5"><?php echo esc_textarea($description); ?></textarea>
            </p>

            <p>
                <label for="event_reminder"><?php _e('Reminder:', 'herohub-crm'); ?></label>
                <select id="event_reminder" name="event_reminder" class="widefat">
                    <?php
                    $reminder_options = array(
                        '0' => __('No reminder', 'herohub-crm'),
                        '15' => __('15 minutes before', 'herohub-crm'),
                        '30' => __('30 minutes before', 'herohub-crm'),
                        '60' => __('1 hour before', 'herohub-crm'),
                        '1440' => __('1 day before', 'herohub-crm'),
                        '2880' => __('2 days before', 'herohub-crm'),
                        '10080' => __('1 week before', 'herohub-crm'),
                    );

                    foreach ($reminder_options as $value => $label) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($value),
                            selected($reminder, $value, false),
                            esc_html($label)
                        );
                    }
                    ?>
                </select>
            </p>
        </div>
        <?php
    }

    public function save_metabox($post_id) {
        // Check if nonce is set
        if (!isset($_POST['event_details_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['event_details_nonce'], 'event_details_nonce')) {
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

        // Save event details
        $fields = array(
            '_event_start_date' => 'sanitize_text_field',
            '_event_end_date' => 'sanitize_text_field',
            '_event_start_time' => 'sanitize_text_field',
            '_event_end_time' => 'sanitize_text_field',
            '_event_location' => 'sanitize_text_field',
            '_event_address' => 'sanitize_textarea_field',
            '_event_description' => 'sanitize_textarea_field',
            '_event_reminder' => 'sanitize_text_field',
        );

        foreach ($fields as $meta_key => $sanitize_callback) {
            $field_name = ltrim($meta_key, '_');
            if (isset($_POST[$field_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$field_name]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }

        // Save attendees separately as it's an array
        if (isset($_POST['event_attendees'])) {
            $attendees = array_map('intval', $_POST['event_attendees']);
            update_post_meta($post_id, '_event_attendees', $attendees);
        } else {
            delete_post_meta($post_id, '_event_attendees');
        }
    }
}
