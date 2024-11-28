<?php
namespace HeroHub\CRM\Core;

use HeroHub\CRM\Error_Handler;

/**
 * Lead Scoring Manager Class
 * 
 * Handles lead scoring calculations and management.
 */
class Lead_Scoring {
    use Error_Handler;

    /**
     * Scoring factors and their weights
     */
    private $scoring_factors = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_scoring_factors();
        $this->register_hooks();
    }

    /**
     * Initialize scoring factors and their weights
     */
    private function init_scoring_factors() {
        $default_factors = array(
            'budget' => array(
                'weight' => 25,
                'ranges' => array(
                    array('min' => 1000000, 'score' => 100),
                    array('min' => 500000, 'score' => 80),
                    array('min' => 250000, 'score' => 60),
                    array('min' => 100000, 'score' => 40),
                    array('min' => 0, 'score' => 20)
                )
            ),
            'interaction_frequency' => array(
                'weight' => 20,
                'points' => array(
                    'email_open' => 2,
                    'email_click' => 3,
                    'website_visit' => 4,
                    'form_submission' => 5,
                    'phone_call' => 8,
                    'meeting' => 10
                )
            ),
            'property_views' => array(
                'weight' => 15,
                'points_per_view' => 2,
                'max_points' => 100
            ),
            'timeline' => array(
                'weight' => 20,
                'ranges' => array(
                    array('max_months' => 1, 'score' => 100),
                    array('max_months' => 3, 'score' => 80),
                    array('max_months' => 6, 'score' => 60),
                    array('max_months' => 12, 'score' => 40),
                    array('max_months' => 999, 'score' => 20)
                )
            ),
            'engagement_score' => array(
                'weight' => 20,
                'factors' => array(
                    'email_response_rate' => 0.4,
                    'appointment_attendance' => 0.3,
                    'document_downloads' => 0.3
                )
            )
        );

        $this->scoring_factors = apply_filters('herohub_crm_scoring_factors', $default_factors);
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        add_action('herohub_crm_after_lead_activity', array($this, 'update_lead_score'), 10, 2);
        add_action('herohub_crm_daily_cron', array($this, 'recalculate_all_scores'));
        add_filter('herohub_crm_leads_columns', array($this, 'add_score_column'));
        add_filter('herohub_crm_leads_sortable_columns', array($this, 'add_score_sortable_column'));
    }

    /**
     * Calculate lead score
     * 
     * @param int $lead_id Lead ID
     * @return int Score (0-100)
     */
    public function calculate_score($lead_id) {
        $total_score = 0;
        $total_weight = 0;

        // Budget Score
        $budget_score = $this->calculate_budget_score($lead_id);
        $total_score += $budget_score * $this->scoring_factors['budget']['weight'];
        $total_weight += $this->scoring_factors['budget']['weight'];

        // Interaction Score
        $interaction_score = $this->calculate_interaction_score($lead_id);
        $total_score += $interaction_score * $this->scoring_factors['interaction_frequency']['weight'];
        $total_weight += $this->scoring_factors['interaction_frequency']['weight'];

        // Property Views Score
        $views_score = $this->calculate_property_views_score($lead_id);
        $total_score += $views_score * $this->scoring_factors['property_views']['weight'];
        $total_weight += $this->scoring_factors['property_views']['weight'];

        // Timeline Score
        $timeline_score = $this->calculate_timeline_score($lead_id);
        $total_score += $timeline_score * $this->scoring_factors['timeline']['weight'];
        $total_weight += $this->scoring_factors['timeline']['weight'];

        // Engagement Score
        $engagement_score = $this->calculate_engagement_score($lead_id);
        $total_score += $engagement_score * $this->scoring_factors['engagement_score']['weight'];
        $total_weight += $this->scoring_factors['engagement_score']['weight'];

        // Calculate final weighted score
        $final_score = $total_weight > 0 ? round($total_score / $total_weight) : 0;

        // Store the score
        update_post_meta($lead_id, '_lead_score', $final_score);
        update_post_meta($lead_id, '_lead_score_updated', current_time('mysql'));

        // Log score update
        $this->log_score_update($lead_id, $final_score);

        return $final_score;
    }

    /**
     * Calculate budget score
     * 
     * @param int $lead_id Lead ID
     * @return int Score (0-100)
     */
    private function calculate_budget_score($lead_id) {
        $budget = (float) get_post_meta($lead_id, '_budget', true);
        
        foreach ($this->scoring_factors['budget']['ranges'] as $range) {
            if ($budget >= $range['min']) {
                return $range['score'];
            }
        }
        
        return 0;
    }

    /**
     * Calculate interaction frequency score
     * 
     * @param int $lead_id Lead ID
     * @return int Score (0-100)
     */
    private function calculate_interaction_score($lead_id) {
        global $wpdb;
        
        $points = 0;
        $max_points = 100;
        
        // Get interactions from the last 30 days
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_type, COUNT(*) as count 
            FROM {$wpdb->prefix}herohub_activities 
            WHERE lead_id = %d 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY activity_type",
            $lead_id
        ));

        foreach ($activities as $activity) {
            if (isset($this->scoring_factors['interaction_frequency']['points'][$activity->activity_type])) {
                $points += $activity->count * $this->scoring_factors['interaction_frequency']['points'][$activity->activity_type];
            }
        }

        return min($points, $max_points);
    }

    /**
     * Calculate property views score
     * 
     * @param int $lead_id Lead ID
     * @return int Score (0-100)
     */
    private function calculate_property_views_score($lead_id) {
        $views = (int) get_post_meta($lead_id, '_property_views_count', true);
        $points = $views * $this->scoring_factors['property_views']['points_per_view'];
        
        return min($points, $this->scoring_factors['property_views']['max_points']);
    }

    /**
     * Calculate timeline score
     * 
     * @param int $lead_id Lead ID
     * @return int Score (0-100)
     */
    private function calculate_timeline_score($lead_id) {
        $timeline_months = (int) get_post_meta($lead_id, '_timeline_months', true);
        
        foreach ($this->scoring_factors['timeline']['ranges'] as $range) {
            if ($timeline_months <= $range['max_months']) {
                return $range['score'];
            }
        }
        
        return 0;
    }

    /**
     * Calculate engagement score
     * 
     * @param int $lead_id Lead ID
     * @return int Score (0-100)
     */
    private function calculate_engagement_score($lead_id) {
        $factors = $this->scoring_factors['engagement_score']['factors'];
        $total_score = 0;

        // Email response rate
        $email_stats = $this->get_email_stats($lead_id);
        $response_rate = $email_stats['total_sent'] > 0 
            ? ($email_stats['total_responses'] / $email_stats['total_sent']) * 100 
            : 0;
        $total_score += $response_rate * $factors['email_response_rate'];

        // Appointment attendance
        $attendance_rate = $this->get_appointment_attendance_rate($lead_id);
        $total_score += $attendance_rate * $factors['appointment_attendance'];

        // Document downloads
        $download_score = $this->get_document_download_score($lead_id);
        $total_score += $download_score * $factors['document_downloads'];

        return round($total_score);
    }

    /**
     * Get email statistics
     * 
     * @param int $lead_id Lead ID
     * @return array Email stats
     */
    private function get_email_stats($lead_id) {
        global $wpdb;
        
        $stats = array(
            'total_sent' => 0,
            'total_responses' => 0
        );
        
        // Get email activities
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_type, COUNT(*) as count 
            FROM {$wpdb->prefix}herohub_activities 
            WHERE lead_id = %d 
            AND activity_type IN ('email_sent', 'email_received')
            GROUP BY activity_type",
            $lead_id
        ));

        foreach ($results as $result) {
            if ($result->activity_type === 'email_sent') {
                $stats['total_sent'] = $result->count;
            } elseif ($result->activity_type === 'email_received') {
                $stats['total_responses'] = $result->count;
            }
        }

        return $stats;
    }

    /**
     * Get appointment attendance rate
     * 
     * @param int $lead_id Lead ID
     * @return float Attendance rate (0-100)
     */
    private function get_appointment_attendance_rate($lead_id) {
        global $wpdb;
        
        $results = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended
            FROM {$wpdb->prefix}herohub_appointments
            WHERE lead_id = %d
            AND scheduled_date < NOW()",
            $lead_id
        ));

        if (!$results->total_appointments) {
            return 0;
        }

        return ($results->attended / $results->total_appointments) * 100;
    }

    /**
     * Get document download score
     * 
     * @param int $lead_id Lead ID
     * @return int Download score (0-100)
     */
    private function get_document_download_score($lead_id) {
        global $wpdb;
        
        $downloads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
            FROM {$wpdb->prefix}herohub_activities 
            WHERE lead_id = %d 
            AND activity_type = 'document_download'",
            $lead_id
        ));

        return min($downloads * 10, 100);
    }

    /**
     * Update lead score after activity
     * 
     * @param int $lead_id Lead ID
     * @param string $activity_type Activity type
     */
    public function update_lead_score($lead_id, $activity_type) {
        $this->calculate_score($lead_id);
    }

    /**
     * Recalculate scores for all leads
     */
    public function recalculate_all_scores() {
        $leads = get_posts(array(
            'post_type' => 'herohub_lead',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));

        foreach ($leads as $lead_id) {
            $this->calculate_score($lead_id);
        }
    }

    /**
     * Add score column to leads list
     * 
     * @param array $columns Columns
     * @return array Modified columns
     */
    public function add_score_column($columns) {
        $columns['lead_score'] = __('Lead Score', 'herohub-crm');
        return $columns;
    }

    /**
     * Make score column sortable
     * 
     * @param array $columns Sortable columns
     * @return array Modified columns
     */
    public function add_score_sortable_column($columns) {
        $columns['lead_score'] = 'lead_score';
        return $columns;
    }

    /**
     * Log score update
     * 
     * @param int $lead_id Lead ID
     * @param int $score New score
     */
    private function log_score_update($lead_id, $score) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'herohub_crm_score_log',
            array(
                'lead_id' => $lead_id,
                'score' => $score,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );
    }
}
