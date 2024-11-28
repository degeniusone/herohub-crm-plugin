<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap herohub-dashboard agent-dashboard">
    <h1><?php echo esc_html__('Agent Dashboard', 'herohub-crm'); ?></h1>

    <div class="dashboard-grid">
        <!-- Personal Stats -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('My Performance', 'herohub-crm'); ?></h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Active Listings', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="active_listings"><?php echo esc_html(herohub_get_agent_active_listings()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Deals Closed', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="deals_closed"><?php echo esc_html(herohub_get_agent_deals_closed()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Commission', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="commission"><?php echo esc_html(herohub_format_currency(herohub_get_agent_commission())); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Conversion Rate', 'herohub-crm'); ?></span>
                    <span class="stat-value" data-key="conversion_rate"><?php echo esc_html(herohub_get_agent_conversion_rate() . '%'); ?></span>
                </div>
            </div>
        </div>

        <!-- Tasks -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('My Tasks', 'herohub-crm'); ?></h2>
            <div class="tasks-list">
                <?php
                $tasks = herohub_get_agent_tasks();
                if (!empty($tasks)) :
                    foreach ($tasks as $task) :
                        ?>
                        <div class="task-item <?php echo esc_attr($task->priority); ?>">
                            <div class="task-header">
                                <span class="task-priority"><?php echo esc_html($task->priority_label); ?></span>
                                <span class="task-due"><?php echo esc_html($task->due_date); ?></span>
                            </div>
                            <div class="task-content">
                                <p><?php echo esc_html($task->description); ?></p>
                                <?php if (!$task->completed) : ?>
                                    <button class="button complete-task" data-task-id="<?php echo esc_attr($task->ID); ?>">
                                        <?php echo esc_html__('Mark Complete', 'herohub-crm'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    endforeach;
                else :
                    ?>
                    <p><?php echo esc_html__('No pending tasks.', 'herohub-crm'); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Recent Leads', 'herohub-crm'); ?></h2>
            <div class="leads-list">
                <?php
                $leads = herohub_get_agent_leads();
                if (!empty($leads)) :
                    foreach ($leads as $lead) :
                        ?>
                        <div class="lead-item">
                            <div class="lead-info">
                                <span class="lead-name"><?php echo esc_html($lead->name); ?></span>
                                <span class="lead-source"><?php echo esc_html($lead->source); ?></span>
                            </div>
                            <div class="lead-details">
                                <span class="lead-property"><?php echo esc_html($lead->property_type); ?></span>
                                <span class="lead-budget"><?php echo esc_html(herohub_format_currency($lead->budget)); ?></span>
                            </div>
                            <div class="lead-actions">
                                <button class="button contact-lead" data-lead-id="<?php echo esc_attr($lead->ID); ?>">
                                    <?php echo esc_html__('Contact', 'herohub-crm'); ?>
                                </button>
                            </div>
                        </div>
                        <?php
                    endforeach;
                else :
                    ?>
                    <p><?php echo esc_html__('No recent leads.', 'herohub-crm'); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Performance Trends', 'herohub-crm'); ?></h2>
            <div class="chart-container">
                <canvas id="agent-performance-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize Agent Performance Chart
    const ctx = document.getElementById('agent-performance-chart').getContext('2d');
    const chartData = <?php echo json_encode(herohub_get_agent_performance_data()); ?>;
    
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

    // Handle task completion
    $('.complete-task').on('click', function() {
        const taskId = $(this).data('task-id');
        $.ajax({
            url: herohubAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'herohub_complete_task',
                task_id: taskId,
                nonce: herohubAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Handle lead contact
    $('.contact-lead').on('click', function() {
        const leadId = $(this).data('lead-id');
        $.ajax({
            url: herohubAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'herohub_contact_lead',
                lead_id: leadId,
                nonce: herohubAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;
                }
            }
        });
    });
});
</script>
