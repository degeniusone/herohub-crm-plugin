<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap herohub-dashboard manager-dashboard">
    <h1><?php echo esc_html__('Manager Dashboard', 'herohub-crm'); ?></h1>

    <div class="dashboard-grid">
        <!-- Team Overview -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Team Overview', 'herohub-crm'); ?></h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Active Agents', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="active_agents"><?php echo esc_html(herohub_get_manager_active_agents()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Total Listings', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="total_listings"><?php echo esc_html(herohub_get_manager_total_listings()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Pending Deals', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="pending_deals"><?php echo esc_html(herohub_get_manager_pending_deals()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Monthly Revenue', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="monthly_revenue"><?php echo esc_html(herohub_format_currency(herohub_get_manager_monthly_revenue())); ?></span>
                </div>
            </div>
        </div>

        <!-- Agent Performance -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Agent Performance', 'herohub-crm'); ?></h2>
            <div class="agent-performance">
                <?php
                $agents = herohub_get_manager_agents();
                if (!empty($agents)) :
                    foreach ($agents as $agent) :
                        $performance = herohub_get_agent_performance($agent->ID);
                        ?>
                        <div class="agent-item">
                            <div class="agent-info">
                                <span class="agent-name"><?php echo esc_html($agent->display_name); ?></span>
                                <span class="agent-status <?php echo esc_attr($performance->status); ?>"><?php echo esc_html($performance->status_label); ?></span>
                            </div>
                            <div class="agent-metrics">
                                <div class="metric">
                                    <span class="metric-label"><?php echo esc_html__('Deals', 'herohub-crm'); ?></span>
                                    <span class="metric-value"><?php echo esc_html($performance->deals); ?></span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label"><?php echo esc_html__('Revenue', 'herohub-crm'); ?></span>
                                    <span class="metric-value"><?php echo esc_html(herohub_format_currency($performance->revenue)); ?></span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label"><?php echo esc_html__('Conversion', 'herohub-crm'); ?></span>
                                    <span class="metric-value"><?php echo esc_html($performance->conversion_rate . '%'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach;
                else :
                    ?>
                    <p><?php echo esc_html__('No agents found in your team.', 'herohub-crm'); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>

        <!-- Tasks & Approvals -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Tasks & Approvals', 'herohub-crm'); ?></h2>
            <div class="tasks-list">
                <?php
                $tasks = herohub_get_manager_tasks();
                if (!empty($tasks)) :
                    foreach ($tasks as $task) :
                        ?>
                        <div class="task-item <?php echo esc_attr($task->priority); ?>">
                            <div class="task-header">
                                <span class="task-type"><?php echo esc_html($task->type_label); ?></span>
                                <span class="task-time"><?php echo esc_html(human_time_diff($task->time, current_time('timestamp'))); ?></span>
                            </div>
                            <div class="task-content">
                                <p><?php echo esc_html($task->description); ?></p>
                                <?php if ($task->requires_action) : ?>
                                    <div class="task-actions">
                                        <button class="button approve-task" data-task-id="<?php echo esc_attr($task->ID); ?>"><?php echo esc_html__('Approve', 'herohub-crm'); ?></button>
                                        <button class="button reject-task" data-task-id="<?php echo esc_attr($task->ID); ?>"><?php echo esc_html__('Reject', 'herohub-crm'); ?></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    endforeach;
                else :
                    ?>
                    <p><?php echo esc_html__('No pending tasks or approvals.', 'herohub-crm'); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>

        <!-- Team Analytics -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Team Analytics', 'herohub-crm'); ?></h2>
            <div class="analytics-content">
                <div class="chart-container">
                    <canvas id="team-performance-chart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color deals"></span>
                        <span class="legend-label"><?php echo esc_html__('Deals Closed', 'herohub-crm'); ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color revenue"></span>
                        <span class="legend-label"><?php echo esc_html__('Revenue', 'herohub-crm'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize Team Performance Chart
    const ctx = document.getElementById('team-performance-chart').getContext('2d');
    const chartData = <?php echo json_encode(herohub_get_team_performance_data()); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
