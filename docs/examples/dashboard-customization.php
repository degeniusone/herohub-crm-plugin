<?php
/**
 * Example: Dashboard Customization
 * 
 * This file demonstrates how to customize and extend the HeroHub CRM dashboard.
 */

// Register custom dashboard widget
function register_custom_dashboard_widgets() {
    add_action('herohub_crm_register_dashboard_widgets', function($dashboard) {
        // Add performance widget
        $dashboard->add_widget('agent_performance', array(
            'title'    => 'Agent Performance',
            'callback' => 'render_performance_widget',
            'context'  => 'normal',
            'priority' => 'high'
        ));
        
        // Add revenue forecast widget
        $dashboard->add_widget('revenue_forecast', array(
            'title'    => 'Revenue Forecast',
            'callback' => 'render_forecast_widget',
            'context'  => 'side',
            'priority' => 'high'
        ));
        
        // Add recent activities widget
        $dashboard->add_widget('recent_activities', array(
            'title'    => 'Recent Activities',
            'callback' => 'render_activities_widget',
            'context'  => 'normal',
            'priority' => 'default'
        ));
    });
}
add_action('init', 'register_custom_dashboard_widgets');

// Render performance widget
function render_performance_widget() {
    // Get current user's performance data
    $user_id = get_current_user_id();
    $period = isset($_GET['period']) ? $_GET['period'] : '30'; // days
    
    $performance = array(
        'leads' => herohub_crm_get_user_leads_count($user_id, $period),
        'deals' => herohub_crm_get_user_deals_count($user_id, $period),
        'revenue' => herohub_crm_get_user_revenue($user_id, $period),
        'conversion' => herohub_crm_get_user_conversion_rate($user_id, $period)
    );
    
    // Prepare chart data
    $chart_data = array(
        'labels' => array(),
        'datasets' => array(
            array(
                'label' => 'Leads',
                'data' => array()
            ),
            array(
                'label' => 'Deals',
                'data' => array()
            )
        )
    );
    
    for ($i = $period; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $chart_data['labels'][] = $date;
        
        $chart_data['datasets'][0]['data'][] = herohub_crm_get_user_leads_count($user_id, 1, $date);
        $chart_data['datasets'][1]['data'][] = herohub_crm_get_user_deals_count($user_id, 1, $date);
    }
    
    // Render widget
    ?>
    <div class="performance-widget">
        <div class="metrics-grid">
            <div class="metric">
                <span class="label">Leads</span>
                <span class="value"><?php echo $performance['leads']; ?></span>
            </div>
            <div class="metric">
                <span class="label">Deals</span>
                <span class="value"><?php echo $performance['deals']; ?></span>
            </div>
            <div class="metric">
                <span class="label">Revenue</span>
                <span class="value">$<?php echo number_format($performance['revenue'], 2); ?></span>
            </div>
            <div class="metric">
                <span class="label">Conversion Rate</span>
                <span class="value"><?php echo number_format($performance['conversion'], 1); ?>%</span>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="performance-chart"></canvas>
        </div>
        
        <script>
            new Chart(document.getElementById('performance-chart'), {
                type: 'line',
                data: <?php echo json_encode($chart_data); ?>,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>
    <?php
}

// Render forecast widget
function render_forecast_widget() {
    $forecast = forecast_deals(90); // Next 90 days
    
    // Calculate probability-weighted revenue
    $weighted_revenue = 0;
    foreach ($forecast['probability'] as $stage => $data) {
        $probability = get_stage_probability($stage);
        $weighted_revenue += $data['value'] * $probability;
    }
    
    ?>
    <div class="forecast-widget">
        <div class="forecast-summary">
            <div class="metric">
                <span class="label">Pipeline Value</span>
                <span class="value">$<?php echo number_format($forecast['total_value'], 2); ?></span>
            </div>
            <div class="metric">
                <span class="label">Expected Revenue</span>
                <span class="value">$<?php echo number_format($weighted_revenue, 2); ?></span>
            </div>
            <div class="metric">
                <span class="label">Active Deals</span>
                <span class="value"><?php echo $forecast['deal_count']; ?></span>
            </div>
        </div>
        
        <div class="stage-breakdown">
            <?php foreach ($forecast['probability'] as $stage => $data): ?>
                <div class="stage">
                    <span class="stage-name"><?php echo ucfirst($stage); ?></span>
                    <span class="stage-count"><?php echo $data['count']; ?> deals</span>
                    <span class="stage-value">$<?php echo number_format($data['value'], 2); ?></span>
                    <div class="progress-bar" style="width: <?php echo ($data['value'] / $forecast['total_value']) * 100; ?>%"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

// Render activities widget
function render_activities_widget() {
    $activities = herohub_crm_get_recent_activities(array(
        'per_page' => 10,
        'orderby'  => 'created_at',
        'order'    => 'DESC'
    ));
    
    ?>
    <div class="activities-widget">
        <div class="activity-list">
            <?php foreach ($activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <?php echo get_activity_icon($activity['type']); ?>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <?php echo esc_html($activity['description']); ?>
                        </div>
                        <div class="activity-meta">
                            <span class="activity-time">
                                <?php echo human_time_diff(strtotime($activity['created_at']), current_time('timestamp')); ?> ago
                            </span>
                            <span class="activity-user">
                                by <?php echo get_user_by('id', $activity['user_id'])->display_name; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($activities)): ?>
            <div class="no-activities">
                No recent activities found.
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Helper function to get stage probability
function get_stage_probability($stage) {
    $probabilities = array(
        'qualifying'  => 0.3,
        'negotiating' => 0.6,
        'closing'     => 0.9
    );
    
    return $probabilities[$stage] ?? 0;
}

// Helper function to get activity icon
function get_activity_icon($type) {
    $icons = array(
        'lead_created'    => '<i class="fas fa-user-plus"></i>',
        'deal_created'    => '<i class="fas fa-handshake"></i>',
        'status_changed'  => '<i class="fas fa-exchange-alt"></i>',
        'note_added'      => '<i class="fas fa-sticky-note"></i>',
        'email_sent'      => '<i class="fas fa-envelope"></i>',
        'call_logged'     => '<i class="fas fa-phone"></i>',
        'meeting_scheduled' => '<i class="fas fa-calendar"></i>',
        'document_uploaded' => '<i class="fas fa-file-upload"></i>'
    );
    
    return $icons[$type] ?? '<i class="fas fa-circle"></i>';
}

// Add dashboard styles
function add_dashboard_styles() {
    ?>
    <style>
        .performance-widget .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .performance-widget .metric {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        
        .performance-widget .label {
            display: block;
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .performance-widget .value {
            display: block;
            font-size: 1.5rem;
            font-weight: bold;
            color: #212529;
        }
        
        .forecast-widget .stage {
            margin-bottom: 1rem;
            position: relative;
        }
        
        .forecast-widget .progress-bar {
            height: 4px;
            background: #007bff;
            border-radius: 2px;
            margin-top: 0.5rem;
        }
        
        .activities-widget .activity-item {
            display: flex;
            align-items: start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .activities-widget .activity-icon {
            width: 2rem;
            text-align: center;
            color: #6c757d;
        }
        
        .activities-widget .activity-meta {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
    <?php
}
add_action('admin_head', 'add_dashboard_styles');
