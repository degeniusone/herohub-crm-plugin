<?php
namespace HeroHub\CRM;

/**
 * Logger Class
 * Handles error logging and debugging for the CRM
 */
class Logger {
    /**
     * Log levels
     */
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * Log file path
     */
    private $log_file;

    /**
     * Debug mode
     */
    private $debug_mode;

    /**
     * Initialize logger
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/herohub-crm/logs/debug.log';
        $this->debug_mode = defined('WP_DEBUG') && WP_DEBUG;

        // Create log directory if it doesn't exist
        wp_mkdir_p(dirname($this->log_file));

        // Add actions for error handling
        add_action('admin_init', array($this, 'setup_error_handling'));
    }

    /**
     * Setup error handling
     */
    public function setup_error_handling() {
        if ($this->debug_mode) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
            ini_set('error_log', $this->log_file);
        }

        set_error_handler(array($this, 'handle_error'));
        set_exception_handler(array($this, 'handle_exception'));
        register_shutdown_function(array($this, 'handle_shutdown'));
    }

    /**
     * Log a message
     */
    public function log($message, $level = self::INFO, $context = array()) {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }

        $timestamp = current_time('mysql');
        $user_id = get_current_user_id();
        $user_info = $user_id ? " | User ID: $user_id" : '';
        
        $log_entry = sprintf(
            "[%s] [%s]%s | %s",
            $timestamp,
            strtoupper($level),
            $user_info,
            $message
        );

        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context);
        }

        $log_entry .= PHP_EOL;

        error_log($log_entry, 3, $this->log_file);

        // Send email notification for errors if enabled
        if ($level === self::ERROR && $this->should_send_notification()) {
            $this->send_error_notification($message, $context);
        }
    }

    /**
     * Handle PHP errors
     */
    public function handle_error($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error_type = $this->get_error_type($errno);
        $message = sprintf(
            "PHP %s: %s in %s on line %d",
            $error_type,
            $errstr,
            $errfile,
            $errline
        );

        $context = array(
            'error_type' => $error_type,
            'file' => $errfile,
            'line' => $errline,
        );

        $this->log($message, self::ERROR, $context);

        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public function handle_exception($exception) {
        $message = sprintf(
            "Uncaught Exception: %s in %s on line %d",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $context = array(
            'exception_type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        );

        $this->log($message, self::ERROR, $context);

        // Display error page in admin
        if (is_admin()) {
            wp_die(
                esc_html__('An error occurred. Please check the error logs or contact support.', 'herohub-crm'),
                esc_html__('Error', 'herohub-crm'),
                array('response' => 500)
            );
        }
    }

    /**
     * Handle fatal errors
     */
    public function handle_shutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $message = sprintf(
                "Fatal Error: %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );

            $context = array(
                'error_type' => $this->get_error_type($error['type']),
                'file' => $error['file'],
                'line' => $error['line'],
            );

            $this->log($message, self::ERROR, $context);
        }
    }

    /**
     * Get error type string
     */
    private function get_error_type($type) {
        switch($type) {
            case E_ERROR:
                return 'ERROR';
            case E_WARNING:
                return 'WARNING';
            case E_PARSE:
                return 'PARSE ERROR';
            case E_NOTICE:
                return 'NOTICE';
            case E_CORE_ERROR:
                return 'CORE ERROR';
            case E_CORE_WARNING:
                return 'CORE WARNING';
            case E_COMPILE_ERROR:
                return 'COMPILE ERROR';
            case E_COMPILE_WARNING:
                return 'COMPILE WARNING';
            case E_USER_ERROR:
                return 'USER ERROR';
            case E_USER_WARNING:
                return 'USER WARNING';
            case E_USER_NOTICE:
                return 'USER NOTICE';
            case E_STRICT:
                return 'STRICT';
            case E_RECOVERABLE_ERROR:
                return 'RECOVERABLE ERROR';
            case E_DEPRECATED:
                return 'DEPRECATED';
            case E_USER_DEPRECATED:
                return 'USER DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Check if error notification should be sent
     */
    private function should_send_notification() {
        $options = get_option('herohub_settings');
        return isset($options['enable_notifications']) && $options['enable_notifications'];
    }

    /**
     * Send error notification email
     */
    private function send_error_notification($message, $context) {
        $options = get_option('herohub_settings');
        $to = isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email');
        
        $subject = sprintf(
            '[%s] %s',
            get_bloginfo('name'),
            __('HeroHub CRM Error Notification', 'herohub-crm')
        );
        
        $body = sprintf(
            "Error Message: %s\n\nContext: %s\n\nTimestamp: %s",
            $message,
            json_encode($context, JSON_PRETTY_PRINT),
            current_time('mysql')
        );
        
        wp_mail($to, $subject, $body);
    }

    /**
     * Get log file content
     */
    public function get_log_content($lines = 100) {
        if (!file_exists($this->log_file)) {
            return '';
        }

        $file = new \SplFileObject($this->log_file, 'r');
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();

        $start_line = max(0, $total_lines - $lines);
        $log_content = array();

        $file->seek($start_line);
        while (!$file->eof()) {
            $log_content[] = $file->fgets();
        }

        return implode('', $log_content);
    }

    /**
     * Clear log file
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }
}
