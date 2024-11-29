<?php
namespace HeroHub\CRM\Core;

/**
 * Error Handler Trait
 * 
 * Provides common error handling methods for HeroHub CRM components
 */
trait Error_Handler {
    /**
     * Log an error message
     * 
     * @param string $message Error message
     * @param array $context Additional context information
     */
    protected function log_error($message, $context = []) {
        if (function_exists('herohub_log_error')) {
            herohub_log_error($message, $context);
        } else {
            error_log(sprintf(
                '[HeroHub CRM Error] %s | Context: %s', 
                $message, 
                json_encode($context)
            ));
        }
    }

    /**
     * Handle and report an exception
     * 
     * @param \Exception $exception Exception to handle
     * @param bool $fatal Whether this is a fatal error
     */
    protected function handle_exception($exception, $fatal = false) {
        $error_message = sprintf(
            'Exception: %s in %s on line %d', 
            $exception->getMessage(), 
            $exception->getFile(), 
            $exception->getLine()
        );

        $this->log_error($error_message);

        if ($fatal) {
            wp_die($error_message, 'HeroHub CRM Error', [
                'response' => 500,
                'back_link' => true
            ]);
        }
    }

    /**
     * Validate input data
     * 
     * @param mixed $data Data to validate
     * @param string $type Expected data type
     * @return bool
     */
    protected function validate_input($data, $type = 'string') {
        switch ($type) {
            case 'string':
                return is_string($data) && !empty(trim($data));
            case 'email':
                return is_string($data) && filter_var($data, FILTER_VALIDATE_EMAIL);
            case 'phone':
                return is_string($data) && preg_match('/^\+?[1-9]\d{1,14}$/', $data);
            case 'integer':
                return is_numeric($data) && intval($data) == $data;
            case 'array':
                return is_array($data) && !empty($data);
            default:
                return false;
        }
    }

    /**
     * Sanitize input data
     * 
     * @param mixed $data Data to sanitize
     * @param string $type Type of sanitization
     * @return mixed Sanitized data
     */
    protected function sanitize_input($data, $type = 'string') {
        switch ($type) {
            case 'string':
                return sanitize_text_field($data);
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'integer':
                return intval($data);
            case 'array':
                return array_map('sanitize_text_field', $data);
            default:
                return $data;
        }
    }
}
