<?php
namespace HeroHub\CRM\Providers;

/**
 * Twilio SMS Provider Class
 * 
 * Handles integration with Twilio SMS service
 */
class Twilio_Provider {
    /**
     * Twilio client instance
     */
    private $client;

    /**
     * Twilio phone number
     */
    private $from_number;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize Twilio client
     */
    private function init() {
        // Load Twilio SDK via Composer autoload
        if (!class_exists('Twilio\Rest\Client')) {
            require_once HEROHUB_CRM_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $account_sid = get_option('herohub_crm_twilio_account_sid');
        $auth_token = get_option('herohub_crm_twilio_auth_token');
        $this->from_number = get_option('herohub_crm_twilio_phone_number');

        if ($account_sid && $auth_token) {
            try {
                $this->client = new \Twilio\Rest\Client($account_sid, $auth_token);
            } catch (\Exception $e) {
                // Log error
                error_log('Twilio initialization failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send SMS message via Twilio
     * 
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function send($to, $message, $options = array()) {
        if (!$this->client) {
            return new \WP_Error('no_client', 'Twilio client not initialized');
        }

        try {
            $message_params = array(
                'from' => $this->from_number,
                'body' => $message
            );

            // Add optional parameters
            if (!empty($options['media_url'])) {
                $message_params['mediaUrl'] = $options['media_url'];
            }

            // Send message
            $result = $this->client->messages->create($to, $message_params);

            // Return true if message SID is present
            return !empty($result->sid);

        } catch (\Exception $e) {
            return new \WP_Error('send_failed', $e->getMessage());
        }
    }

    /**
     * Get message status
     * 
     * @param string $message_sid Message SID
     * @return string|WP_Error Message status or error
     */
    public function get_message_status($message_sid) {
        if (!$this->client) {
            return new \WP_Error('no_client', 'Twilio client not initialized');
        }

        try {
            $message = $this->client->messages($message_sid)->fetch();
            return $message->status;
        } catch (\Exception $e) {
            return new \WP_Error('status_failed', $e->getMessage());
        }
    }

    /**
     * Validate webhook request
     * 
     * @param string $signature X-Twilio-Signature header
     * @param string $url Webhook URL
     * @param array $params Request parameters
     * @return bool True if valid
     */
    public function validate_webhook($signature, $url, $params) {
        if (!class_exists('Twilio\Security\RequestValidator')) {
            return false;
        }

        $auth_token = get_option('herohub_crm_twilio_auth_token');
        $validator = new \Twilio\Security\RequestValidator($auth_token);

        return $validator->validate($signature, $url, $params);
    }
}
