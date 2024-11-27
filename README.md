# HeroHub CRM - WordPress Plugin

A comprehensive Customer Relationship Management (CRM) plugin for real estate professionals, built on WordPress.

## Features

- Contact Management
- Deal Tracking
- Property Listings
- Event Scheduling
- Activity Logging
- Custom Role Management
- Analytics Dashboard
- CSV Import/Export

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation

1. Download the plugin zip file
2. Go to WordPress admin panel > Plugins > Add New
3. Click "Upload Plugin" and choose the downloaded zip file
4. Click "Install Now" and then "Activate"

## Configuration

1. After activation, go to WordPress admin panel > HeroHub CRM > Settings
2. Configure user roles and permissions
3. Set up default values and preferences
4. Start adding your contacts and properties

## User Roles

### Admin
- Full access to all plugin features
- Manage plugin settings and user roles
- Access all reports and analytics

### Manager
- Create and manage agents
- View all contacts, deals, and properties
- Access team-wide reports
- Import/Export data

### Agent
- Manage assigned contacts
- Create and update deals
- Schedule events and activities
- Access personal dashboard

## Development

### Setup

1. Clone the repository:
```bash
git clone https://github.com/yourusername/herohub-crm.git
```

2. Install dependencies:
```bash
composer install
```

### Directory Structure

```
herohub_plugin/
├── herohub-crm.php (main plugin file)
├── includes/
│   ├── admin/
│   │   ├── class-contact-metabox.php
│   │   ├── class-deal-metabox.php
│   │   └── class-admin.php
│   └── core/
│       ├── class-post-types.php
│       └── class-taxonomies.php
├── admin/
│   ├── css/herohub-crm-admin.css
│   ├── js/herohub-crm-admin.js
│   └── partials/
│       ├── herohub-crm-admin-dashboard.php
│       └── herohub-crm-admin-settings.php
└── doc.md
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Support

For support, please:
1. Check the documentation
2. Search existing issues
3. Create a new issue if needed

## License

This project is licensed under the GPL v2 or later - see the LICENSE file for details.
