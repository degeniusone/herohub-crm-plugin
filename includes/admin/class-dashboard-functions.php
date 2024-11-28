<?php
namespace HeroHub\CRM\Admin;

/**
 * Dashboard helper functions
 */
class Dashboard_Functions {

    /**
     * Get total number of managers
     */
    public static function get_total_managers() {
        $args = array(
            'role' => 'real_estate_manager',
            'count_total' => true,
        );
        $users = new \WP_User_Query($args);
        return $users->get_total();
    }

    /**
     * Get total number of agents
     */
    public static function get_total_agents() {
        $args = array(
            'role' => 'real_estate_agent',
            'count_total' => true,
        );
        $users = new \WP_User_Query($args);
        return $users->get_total();
    }

    /**
     * Get total properties
     */
    public static function get_total_properties() {
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get total deals
     */
    public static function get_total_deals() {
        $args = array(
            'post_type' => 'deal',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get recent activities
     */
    public static function get_recent_activities($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'herohub_activities';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY time DESC LIMIT %d",
            $limit
        ));
    }

    /**
     * Format currency
     */
    public static function format_currency($amount) {
        return '$' . number_format($amount, 2);
    }

    /**
     * Get total revenue
     */
    public static function get_total_revenue() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'herohub_deals';
        
        return (float) $wpdb->get_var(
            "SELECT SUM(amount) FROM {$table_name} WHERE status = 'completed'"
        );
    }

    /**
     * Get average deal size
     */
    public static function get_average_deal_size() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'herohub_deals';
        
        return (float) $wpdb->get_var(
            "SELECT AVG(amount) FROM {$table_name} WHERE status = 'completed'"
        );
    }

    /**
     * Get conversion rate
     */
    public static function get_conversion_rate() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $leads_table = $wpdb->prefix . 'herohub_leads';
        
        $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$leads_table}");
        $converted_leads = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'completed'"
        );
        
        if ($total_leads == 0) return 0;
        return round(($converted_leads / $total_leads) * 100, 2);
    }

    /**
     * Get top performers
     */
    public static function get_top_performers($limit = 5) {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $users_table = $wpdb->prefix . 'users';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                u.display_name as name,
                COUNT(*) as deals_closed,
                SUM(d.amount) as revenue
            FROM {$deals_table} d
            JOIN {$users_table} u ON d.agent_id = u.ID
            WHERE d.status = 'completed'
            GROUP BY d.agent_id
            ORDER BY revenue DESC
            LIMIT %d",
            $limit
        ));
    }

    /**
     * Get manager's active agents
     */
    public static function get_manager_active_agents() {
        $manager_id = get_current_user_id();
        $args = array(
            'role' => 'real_estate_agent',
            'meta_key' => 'manager_id',
            'meta_value' => $manager_id,
            'count_total' => true,
        );
        $users = new \WP_User_Query($args);
        return $users->get_total();
    }

    /**
     * Get manager's total listings
     */
    public static function get_manager_total_listings() {
        $manager_id = get_current_user_id();
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'manager_id',
                    'value' => $manager_id,
                ),
            ),
        );
        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get manager's pending deals
     */
    public static function get_manager_pending_deals() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $manager_id = get_current_user_id();
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$deals_table} WHERE manager_id = %d AND status = 'pending'",
            $manager_id
        ));
    }

    /**
     * Get manager's monthly revenue
     */
    public static function get_manager_monthly_revenue() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $manager_id = get_current_user_id();
        $first_day = date('Y-m-01');
        $last_day = date('Y-m-t');
        
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$deals_table} 
            WHERE manager_id = %d 
            AND status = 'completed'
            AND completion_date BETWEEN %s AND %s",
            $manager_id,
            $first_day,
            $last_day
        ));
    }

    /**
     * Get agent's active listings
     */
    public static function get_agent_active_listings() {
        $agent_id = get_current_user_id();
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $agent_id,
        );
        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get agent's deals closed
     */
    public static function get_agent_deals_closed() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $agent_id = get_current_user_id();
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$deals_table} WHERE agent_id = %d AND status = 'completed'",
            $agent_id
        ));
    }

    /**
     * Get agent's commission
     */
    public static function get_agent_commission() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $agent_id = get_current_user_id();
        
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(commission_amount) FROM {$deals_table} 
            WHERE agent_id = %d AND status = 'completed'",
            $agent_id
        ));
    }

    /**
     * Get agent's conversion rate
     */
    public static function get_agent_conversion_rate() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $leads_table = $wpdb->prefix . 'herohub_leads';
        $agent_id = get_current_user_id();
        
        $total_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$leads_table} WHERE agent_id = %d",
            $agent_id
        ));
        
        $converted_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$deals_table} 
            WHERE agent_id = %d AND status = 'completed'",
            $agent_id
        ));
        
        if ($total_leads == 0) return 0;
        return round(($converted_leads / $total_leads) * 100, 2);
    }

    /**
     * Get agent's tasks
     */
    public static function get_agent_tasks() {
        global $wpdb;
        $tasks_table = $wpdb->prefix . 'herohub_tasks';
        $agent_id = get_current_user_id();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$tasks_table} 
            WHERE agent_id = %d AND completed = 0
            ORDER BY priority DESC, due_date ASC",
            $agent_id
        ));
    }

    /**
     * Get agent's leads
     */
    public static function get_agent_leads() {
        global $wpdb;
        $leads_table = $wpdb->prefix . 'herohub_leads';
        $agent_id = get_current_user_id();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$leads_table} 
            WHERE agent_id = %d AND status = 'new'
            ORDER BY created_at DESC",
            $agent_id
        ));
    }

    /**
     * Get team performance data for charts
     */
    public static function get_team_performance_data() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $manager_id = get_current_user_id();
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE_FORMAT(completion_date, '%Y-%m') as month,
                COUNT(*) as deals,
                SUM(amount) as revenue
            FROM {$deals_table}
            WHERE manager_id = %d
            AND status = 'completed'
            AND completion_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month ASC",
            $manager_id
        ));

        $labels = array();
        $deals_data = array();
        $revenue_data = array();

        foreach ($results as $row) {
            $labels[] = date('M Y', strtotime($row->month));
            $deals_data[] = $row->deals;
            $revenue_data[] = $row->revenue;
        }

        return array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => 'Deals Closed',
                    'data' => $deals_data,
                    'borderColor' => '#4e73df',
                    'fill' => false
                ),
                array(
                    'label' => 'Revenue',
                    'data' => $revenue_data,
                    'borderColor' => '#1cc88a',
                    'fill' => false
                )
            )
        );
    }

    /**
     * Get agent performance data for charts
     */
    public static function get_agent_performance_data() {
        global $wpdb;
        $deals_table = $wpdb->prefix . 'herohub_deals';
        $agent_id = get_current_user_id();
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE_FORMAT(completion_date, '%Y-%m') as month,
                COUNT(*) as deals,
                SUM(commission_amount) as commission
            FROM {$deals_table}
            WHERE agent_id = %d
            AND status = 'completed'
            AND completion_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month ASC",
            $agent_id
        ));

        $labels = array();
        $deals_data = array();
        $commission_data = array();

        foreach ($results as $row) {
            $labels[] = date('M Y', strtotime($row->month));
            $deals_data[] = $row->deals;
            $commission_data[] = $row->commission;
        }

        return array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => 'Deals Closed',
                    'data' => $deals_data,
                    'borderColor' => '#4e73df',
                    'fill' => false
                ),
                array(
                    'label' => 'Commission',
                    'data' => $commission_data,
                    'borderColor' => '#1cc88a',
                    'fill' => false
                )
            )
        );
    }
}
