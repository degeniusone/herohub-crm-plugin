<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap herohub-dashboard admin-dashboard">
    <h1><?php echo esc_html__('HeroHub CRM Dashboard', 'herohub-crm'); ?></h1>

    <div class="dashboard-grid">
        <!-- System Overview -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('System Overview', 'herohub-crm'); ?></h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Total Managers', 'herohub-crm'); ?></span>
                    <span class="stat-value"><?php echo esc_html(herohub_get_total_managers()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Total Agents', 'herohub-crm'); ?></span>
                    <span class="stat-value"><?php echo esc_html(herohub_get_total_agents()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Total Properties', 'herohub-crm'); ?></span>
                    <span class="stat-value"><?php echo esc_html(herohub_get_total_properties()); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo esc_html__('Total Deals', 'herohub-crm'); ?></span>
                    <span class="stat-value"><?php echo esc_html(herohub_get_total_deals()); ?></span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Recent Activity', 'herohub-crm'); ?></h2>
            <div class="activity-list">
                <?php
                $activities = herohub_get_recent_activities(5);
                if (!empty($activities)) :
                    foreach ($activities as $activity) :
                        ?>
                        <div class="activity-item">
                            <span class="activity-time"><?php echo esc_html(human_time_diff($activity->time, current_time('timestamp'))); ?></span>
                            <span class="activity-text"><?php echo esc_html($activity->description); ?></span>
                        </div>
                        <?php
                    endforeach;
                else :
                    ?>
                    <p><?php echo esc_html__('No recent activity.', 'herohub-crm'); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Performance Overview', 'herohub-crm'); ?></h2>
            <div class="performance-stats">
                <div class="performance-item">
                    <span class="performance-label"><?php echo esc_html__('Total Revenue', 'herohub-crm'); ?></span>
                    <span class="performance-value"><?php echo esc_html(herohub_format_currency(herohub_get_total_revenue())); ?></span>
                </div>
                <div class="performance-item">
                    <span class="performance-label"><?php echo esc_html__('Average Deal Size', 'herohub-crm'); ?></span>
                    <span class="performance-value"><?php echo esc_html(herohub_format_currency(herohub_get_average_deal_size())); ?></span>
                </div>
                <div class="performance-item">
                    <span class="performance-label"><?php echo esc_html__('Conversion Rate', 'herohub-crm'); ?></span>
                    <span class="performance-value"><?php echo esc_html(herohub_get_conversion_rate() . '%'); ?></span>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="dashboard-card">
            <h2><?php echo esc_html__('Top Performers', 'herohub-crm'); ?></h2>
            <div class="performers-list">
                <?php
                $top_performers = herohub_get_top_performers(5);
                if (!empty($top_performers)) :
                    foreach ($top_performers as $performer) :
                        ?>
                        <div class="performer-item">
                            <span class="performer-name"><?php echo esc_html($performer->name); ?></span>
                            <span class="performer-stats">
                                <span class="deals"><?php echo esc_html($performer->deals_closed); ?> <?php echo esc_html__('deals', 'herohub-crm'); ?></span>
                                <span class="revenue"><?php echo esc_html(herohub_format_currency($performer->revenue)); ?></span>
                            </span>
                        </div>
                        <?php
                    endforeach;
                else :
                    ?>
                    <p><?php echo esc_html__('No performance data available.', 'herohub-crm'); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </div>
</div>
