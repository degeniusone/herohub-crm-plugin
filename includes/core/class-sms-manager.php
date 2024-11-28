<?php
namespace HeroHub\CRM\Core;

use HeroHub\CRM\Error_Handler;

/**
 * SMS Manager Class
 * 
 * Handles all SMS-related functionality including sending messages,
 * managing templates, and tracking delivery status.
 */
class SMS_Manager {
    use Error_Handler;

    /**
     * SMS gateway provider instance
     */
    private $provider;

    /**
     * Default country code
     */
    private $default_country_code = '+1';

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_provider();
        $this->register_hooks();
    }

    /**
     * Initialize the SMS provider based on settings
     */
    private function init_provider() {
        $provider_name = get_option('herohub_crm_sms_provider', 'twilio');
        
        switch ($provider_name) {
            case 'twilio':
                require_once HEROHUB_CRM_PLUGIN_DIR . 'includes/providers/class-twilio-provider.php';
                $this->provider = new \HeroHub\CRM\Providers\Twilio_Provider();
                break;
            // Add more providers as needed
            default:
                $this->provider = null;
                $this->log_error('Invalid SMS provider specified');
                break;
        }
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        add_action('herohub_crm_after_lead_create', array($this, 'send_lead_welcome_message'), 10, 1);
        add_action('herohub_crm_before_appointment', array($this, 'send_appointment_reminder'), 10, 2);
        add_action('herohub_crm_after_deal_status_change', array($this, 'send_deal_update_notification'), 10, 3);
    }

    /**
     * Send SMS message
     * 
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function send_message($to, $message, $options = array()) {
        if (!$this->provider) {
            return new \WP_Error('no_provider', 'SMS provider not initialized');
        }

        try {
            // Format phone number
            $to = $this->format_phone_number($to);
            
            // Validate phone number
            if (!$this->validate_phone_number($to)) {
                return new \WP_Error('invalid_phone', 'Invalid phone number');
            }

            // Send message through provider
            $result = $this->provider->send($to, $message, $options);

            // Log the message
            $this->log_message($to, $message, $result);

            return $result;
        } catch (\Exception $e) {
            $this->log_error($e->getMessage());
            return new \WP_Error('send_failed', $e->getMessage());
        }
    }

    /**
     * Send bulk SMS messages
     * 
     * @param array $recipients Array of phone numbers
     * @param string $message Message content
     * @param array $options Additional options
     * @return array Array of results
     */
    public function send_bulk_messages($recipients, $message, $options = array()) {
        $results = array();
        
        foreach ($recipients as $recipient) {
            $results[$recipient] = $this->send_message($recipient, $message, $options);
        }
        
        return $results;
    }

    /**
     * Send lead welcome message
     * 
     * @param int $lead_id Lead ID
     */
    public function send_lead_welcome_message($lead_id) {
        $lead = get_post($lead_id);
        $phone = get_post_meta($lead_id, '_phone', true);
        
        if (!$phone) {
            return;
        }

        $template = get_option('herohub_crm_sms_welcome_template', 
            'Hi {name}, thank you for your interest! Your agent {agent} will contact you shortly.');
        
        $agent_id = get_post_meta($lead_id, '_assigned_agent', true);
        $agent = get_userdata($agent_id);
        
        $message = str_replace(
            array('{name}', '{agent}'),
            array($lead->post_title, $agent->display_name),
            $template
        );
        
        $this->send_message($phone, $message);
    }

    /**
     * Send appointment reminder
     * 
     * @param int $appointment_id Appointment ID
     * @param int $lead_id Lead ID
     */
    public function send_appointment_reminder($appointment_id, $lead_id) {
        $appointment = get_post($appointment_id);
        $lead = get_post($lead_id);
        $phone = get_post_meta($lead_id, '_phone', true);
        
        if (!$phone) {
            return;
        }

        $template = get_option('herohub_crm_sms_appointment_template',
            'Reminder: Your appointment is scheduled for {date} at {time}. Reply YES to confirm.');
        
        $date = get_post_meta($appointment_id, '_date', true);
        $time = get_post_meta($appointment_id, '_time', true);
        
        $message = str_replace(
            array('{date}', '{time}'),
            array(
                date('l, F j', strtotime($date)),
                date('g:i A', strtotime($time))
            ),
            $template
        );
        
        $this->send_message($phone, $message);
    }

    /**
     * Send deal update notification
     * 
     * @param int $deal_id Deal ID
     * @param string $old_status Old status
     * @param string $new_status New status
     */
    public function send_deal_update_notification($deal_id, $old_status, $new_status) {
        $deal = get_post($deal_id);
        $lead_id = get_post_meta($deal_id, '_lead_id', true);
        $phone = get_post_meta($lead_id, '_phone', true);
        
        if (!$phone) {
            return;
        }

        $template = get_option('herohub_crm_sms_deal_update_template',
            'Update: Your deal status has changed to {status}. Contact your agent for details.');
        
        $message = str_replace('{status}', $new_status, $template);
        
        $this->send_message($phone, $message);
    }

    /**
     * Format phone number
     * 
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    private function format_phone_number($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add default country code if not present
        if (strlen($phone) === 10) {
            $phone = $this->default_country_code . $phone;
        }
        
        return $phone;
    }

    /**
     * Validate phone number
     * 
     * @param string $phone Phone number
     * @return bool True if valid
     */
    private function validate_phone_number($phone) {
        // Basic validation - should be enhanced based on requirements
        return preg_match('/^\+[1-9]\d{10,14}$/', $phone);
    }

    /**
     * Log SMS message
     * 
     * @param string $to Recipient
     * @param string $message Message
     * @param mixed $result Send result
     */
    private function log_message($to, $message, $result) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'herohub_crm_sms_log',
            array(
                'phone_number' => $to,
                'message' => $message,
                'status' => is_wp_error($result) ? 'failed' : 'sent',
                'error' => is_wp_error($result) ? $result->get_error_message() : '',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }
}
