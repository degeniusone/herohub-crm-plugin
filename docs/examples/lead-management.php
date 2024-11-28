<?php
/**
 * Example: Lead Management
 * 
 * This file demonstrates various ways to work with leads in HeroHub CRM.
 */

// Create a new lead
$lead_data = array(
    'name'    => 'John Doe',
    'email'   => 'john@example.com',
    'phone'   => '1234567890',
    'source'  => 'website',
    'status'  => 'new',
    'notes'   => 'Interested in beachfront properties'
);

$lead_id = herohub_crm_create_lead($lead_data);

if (!is_wp_error($lead_id)) {
    // Lead created successfully
    echo "Lead created with ID: " . $lead_id;
} else {
    // Handle error
    echo "Error creating lead: " . $lead_id->get_error_message();
}

// Update lead status
$update_data = array(
    'status' => 'contacted',
    'notes'  => 'Called on Dec 1, scheduled viewing'
);

$updated = herohub_crm_update_lead($lead_id, $update_data);

// Add lead activity
$activity_data = array(
    'lead_id'     => $lead_id,
    'type'        => 'phone_call',
    'description' => 'Initial consultation call',
    'duration'    => 30 // minutes
);

herohub_crm_add_lead_activity($activity_data);

// Get lead with activities
$lead = herohub_crm_get_lead($lead_id);
$activities = herohub_crm_get_lead_activities($lead_id);

// Example of using filters
add_filter('herohub_crm_lead_data', function($data) {
    // Add default source if not set
    if (empty($data['source'])) {
        $data['source'] = 'direct';
    }
    
    // Normalize phone number
    if (!empty($data['phone'])) {
        $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
    }
    
    return $data;
});

// Example of using actions
add_action('herohub_crm_after_lead_create', function($lead_id, $lead_data) {
    // Send notification email
    $to = get_option('admin_email');
    $subject = 'New Lead Created';
    $message = sprintf(
        'New lead created: %s (%s)',
        $lead_data['name'],
        $lead_data['email']
    );
    
    wp_mail($to, $subject, $message);
}, 10, 2);

// Example: Custom lead query
$args = array(
    'status'    => 'active',
    'source'    => 'website',
    'orderby'   => 'created_at',
    'order'     => 'DESC',
    'per_page'  => 10,
    'page'      => 1
);

$leads = herohub_crm_query_leads($args);

// Example: Export leads to CSV
$export_args = array(
    'status' => array('active', 'contacted'),
    'date_range' => array(
        'start' => '2023-01-01',
        'end'   => '2023-12-31'
    ),
    'fields' => array(
        'name',
        'email',
        'phone',
        'status',
        'source',
        'created_at'
    )
);

$csv_file = herohub_crm_export_leads_csv($export_args);

// Example: Custom lead validation
function validate_lead_data($data) {
    $errors = new WP_Error();
    
    // Validate email
    if (!is_email($data['email'])) {
        $errors->add('invalid_email', 'Invalid email address');
    }
    
    // Validate phone
    if (!empty($data['phone']) && !preg_match('/^\d{10}$/', $data['phone'])) {
        $errors->add('invalid_phone', 'Phone number must be 10 digits');
    }
    
    // Validate status
    $valid_statuses = array('new', 'contacted', 'qualified', 'lost');
    if (!in_array($data['status'], $valid_statuses)) {
        $errors->add('invalid_status', 'Invalid lead status');
    }
    
    return $errors;
}

// Example: Custom lead source tracking
function track_lead_source() {
    // Get UTM parameters
    $utm_source   = $_GET['utm_source'] ?? '';
    $utm_medium   = $_GET['utm_medium'] ?? '';
    $utm_campaign = $_GET['utm_campaign'] ?? '';
    
    // Store in session
    if (!session_id()) {
        session_start();
    }
    
    $_SESSION['lead_source'] = array(
        'utm_source'   => $utm_source,
        'utm_medium'   => $utm_medium,
        'utm_campaign' => $utm_campaign,
        'referrer'     => $_SERVER['HTTP_REFERER'] ?? '',
        'timestamp'    => current_time('mysql')
    );
}
add_action('init', 'track_lead_source');

// Example: Lead scoring
function calculate_lead_score($lead_id) {
    $lead = herohub_crm_get_lead($lead_id);
    $score = 0;
    
    // Score based on source
    $source_scores = array(
        'referral' => 30,
        'website'  => 20,
        'social'   => 15,
        'other'    => 10
    );
    
    $score += $source_scores[$lead['source']] ?? 10;
    
    // Score based on activity
    $activities = herohub_crm_get_lead_activities($lead_id);
    $score += count($activities) * 5;
    
    // Score based on property views
    $property_views = herohub_crm_get_lead_property_views($lead_id);
    $score += count($property_views) * 3;
    
    return $score;
}

// Example: Lead assignment
function auto_assign_lead($lead_id) {
    // Get available agents
    $args = array(
        'role'    => 'real_estate_agent',
        'orderby' => 'meta_value_num',
        'meta_key' => 'lead_count',
        'order'   => 'ASC'
    );
    
    $agents = get_users($args);
    
    if (!empty($agents)) {
        // Assign to agent with least leads
        $agent_id = $agents[0]->ID;
        
        // Update lead
        herohub_crm_update_lead($lead_id, array('agent_id' => $agent_id));
        
        // Update agent's lead count
        $lead_count = get_user_meta($agent_id, 'lead_count', true);
        update_user_meta($agent_id, 'lead_count', (int)$lead_count + 1);
        
        // Notify agent
        $lead = herohub_crm_get_lead($lead_id);
        $subject = 'New Lead Assigned';
        $message = sprintf(
            'New lead assigned to you: %s (%s)',
            $lead['name'],
            $lead['email']
        );
        
        wp_mail($agent_id->user_email, $subject, $message);
    }
}
add_action('herohub_crm_after_lead_create', 'auto_assign_lead');
