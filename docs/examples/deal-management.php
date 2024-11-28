<?php
/**
 * Example: Deal Management
 * 
 * This file demonstrates various ways to work with deals in HeroHub CRM.
 */

// Create a new deal
$deal_data = array(
    'lead_id'     => 123,
    'title'       => 'Beachfront Villa Sale',
    'amount'      => 750000.00,
    'status'      => 'new',
    'type'        => 'sale',
    'description' => '4 bedroom beachfront villa',
    'commission'  => 5, // percentage
    'close_date'  => '2024-03-15'
);

$deal_id = herohub_crm_create_deal($deal_data);

if (!is_wp_error($deal_id)) {
    // Deal created successfully
    echo "Deal created with ID: " . $deal_id;
} else {
    // Handle error
    echo "Error creating deal: " . $deal_id->get_error_message();
}

// Update deal status with stage tracking
function update_deal_stage($deal_id, $new_status) {
    $old_status = herohub_crm_get_deal_status($deal_id);
    
    $update_data = array(
        'status' => $new_status,
        'stage_history' => array(
            'from_stage' => $old_status,
            'to_stage'   => $new_status,
            'timestamp'  => current_time('mysql'),
            'user_id'    => get_current_user_id()
        )
    );
    
    return herohub_crm_update_deal($deal_id, $update_data);
}

// Add deal documents
function attach_deal_documents($deal_id, $files) {
    foreach ($files as $file) {
        $document_data = array(
            'deal_id'     => $deal_id,
            'name'        => $file['name'],
            'type'        => $file['type'],
            'size'        => $file['size'],
            'path'        => $file['path'],
            'uploaded_by' => get_current_user_id(),
            'category'    => $file['category'] // contract, inspection, etc.
        );
        
        herohub_crm_add_deal_document($document_data);
    }
}

// Calculate deal metrics
function calculate_deal_metrics($deal_id) {
    $deal = herohub_crm_get_deal($deal_id);
    
    // Calculate commission
    $commission_amount = ($deal['amount'] * $deal['commission']) / 100;
    
    // Calculate timeline
    $created_date = new DateTime($deal['created_at']);
    $closed_date = new DateTime($deal['close_date']);
    $timeline = $created_date->diff($closed_date);
    
    // Get stage history
    $stage_history = herohub_crm_get_deal_stage_history($deal_id);
    $stage_duration = array();
    
    foreach ($stage_history as $stage) {
        $start = new DateTime($stage['timestamp']);
        $end = isset($stage_history[$stage['to_stage']]) 
            ? new DateTime($stage_history[$stage['to_stage']]['timestamp'])
            : new DateTime();
            
        $duration = $start->diff($end);
        $stage_duration[$stage['from_stage']] = $duration;
    }
    
    return array(
        'commission_amount' => $commission_amount,
        'timeline'         => $timeline,
        'stage_duration'   => $stage_duration
    );
}

// Example: Deal pipeline visualization data
function get_pipeline_data($args = array()) {
    $stages = array(
        'new'         => array('deals' => 0, 'value' => 0),
        'qualifying'  => array('deals' => 0, 'value' => 0),
        'negotiating' => array('deals' => 0, 'value' => 0),
        'closing'     => array('deals' => 0, 'value' => 0),
        'won'         => array('deals' => 0, 'value' => 0),
        'lost'        => array('deals' => 0, 'value' => 0)
    );
    
    $deals = herohub_crm_query_deals($args);
    
    foreach ($deals as $deal) {
        $stages[$deal['status']]['deals']++;
        $stages[$deal['status']]['value'] += $deal['amount'];
    }
    
    return $stages;
}

// Example: Deal forecasting
function forecast_deals($period = '30') {
    $forecast = array(
        'total_value'     => 0,
        'weighted_value'  => 0,
        'deal_count'      => 0,
        'probability'     => array()
    );
    
    // Get deals closing in the next X days
    $args = array(
        'close_date' => array(
            'start' => current_time('mysql'),
            'end'   => date('Y-m-d', strtotime("+{$period} days"))
        ),
        'status' => array('qualifying', 'negotiating', 'closing')
    );
    
    $deals = herohub_crm_query_deals($args);
    
    // Stage probability weights
    $weights = array(
        'qualifying'  => 0.3,
        'negotiating' => 0.6,
        'closing'     => 0.9
    );
    
    foreach ($deals as $deal) {
        $forecast['total_value'] += $deal['amount'];
        $forecast['weighted_value'] += $deal['amount'] * $weights[$deal['status']];
        $forecast['deal_count']++;
        
        if (!isset($forecast['probability'][$deal['status']])) {
            $forecast['probability'][$deal['status']] = array(
                'count' => 0,
                'value' => 0
            );
        }
        
        $forecast['probability'][$deal['status']]['count']++;
        $forecast['probability'][$deal['status']]['value'] += $deal['amount'];
    }
    
    return $forecast;
}

// Example: Deal notifications
function setup_deal_notifications() {
    // New deal created
    add_action('herohub_crm_after_deal_create', function($deal_id, $deal_data) {
        $manager_email = get_option('herohub_crm_manager_email');
        $subject = 'New Deal Created';
        $message = sprintf(
            'New deal created: %s\nAmount: $%s\nLead: %s',
            $deal_data['title'],
            number_format($deal_data['amount'], 2),
            herohub_crm_get_lead_name($deal_data['lead_id'])
        );
        
        wp_mail($manager_email, $subject, $message);
    }, 10, 2);
    
    // Deal status changed
    add_action('herohub_crm_after_deal_status_change', function($deal_id, $old_status, $new_status) {
        $deal = herohub_crm_get_deal($deal_id);
        $agent_email = get_user_by('id', $deal['agent_id'])->user_email;
        
        $subject = 'Deal Status Updated';
        $message = sprintf(
            'Deal status updated: %s\nFrom: %s\nTo: %s',
            $deal['title'],
            $old_status,
            $new_status
        );
        
        wp_mail($agent_email, $subject, $message);
    }, 10, 3);
    
    // Deal closing soon
    add_action('herohub_crm_daily_cron', function() {
        $closing_soon = herohub_crm_query_deals(array(
            'status' => 'closing',
            'close_date' => array(
                'start' => current_time('mysql'),
                'end'   => date('Y-m-d', strtotime('+7 days'))
            )
        ));
        
        foreach ($closing_soon as $deal) {
            $agent_email = get_user_by('id', $deal['agent_id'])->user_email;
            $subject = 'Deal Closing Soon';
            $message = sprintf(
                'Deal closing in %d days: %s\nAmount: $%s',
                ceil((strtotime($deal['close_date']) - time()) / DAY_IN_SECONDS),
                $deal['title'],
                number_format($deal['amount'], 2)
            );
            
            wp_mail($agent_email, $subject, $message);
        }
    });
}

// Initialize deal notifications
setup_deal_notifications();
