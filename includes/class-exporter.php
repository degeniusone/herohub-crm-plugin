<?php
namespace HeroHub\CRM;

/**
 * Exporter Class
 * Handles data export functionality for the CRM
 */
class Exporter {
    /**
     * Initialize the exporter
     */
    public function __construct() {
        add_action('wp_ajax_herohub_export_data', array($this, 'handle_export'));
    }

    /**
     * Handle export request
     */
    public function handle_export() {
        check_ajax_referer('herohub_dashboard_nonce', 'nonce');

        if (!current_user_can('edit_real_estate')) {
            wp_send_json_error(__('Permission denied', 'herohub-crm'));
        }

        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'month';

        switch ($type) {
            case 'leads':
                $data = $this->get_leads_data($period);
                break;
            case 'deals':
                $data = $this->get_deals_data($period);
                break;
            case 'tasks':
                $data = $this->get_tasks_data($period);
                break;
            case 'performance':
                $data = $this->get_performance_data($period);
                break;
            default:
                wp_send_json_error(__('Invalid export type', 'herohub-crm'));
                return;
        }

        if ($format === 'csv') {
            $this->export_csv($data, $type);
        } else if ($format === 'pdf') {
            $this->export_pdf($data, $type);
        } else {
            wp_send_json_error(__('Invalid export format', 'herohub-crm'));
        }
    }

    /**
     * Get leads data for export
     */
    private function get_leads_data($period) {
        global $wpdb;
        $table = $wpdb->prefix . 'herohub_leads';
        
        $query = $wpdb->prepare(
            "SELECT l.*, u.display_name as agent_name 
            FROM $table l 
            LEFT JOIN {$wpdb->users} u ON l.agent_id = u.ID 
            WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 1 %s)
            ORDER BY l.created_at DESC",
            $period
        );
        
        $leads = $wpdb->get_results($query);
        
        $data = array(
            'headers' => array(
                __('Lead ID', 'herohub-crm'),
                __('Name', 'herohub-crm'),
                __('Email', 'herohub-crm'),
                __('Phone', 'herohub-crm'),
                __('Source', 'herohub-crm'),
                __('Property Type', 'herohub-crm'),
                __('Budget', 'herohub-crm'),
                __('Status', 'herohub-crm'),
                __('Agent', 'herohub-crm'),
                __('Created Date', 'herohub-crm'),
            ),
            'rows' => array(),
        );
        
        foreach ($leads as $lead) {
            $data['rows'][] = array(
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->source,
                $lead->property_type,
                number_format($lead->budget, 2),
                $lead->status,
                $lead->agent_name,
                $lead->created_at,
            );
        }
        
        return $data;
    }

    /**
     * Get deals data for export
     */
    private function get_deals_data($period) {
        global $wpdb;
        $table = $wpdb->prefix . 'herohub_deals';
        
        $query = $wpdb->prepare(
            "SELECT d.*, 
            ua.display_name as agent_name,
            um.display_name as manager_name
            FROM $table d 
            LEFT JOIN {$wpdb->users} ua ON d.agent_id = ua.ID
            LEFT JOIN {$wpdb->users} um ON d.manager_id = um.ID
            WHERE d.creation_date >= DATE_SUB(NOW(), INTERVAL 1 %s)
            ORDER BY d.creation_date DESC",
            $period
        );
        
        $deals = $wpdb->get_results($query);
        
        $data = array(
            'headers' => array(
                __('Deal ID', 'herohub-crm'),
                __('Property ID', 'herohub-crm'),
                __('Amount', 'herohub-crm'),
                __('Commission', 'herohub-crm'),
                __('Status', 'herohub-crm'),
                __('Agent', 'herohub-crm'),
                __('Manager', 'herohub-crm'),
                __('Created Date', 'herohub-crm'),
                __('Completed Date', 'herohub-crm'),
            ),
            'rows' => array(),
        );
        
        foreach ($deals as $deal) {
            $data['rows'][] = array(
                $deal->id,
                $deal->property_id,
                number_format($deal->amount, 2),
                number_format($deal->commission_amount, 2),
                $deal->status,
                $deal->agent_name,
                $deal->manager_name,
                $deal->creation_date,
                $deal->completion_date,
            );
        }
        
        return $data;
    }

    /**
     * Export data as CSV
     */
    private function export_csv($data, $type) {
        $filename = 'herohub-' . $type . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Write headers
        fputcsv($output, $data['headers']);
        
        // Write data rows
        foreach ($data['rows'] as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }

    /**
     * Export data as PDF
     */
    private function export_pdf($data, $type) {
        require_once HEROHUB_CRM_PLUGIN_DIR . 'vendor/autoload.php';

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('HeroHub CRM');
        $pdf->SetTitle('HeroHub ' . ucfirst($type) . ' Report');
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Create the table
        $html = '<table border="1" cellpadding="4">';
        
        // Add headers
        $html .= '<tr>';
        foreach ($data['headers'] as $header) {
            $html .= '<th style="font-weight: bold; background-color: #f5f5f5;">' . $header . '</th>';
        }
        $html .= '</tr>';
        
        // Add data rows
        foreach ($data['rows'] as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        // Print the table
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('herohub-' . $type . '-' . date('Y-m-d') . '.pdf', 'D');
        exit();
    }
}
