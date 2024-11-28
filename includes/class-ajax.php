<?php
namespace HeroHub\CRM;

/**
 * AJAX Handler Class
 * Handles all AJAX requests for the dashboard
 */
class Ajax {
    /**
     * Initialize AJAX hooks
     */
    public function __construct() {
        // Dashboard data actions
        add_action('wp_ajax_herohub_get_dashboard_data', array($this, 'get_dashboard_data'));
        add_action('wp_ajax_herohub_get_performance_metrics', array($this, 'get_performance_metrics'));
        
        // Task management
        add_action('wp_ajax_herohub_add_task', array($this, 'add_task'));
        add_action('wp_ajax_herohub_update_task', array($this, 'update_task'));
        add_action('wp_ajax_herohub_delete_task', array($this, 'delete_task'));
        
        // Lead management
        add_action('wp_ajax_herohub_add_lead', array($this, 'add_lead'));
        add_action('wp_ajax_herohub_update_lead', array($this, 'update_lead'));
        add_action('wp_ajax_herohub_delete_lead', array($this, 'delete_lead'));
        
        // Deal management
        add_action('wp_ajax_herohub_add_deal', array($this, 'add_deal'));
        add_action('wp_ajax_herohub_update_deal', array($this, 'update_deal'));
        add_action('wp_ajax_herohub_delete_deal', array($this, 'delete_deal'));
    }

    /**
     * Get dashboard data based on user role
     */
    public function get_dashboard_data() {
        check_ajax_referer('herohub_dashboard_nonce', 'nonce');

        $user = wp_get_current_user();
        $data = array();

        if (in_array('administrator', $user->roles)) {
            $data = $this->get_admin_dashboard_data();
        } elseif (in_array('real_estate_manager', $user->roles)) {
            $data = $this->get_manager_dashboard_data();
        } elseif (in_array('real_estate_agent', $user->roles)) {
            $data = $this->get_agent_dashboard_data();
        }

        wp_send_json_success($data);
    }

    /**
     * Get performance metrics
     */
    public function get_performance_metrics() {
        check_ajax_referer('herohub_dashboard_nonce', 'nonce');
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'month';
        
        global $wpdb;
        
        // Get deals data
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $deals_query = $wpdb->prepare(
            "SELECT COUNT(*) as total_deals, SUM(amount) as total_amount, SUM(commission_amount) as total_commission 
            FROM $deals_table 
            WHERE agent_id = %d 
            AND completion_date >= DATE_SUB(NOW(), INTERVAL 1 $period)",
            $user_id
        );
        
        $deals_data = $wpdb->get_row($deals_query);
        
        // Get leads data
        $leads_table = $wpdb->prefix . 'herohub_leads';
        $leads_query = $wpdb->prepare(
            "SELECT COUNT(*) as total_leads, 
            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads
            FROM $leads_table 
            WHERE agent_id = %d 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)",
            $user_id
        );
        
        $leads_data = $wpdb->get_row($leads_query);
        
        $metrics = array(
            'deals' => array(
                'total' => (int) $deals_data->total_deals,
                'amount' => (float) $deals_data->total_amount,
                'commission' => (float) $deals_data->total_commission,
            ),
            'leads' => array(
                'total' => (int) $leads_data->total_leads,
                'converted' => (int) $leads_data->converted_leads,
                'conversion_rate' => $leads_data->total_leads > 0 ? 
                    round(($leads_data->converted_leads / $leads_data->total_leads) * 100, 2) : 0,
            ),
        );
        
        wp_send_json_success($metrics);
    }

    /**
     * Add new task
     */
    public function add_task() {
        check_ajax_referer('herohub_dashboard_nonce', 'nonce');
        
        if (!current_user_can('edit_real_estate')) {
            wp_send_json_error('Permission denied');
        }
        
        $task_data = array(
            'agent_id' => isset($_POST['agent_id']) ? intval($_POST['agent_id']) : get_current_user_id(),
            'type' => sanitize_text_field($_POST['type']),
            'description' => sanitize_textarea_field($_POST['description']),
            'priority' => sanitize_text_field($_POST['priority']),
            'due_date' => sanitize_text_field($_POST['due_date']),
        );
        
        global $wpdb;
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'herohub_tasks',
            $task_data,
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            wp_send_json_success(array(
                'task_id' => $wpdb->insert_id,
                'message' => __('Task added successfully', 'herohub-crm'),
            ));
        } else {
            wp_send_json_error(__('Failed to add task', 'herohub-crm'));
        }
    }

    /**
     * Update existing task
     */
    public function update_task() {
        check_ajax_referer('herohub_dashboard_nonce', 'nonce');
        
        $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
        if (!$task_id) {
            wp_send_json_error(__('Invalid task ID', 'herohub-crm'));
        }
        
        global $wpdb;
        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}herohub_tasks WHERE id = %d",
            $task_id
        ));
        
        if (!$task) {
            wp_send_json_error(__('Task not found', 'herohub-crm'));
        }
        
        // Check permissions
        if (!current_user_can('manage_real_estate') && get_current_user_id() != $task->agent_id) {
            wp_send_json_error(__('Permission denied', 'herohub-crm'));
        }
        
        $update_data = array();
        $update_format = array();
        
        if (isset($_POST['type'])) {
            $update_data['type'] = sanitize_text_field($_POST['type']);
            $update_format[] = '%s';
        }
        
        if (isset($_POST['description'])) {
            $update_data['description'] = sanitize_textarea_field($_POST['description']);
            $update_format[] = '%s';
        }
        
        if (isset($_POST['priority'])) {
            $update_data['priority'] = sanitize_text_field($_POST['priority']);
            $update_format[] = '%s';
        }
        
        if (isset($_POST['due_date'])) {
            $update_data['due_date'] = sanitize_text_field($_POST['due_date']);
            $update_format[] = '%s';
        }
        
        if (isset($_POST['completed'])) {
            $update_data['completed'] = (bool) $_POST['completed'];
            $update_data['completed_at'] = $update_data['completed'] ? current_time('mysql') : null;
            $update_format[] = '%d';
            $update_format[] = '%s';
        }
        
        $updated = $wpdb->update(
            $wpdb->prefix . 'herohub_tasks',
            $update_data,
            array('id' => $task_id),
            $update_format,
            array('%d')
        );
        
        if ($updated !== false) {
            wp_send_json_success(array(
                'message' => __('Task updated successfully', 'herohub-crm'),
            ));
        } else {
            wp_send_json_error(__('Failed to update task', 'herohub-crm'));
        }
    }

    // Similar methods for leads and deals management...
    // Implementation follows the same pattern as tasks
}
