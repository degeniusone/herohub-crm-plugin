<?php
namespace HeroHub\CRM;

/**
 * Error Handler Trait
 * Provides error handling functionality for plugin classes
 */
trait Error_Handler {
    /**
     * Store errors
     */
    protected $errors = array();

    /**
     * Add an error
     */
    protected function add_error($code, $message, $data = array()) {
        $this->errors[] = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );

        // Log the error
        if (class_exists('HeroHub\CRM\Logger')) {
            $logger = new Logger();
            $logger->log($message, Logger::ERROR, array(
                'code' => $code,
                'data' => $data,
            ));
        }
    }

    /**
     * Get all errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Check if there are any errors
     */
    public function has_errors() {
        return !empty($this->errors);
    }

    /**
     * Clear all errors
     */
    protected function clear_errors() {
        $this->errors = array();
    }

    /**
     * Handle database errors
     */
    protected function handle_db_error($wpdb, $operation) {
        if ($wpdb->last_error) {
            $this->add_error(
                'db_error',
                sprintf(
                    __('Database error during %s: %s', 'herohub-crm'),
                    $operation,
                    $wpdb->last_error
                ),
                array(
                    'operation' => $operation,
                    'last_query' => $wpdb->last_query,
                )
            );
            return true;
        }
        return false;
    }

    /**
     * Handle AJAX response with errors
     */
    protected function send_error_response($message = '', $data = array()) {
        $response = array(
            'success' => false,
            'errors' => $this->get_errors(),
        );

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        wp_send_json($response);
    }

    /**
     * Handle AJAX success response
     */
    protected function send_success_response($message = '', $data = array()) {
        $response = array(
            'success' => true,
        );

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        wp_send_json($response);
    }

    /**
     * Validate required fields
     */
    protected function validate_required_fields($data, $required_fields) {
        $missing_fields = array();

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            $this->add_error(
                'missing_fields',
                __('Required fields are missing', 'herohub-crm'),
                array('fields' => $missing_fields)
            );
            return false;
        }

        return true;
    }

    /**
     * Validate email field
     */
    protected function validate_email($email) {
        if (!is_email($email)) {
            $this->add_error(
                'invalid_email',
                __('Invalid email address', 'herohub-crm'),
                array('email' => $email)
            );
            return false;
        }
        return true;
    }

    /**
     * Validate numeric field
     */
    protected function validate_numeric($value, $field_name) {
        if (!is_numeric($value)) {
            $this->add_error(
                'invalid_numeric',
                sprintf(__('Invalid numeric value for %s', 'herohub-crm'), $field_name),
                array('field' => $field_name, 'value' => $value)
            );
            return false;
        }
        return true;
    }

    /**
     * Validate date field
     */
    protected function validate_date($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->add_error(
                'invalid_date',
                __('Invalid date format', 'herohub-crm'),
                array('date' => $date, 'expected_format' => $format)
            );
            return false;
        }
        return true;
    }

    /**
     * Sanitize and validate form data
     */
    protected function sanitize_form_data($data, $fields) {
        $sanitized = array();
        
        foreach ($fields as $field => $rules) {
            if (!isset($data[$field]) && !empty($rules['required'])) {
                $this->add_error(
                    'missing_field',
                    sprintf(__('Field %s is required', 'herohub-crm'), $field)
                );
                continue;
            }

            $value = isset($data[$field]) ? $data[$field] : '';

            // Apply sanitization
            switch ($rules['type']) {
                case 'text':
                    $value = sanitize_text_field($value);
                    break;
                case 'email':
                    $value = sanitize_email($value);
                    if (!empty($value) && !is_email($value)) {
                        $this->add_error(
                            'invalid_email',
                            sprintf(__('Invalid email for field %s', 'herohub-crm'), $field)
                        );
                    }
                    break;
                case 'number':
                    if (!empty($value) && !is_numeric($value)) {
                        $this->add_error(
                            'invalid_number',
                            sprintf(__('Invalid number for field %s', 'herohub-crm'), $field)
                        );
                    }
                    break;
                case 'url':
                    $value = esc_url_raw($value);
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
            }

            // Apply validation
            if (isset($rules['validation'])) {
                foreach ($rules['validation'] as $validation => $param) {
                    switch ($validation) {
                        case 'min_length':
                            if (strlen($value) < $param) {
                                $this->add_error(
                                    'min_length',
                                    sprintf(__('Field %s must be at least %d characters', 'herohub-crm'), $field, $param)
                                );
                            }
                            break;
                        case 'max_length':
                            if (strlen($value) > $param) {
                                $this->add_error(
                                    'max_length',
                                    sprintf(__('Field %s must not exceed %d characters', 'herohub-crm'), $field, $param)
                                );
                            }
                            break;
                        case 'pattern':
                            if (!preg_match($param, $value)) {
                                $this->add_error(
                                    'pattern_mismatch',
                                    sprintf(__('Field %s has an invalid format', 'herohub-crm'), $field)
                                );
                            }
                            break;
                    }
                }
            }

            $sanitized[$field] = $value;
        }

        return $sanitized;
    }
}
