<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="herohub-crm-dashboard">
        <div class="herohub-crm-stats-grid">
            <!-- Contacts Stats -->
            <div class="stats-card">
                <h3><?php _e('Contacts', 'herohub-crm'); ?></h3>
                <div class="stats-number">
                    <?php
                    $contacts_count = wp_count_posts('contact');
                    echo esc_html($contacts_count->publish);
                    ?>
                </div>
                <a href="<?php echo admin_url('edit.php?post_type=contact'); ?>" class="button">
                    <?php _e('View All', 'herohub-crm'); ?>
                </a>
            </div>

            <!-- Deals Stats -->
            <div class="stats-card">
                <h3><?php _e('Deals', 'herohub-crm'); ?></h3>
                <div class="stats-number">
                    <?php
                    $deals_count = wp_count_posts('deal');
                    echo esc_html($deals_count->publish);
                    ?>
                </div>
                <a href="<?php echo admin_url('edit.php?post_type=deal'); ?>" class="button">
                    <?php _e('View All', 'herohub-crm'); ?>
                </a>
            </div>

            <!-- Properties Stats -->
            <div class="stats-card">
                <h3><?php _e('Properties', 'herohub-crm'); ?></h3>
                <div class="stats-number">
                    <?php
                    $properties_count = wp_count_posts('property');
                    echo esc_html($properties_count->publish);
                    ?>
                </div>
                <a href="<?php echo admin_url('edit.php?post_type=property'); ?>" class="button">
                    <?php _e('View All', 'herohub-crm'); ?>
                </a>
            </div>

            <!-- Activities Stats -->
            <div class="stats-card">
                <h3><?php _e('Activities', 'herohub-crm'); ?></h3>
                <div class="stats-number">
                    <?php
                    $activities_count = wp_count_posts('activity');
                    echo esc_html($activities_count->publish);
                    ?>
                </div>
                <a href="<?php echo admin_url('edit.php?post_type=activity'); ?>" class="button">
                    <?php _e('View All', 'herohub-crm'); ?>
                </a>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="recent-activities">
            <h2><?php _e('Recent Activities', 'herohub-crm'); ?></h2>
            <?php
            $recent_activities = get_posts(array(
                'post_type' => 'activity',
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC'
            ));

            if ($recent_activities) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Activity', 'herohub-crm'); ?></th>
                            <th><?php _e('Type', 'herohub-crm'); ?></th>
                            <th><?php _e('Date', 'herohub-crm'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activities as $activity) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo get_edit_post_link($activity->ID); ?>">
                                        <?php echo esc_html($activity->post_title); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $terms = wp_get_post_terms($activity->ID, 'activity_type');
                                    echo esc_html($terms[0]->name ?? '');
                                    ?>
                                </td>
                                <td>
                                    <?php echo get_the_date('', $activity->ID); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No recent activities found.', 'herohub-crm'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
