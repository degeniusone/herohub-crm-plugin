# HeroHub CRM - Developer Guide

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Development Setup](#development-setup)
3. [Plugin Structure](#plugin-structure)
4. [Core Components](#core-components)
5. [Database Schema](#database-schema)
6. [API Reference](#api-reference)
7. [Testing](#testing)
8. [Contributing](#contributing)

## Architecture Overview

### Design Principles
- Object-Oriented Programming (OOP)
- WordPress Coding Standards
- PSR-4 Autoloading
- Modular Architecture
- Security First

### Technology Stack
- PHP 7.4+
- WordPress 5.0+
- MySQL/MariaDB
- jQuery
- Chart.js

## Development Setup

### Prerequisites
```bash
# Required software
- PHP 7.4+
- Composer
- Node.js & npm
- WordPress 5.0+
```

### Installation
1. Clone the repository:
```bash
git clone https://github.com/herohub/crm-plugin.git
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Run tests:
```bash
composer test
```

## Plugin Structure

```
herohub_plugin/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
│   ├── admin/
│   │   ├── class-dashboard.php
│   │   ├── class-settings.php
│   │   └── views/
│   ├── class-herohub-crm.php
│   ├── class-installer.php
│   ├── class-ajax.php
│   ├── class-exporter.php
│   ├── class-logger.php
│   └── trait-error-handler.php
├── languages/
├── templates/
├── tests/
├── vendor/
├── herohub-crm.php
└── README.md
```

## Core Components

### Main Plugin Class (class-herohub-crm.php)
```php
namespace HeroHub\CRM;

class HeroHub_CRM {
    public function __construct() {
        // Initialize components
    }
}
```

### Installer (class-installer.php)
Handles:
- Database table creation
- Role creation
- Default settings
- Upgrades

### Logger (class-logger.php)
Features:
- Multiple log levels
- File rotation
- Email notifications
- Debug mode

### Error Handler (trait-error-handler.php)
Provides:
- Input validation
- Sanitization
- Error collection
- AJAX responses

## Database Schema

### Tables

#### herohub_leads
```sql
CREATE TABLE {$wpdb->prefix}herohub_leads (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20),
    status varchar(20) NOT NULL,
    source varchar(50),
    created_at datetime NOT NULL,
    updated_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

#### herohub_deals
```sql
CREATE TABLE {$wpdb->prefix}herohub_deals (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    lead_id bigint(20) unsigned NOT NULL,
    user_id bigint(20) unsigned NOT NULL,
    title varchar(100) NOT NULL,
    amount decimal(10,2) NOT NULL,
    status varchar(20) NOT NULL,
    created_at datetime NOT NULL,
    updated_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

## API Reference

### Hooks

#### Actions
```php
// After lead creation
do_action('herohub_crm_after_lead_create', $lead_id, $lead_data);

// After deal update
do_action('herohub_crm_after_deal_update', $deal_id, $deal_data);

// Before export
do_action('herohub_crm_before_export', $export_type, $data);
```

#### Filters
```php
// Modify lead data before save
apply_filters('herohub_crm_lead_data', $lead_data);

// Customize export columns
apply_filters('herohub_crm_export_columns', $columns, $export_type);

// Modify dashboard widgets
apply_filters('herohub_crm_dashboard_widgets', $widgets);
```

### Functions

```php
// Get lead details
herohub_crm_get_lead($lead_id);

// Update deal status
herohub_crm_update_deal_status($deal_id, $status);

// Generate report
herohub_crm_generate_report($type, $params);
```

## Testing

### Unit Tests
```bash
# Run all tests
composer test

# Run specific test suite
composer test -- --testsuite=Unit

# Generate coverage report
composer test-coverage
```

### Test Structure
```
tests/
├── bootstrap.php
├── test-installer.php
├── test-logger.php
└── test-error-handler.php
```

## Contributing

### Guidelines
1. Fork the repository
2. Create feature branch
3. Write tests
4. Follow coding standards
5. Submit pull request

### Code Standards
- Follow WordPress Coding Standards
- Use PHPDoc comments
- Write unit tests
- Keep functions focused
- Document changes

### Pull Request Process
1. Update documentation
2. Add tests
3. Update changelog
4. Submit PR with description

## Security

### Best Practices
- Validate all input
- Sanitize all output
- Use prepared statements
- Check permissions
- Implement nonces

### Reporting Issues
Email: security@herohub.com
