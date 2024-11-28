<?php
/**
 * Admin dashboard display
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get dashboard data
$total_leads = wp_count_posts('herohub_lead')->publish;
$total_deals = wp_count_posts('herohub_deal')->publish;
$total_properties = wp_count_posts('herohub_property')->publish;

// Get recent leads
$recent_leads = get_posts(array(
    'post_type' => 'herohub_lead',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
));

// Get active deals
$active_deals = get_posts(array(
    'post_type' => 'herohub_deal',
    'posts_per_page' => 5,
    'orderby' => 'modified',
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => '_deal_status',
            'value' => array('active', 'negotiation', 'pending'),
            'compare' => 'IN'
        )
    )
));

?>
<div class="wrap herohub-crm-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Overview Cards -->
    <div class="herohub-crm-overview">
        <div class="herohub-crm-card">
            <h3><?php _e('Total Leads', 'herohub-crm'); ?></h3>
            <div class="herohub-crm-card-number"><?php echo esc_html($total_leads); ?></div>
            <a href="<?php echo admin_url('edit.php?post_type=herohub_lead'); ?>" class="button">
                <?php _e('View All Leads', 'herohub-crm'); ?>
            </a>
        </div>
        
        <div class="herohub-crm-card">
            <h3><?php _e('Active Deals', 'herohub-crm'); ?></h3>
            <div class="herohub-crm-card-number"><?php echo esc_html($total_deals); ?></div>
            <a href="<?php echo admin_url('edit.php?post_type=herohub_deal'); ?>" class="button">
                <?php _e('View All Deals', 'herohub-crm'); ?>
            </a>
        </div>
        
        <div class="herohub-crm-card">
            <h3><?php _e('Properties', 'herohub-crm'); ?></h3>
            <div class="herohub-crm-card-number"><?php echo esc_html($total_properties); ?></div>
            <a href="<?php echo admin_url('edit.php?post_type=herohub_property'); ?>" class="button">
                <?php _e('View Properties', 'herohub-crm'); ?>
            </a>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="herohub-crm-recent">
        <div class="herohub-crm-section">
            <h2><?php _e('Recent Leads', 'herohub-crm'); ?></h2>
            <div class="herohub-crm-table-wrapper">
                <?php if (!empty($recent_leads)) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'herohub-crm'); ?></th>
                                <th><?php _e('Email', 'herohub-crm'); ?></th>
                                <th><?php _e('Phone', 'herohub-crm'); ?></th>
                                <th><?php _e('Score', 'herohub-crm'); ?></th>
                                <th><?php _e('Added', 'herohub-crm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leads as $lead) : 
                                $email = get_post_meta($lead->ID, '_email', true);
                                $phone = get_post_meta($lead->ID, '_phone', true);
                                $score = get_post_meta($lead->ID, '_lead_score', true);
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($lead->ID); ?>">
                                            <?php echo esc_html($lead->post_title); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($email); ?></td>
                                    <td><?php echo esc_html($phone); ?></td>
                                    <td><?php echo $score ? esc_html($score) : '—'; ?></td>
                                    <td><?php echo get_the_date('', $lead->ID); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php _e('No recent leads found.', 'herohub-crm'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="herohub-crm-section">
            <h2><?php _e('Active Deals', 'herohub-crm'); ?></h2>
            <div class="herohub-crm-table-wrapper">
                <?php if (!empty($active_deals)) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Deal', 'herohub-crm'); ?></th>
                                <th><?php _e('Lead', 'herohub-crm'); ?></th>
                                <th><?php _e('Value', 'herohub-crm'); ?></th>
                                <th><?php _e('Status', 'herohub-crm'); ?></th>
                                <th><?php _e('Last Updated', 'herohub-crm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_deals as $deal) : 
                                $lead_id = get_post_meta($deal->ID, '_lead_id', true);
                                $lead = get_post($lead_id);
                                $value = get_post_meta($deal->ID, '_deal_value', true);
                                $status = get_post_meta($deal->ID, '_deal_status', true);
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($deal->ID); ?>">
                                            <?php echo esc_html($deal->post_title); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($lead) : ?>
                                            <a href="<?php echo get_edit_post_link($lead->ID); ?>">
                                                <?php echo esc_html($lead->post_title); ?>
                                            </a>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $value ? ('$' . number_format($value, 2)) : '—'; ?></td>
                                    <td><?php echo esc_html(ucfirst($status)); ?></td>
                                    <td><?php echo get_the_modified_date('', $deal->ID); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php _e('No active deals found.', 'herohub-crm'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
