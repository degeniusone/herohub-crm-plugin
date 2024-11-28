# HeroHub CRM - API Reference

## Table of Contents
1. [REST API Endpoints](#rest-api-endpoints)
2. [Hooks](#hooks)
3. [Functions](#functions)
4. [Constants](#constants)

## REST API Endpoints

### Leads

#### Get Leads
```
GET /wp-json/herohub-crm/v1/leads
```

Parameters:
- `page` (int): Page number
- `per_page` (int): Items per page
- `status` (string): Lead status
- `search` (string): Search term

Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "status": "active",
            "created_at": "2023-12-01T12:00:00Z"
        }
    ],
    "total": 100,
    "pages": 10
}
```

#### Create Lead
```
POST /wp-json/herohub-crm/v1/leads
```

Request Body:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "source": "website"
}
```

### Deals

#### Get Deals
```
GET /wp-json/herohub-crm/v1/deals
```

Parameters:
- `page` (int): Page number
- `per_page` (int): Items per page
- `status` (string): Deal status
- `lead_id` (int): Associated lead ID

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Property Sale",
            "amount": 250000,
            "status": "pending",
            "lead_id": 1
        }
    ],
    "total": 50,
    "pages": 5
}
```

## Hooks

### Actions

#### Lead Management
```php
// After lead creation
do_action('herohub_crm_after_lead_create', int $lead_id, array $lead_data)

// Before lead update
do_action('herohub_crm_before_lead_update', int $lead_id, array $lead_data)

// After lead update
do_action('herohub_crm_after_lead_update', int $lead_id, array $lead_data)

// Before lead deletion
do_action('herohub_crm_before_lead_delete', int $lead_id)
```

#### Deal Management
```php
// After deal creation
do_action('herohub_crm_after_deal_create', int $deal_id, array $deal_data)

// Before deal update
do_action('herohub_crm_before_deal_update', int $deal_id, array $deal_data)

// After deal status change
do_action('herohub_crm_after_deal_status_change', int $deal_id, string $old_status, string $new_status)
```

#### Task Management
```php
// After task creation
do_action('herohub_crm_after_task_create', int $task_id, array $task_data)

// Before task completion
do_action('herohub_crm_before_task_complete', int $task_id)

// After task assignment
do_action('herohub_crm_after_task_assign', int $task_id, int $user_id)
```

### Filters

#### Lead Data
```php
// Modify lead data before save
apply_filters('herohub_crm_lead_data', array $lead_data)

// Filter lead list query
apply_filters('herohub_crm_lead_query_args', array $query_args)

// Modify lead columns in admin
apply_filters('herohub_crm_lead_columns', array $columns)
```

#### Deal Data
```php
// Modify deal data before save
apply_filters('herohub_crm_deal_data', array $deal_data)

// Filter deal amount display
apply_filters('herohub_crm_deal_amount_display', string $amount, array $deal_data)

// Customize deal statuses
apply_filters('herohub_crm_deal_statuses', array $statuses)
```

## Functions

### Lead Functions

```php
/**
 * Get lead by ID
 *
 * @param int $lead_id Lead ID
 * @return array|false Lead data or false if not found
 */
function herohub_crm_get_lead($lead_id)

/**
 * Create new lead
 *
 * @param array $lead_data Lead data
 * @return int|WP_Error Lead ID or error
 */
function herohub_crm_create_lead($lead_data)

/**
 * Update lead
 *
 * @param int $lead_id Lead ID
 * @param array $lead_data Updated lead data
 * @return bool|WP_Error True on success or error
 */
function herohub_crm_update_lead($lead_id, $lead_data)
```

### Deal Functions

```php
/**
 * Get deal by ID
 *
 * @param int $deal_id Deal ID
 * @return array|false Deal data or false if not found
 */
function herohub_crm_get_deal($deal_id)

/**
 * Create new deal
 *
 * @param array $deal_data Deal data
 * @return int|WP_Error Deal ID or error
 */
function herohub_crm_create_deal($deal_data)

/**
 * Update deal status
 *
 * @param int $deal_id Deal ID
 * @param string $status New status
 * @return bool|WP_Error True on success or error
 */
function herohub_crm_update_deal_status($deal_id, $status)
```

### Utility Functions

```php
/**
 * Format currency amount
 *
 * @param float $amount Amount to format
 * @param array $args Formatting arguments
 * @return string Formatted amount
 */
function herohub_crm_format_currency($amount, $args = array())

/**
 * Get user's leads
 *
 * @param int $user_id User ID
 * @param array $args Query arguments
 * @return array Array of leads
 */
function herohub_crm_get_user_leads($user_id, $args = array())

/**
 * Check user permission
 *
 * @param string $capability Capability to check
 * @param int $user_id Optional user ID
 * @return bool True if user has capability
 */
function herohub_crm_user_can($capability, $user_id = null)
```

## Constants

```php
// Plugin version
define('HEROHUB_CRM_VERSION', '1.0.0');

// Plugin file
define('HEROHUB_CRM_FILE', __FILE__);

// Plugin directory
define('HEROHUB_CRM_DIR', plugin_dir_path(__FILE__));

// Plugin URL
define('HEROHUB_CRM_URL', plugin_dir_url(__FILE__));

// Plugin includes directory
define('HEROHUB_CRM_INCLUDES', HEROHUB_CRM_DIR . 'includes');

// Plugin templates directory
define('HEROHUB_CRM_TEMPLATES', HEROHUB_CRM_DIR . 'templates');

// Debug mode
define('HEROHUB_CRM_DEBUG', false);
```
