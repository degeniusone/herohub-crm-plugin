# HeroHub CRM - AI Developer Documentation

This document provides detailed technical guidance for the HeroHub CRM plugin, designed for traditional WordPress development within the admin interface. It includes all custom fields, roles, pages, and their respective configurations to assist AI coding tools in structuring the codebase accurately.

---

## Plugin Overview
HeroHub CRM leverages native WordPress capabilities to provide a robust CRM system for real estate, with a main focus on cold calling leads management for agents. Key features include custom post types, taxonomies, metaboxes, and roles to manage Contacts, Deals, Events, Properties, Activities, and more.

---

## Folder Structure
The folder structure organizes the plugin's functionality and ensures clarity for both developers and AI tools:

hero-hub-crm/
├── includes/
│   ├── admin/           # Logic for roles, dashboards, and settings
│   ├── core/            # Core CPTs, taxonomies, and metaboxes
├── templates/           # Templates for admin pages and metaboxes
├── assets/              # CSS, JS, and images
├── hero-hub-crm.php     # Main plugin file
└── README.md            # AI-focused documentation

---

## Roles and Capabilities
### Admin
- Access: Full access to all plugin features and WordPress core functionality.

### Manager
- Capabilities:
  - Create, edit, delete, read: Agents, Contacts, Deals, Events, Properties, Activities.
  - Assign Contacts to Agents.
  - Toggle between Manager and Agent views.
  - Dashboard: View stats for all Agents or a specific Agent.
  - Upload leads data list CSV.
  - Add agents and other managers.

### Agent
- Capabilities:
  - Create, edit, read: Contacts, Deals, Properties, Events, and Activities assigned to them.
  - Dashboard: View only their own stats.
  - Cannot manage plugin settings.

---

## Custom Post Types (CPTs) and Fields

### Contacts (formerly Leads)
#### Metafields
- Field Name: full_name | Label: Full Name | Type: Text | Options: -
- Field Name: first_name | Label: First Name | Type: Text | Options: -
- Field Name: last_name | Label: Last Name | Type: Text | Options: -
- Field Name: phone_number | Label: Phone Number | Type: Text | Options: -
- Field Name: whatsapp_number | Label: WhatsApp Number | Type: Text | Options: -
- Field Name: email | Label: Email | Type: Email | Options: -
- Field Name: nationality | Label: Nationality | Type: Dropdown | Options: Predefined list of countries
- Field Name: interest | Label: Interest | Type: Dropdown | Options: Buy, Sell, Rent, Invest, Commercial
- Field Name: property_type | Label: Property Type | Type: Dropdown | Options: Villa, Apartment, Townhouse, Offplan
- Field Name: area | Label: Area | Type: Text | Options: -
- Field Name: project_building | Label: Project/Building | Type: Text | Options: -
- Field Name: city | Label: City | Type: Text | Options: -
- Field Name: purchase_price | Label: Purchase Price | Type: Number | Options: -
- Field Name: purchase_date | Label: Purchase Date | Type: Date | Options: -
- Field Name: beds | Label: Bedrooms | Type: Number | Options: -
- Field Name: baths | Label: Bathrooms | Type: Number | Options: -
- Field Name: property_size | Label: Property Size | Type: Number | Options: -
- Field Name: contact_status | Label: Contact Status | Type: Dropdown | Options: Cold Lead, Warm Lead, Hot Lead, Customer, VIP Customer, Dead Lead
- Field Name: assigned_agent | Label: Assigned Agent | Type: Relation | Options: Linked Agent

#### Taxonomies
- Tags (can only be created by admin)

#### Actions
- Convert Contact to Property (only allowed when assigned to a Deal).

### Deals
#### Metafields
- Field Name: deal_name | Label: Deal Name | Type: Text | Options: -
- Field Name: associated_contacts | Label: Associated Contacts | Type: Relation | Options: Linked Contacts
- Field Name: associated_properties | Label: Associated Properties | Type: Relation | Options: Linked Properties
- Field Name: deal_stage | Label: Stage | Type: Dropdown | Options: New, In Progress, Won, Lost
- Field Name: deal_type | Label: Deal Type | Type: Dropdown | Options: Sale, Rental, Lease
- Field Name: asking_price | Label: Asking Price | Type: Number | Options: -
- Field Name: documents | Label: Documents | Type: File Upload | Options: Multiple

### Events
#### Metafields
- Field Name: event_title | Label: Event Title | Type: Text | Options: -
- Field Name: event_date | Label: Event Date | Type: Date | Options: -
- Field Name: event_status | Label: Status | Type: Dropdown | Options: Scheduled, Completed, Missed
- Field Name: linked_contact | Label: Linked Contact | Type: Relation | Options: Associated Contact
- Field Name: linked_agent | Label: Linked Agent | Type: Relation | Options: Associated Agent
- Field Name: event_type | Label: Event Type | Type: Dropdown | Options: Scheduled Calls, Follow Up Calls, Schedule Meeting, Actions, Task

### Activities (formerly Logs)
#### Metafields
- Field Name: activity_type | Label: Activity Type | Type: Dropdown | Options: Call, Meeting, Follow-Up
- Field Name: activity_date | Label: Activity Date | Type: Date | Options: -
- Field Name: activity_notes | Label: Notes | Type: Textarea | Options: -
- Field Name: activity_status | Label: Status | Type: Dropdown | Options: [List of all status options provided]
- Field Name: linked_contact | Label: Linked Contact | Type: Relation | Options: Associated Contact
- Field Name: linked_agent | Label: Linked Agent | Type: Relation | Options: Associated Agent (hidden)

### Properties
#### Metafields
- Field Name: address | Label: Address | Type: Text | Options: -
- Field Name: area | Label: Area | Type: Text | Options: -
- Field Name: property_type | Label: Property Type | Type: Dropdown | Options: Villa, Apartment, Townhouse, Offplan
- Field Name: purchase_price | Label: Purchase Price | Type: Number | Options: -
- Field Name: notes | Label: Notes | Type: Textarea | Options: -
- Field Name: status | Label: Status | Type: Dropdown | Options: Available, Sold
- Field Name: beds | Label: Bedrooms | Type: Number | Options: -
- Field Name: baths | Label: Bathrooms | Type: Number | Options: -
- Field Name: property_size | Label: Property Size | Type: Number | Options: -

### Users (Agents and Managers)
#### Metafields
- Field Name: first_name | Label: First Name | Type: Text | Options: -
- Field Name: last_name | Label: Last Name | Type: Text | Options: -
- Field Name: email | Label: Email | Type: Email | Options: -
- Field Name: profile_image | Label: Profile Image | Type: Image Upload | Options: -
- Field Name: bio | Label: Bio | Type: Textarea | Options: -
- Field Name: title | Label: Title | Type: Text | Options: -
- Field Name: whatsapp_number | Label: WhatsApp Number | Type: Text | Options: -
- Field Name: area_of_expertise | Label: Area of Expertise | Type: Text | Options: -
- Field Name: social_media | Label: Social Media | Type: Repeater | Options: Type (Facebook, Instagram, LinkedIn, TikTok, YouTube, Property Finder), URL
- Field Name: starting_date | Label: Starting Date | Type: Date | Options: - (Managers only)
- Field Name: documents | Label: Documents | Type: File Upload | Options: Multiple (Managers only)
- Field Name: hr_notes | Label: HR Notes | Type: Repeater | Options: Date, Note (Managers only)
- Field Name: sales_amount | Label: Amount in Sales | Type: Number | Options: - (Managers only)
- Field Name: calls_made | Label: Number of Calls | Type: Number | Options: - (Managers only)
- Field Name: listings_created | Label: Number of Listings | Type: Number | Options: - (Managers only)
- Field Name: commission_gained | Label: Commission Gained | Type: Number | Options: - (Managers only)

---

## Admin Page Breakdowns

### Dashboard
Features:
- Different sections for Admin, Manager, and Agent roles
- Manager upload function for CSV leads
- Calendar/list view widget for agents
- Note widget for agents
- List of leads and follow-up contacts in order of importance
- Analytics for agents and managers
- Manager view: In-depth analytics of each agent and as a group, filterable by time period
- Toggle switch for managers with dual roles (agent/manager)

### Contacts
Features:
- Table view, filterable by status and tags
- Single contact page (editable)
- Display fields, activities history, and relationship with events and deals
- Option to create events and deals from single contact page

### Deals
Features:
- Must be assigned to a contact to be saved
- Can be created from single contact page as a popup
- Popup appears automatically when Customer group status is chosen

### Activities
Features:
- Widget on single contact page
- "Add Activities" action
- Must be connected to an agent or contact

### Events
Features:
- Calendar view and table view
- Can be added by clicking on the calendar

### Users
Features:
- WordPress Users page with additional fields for agents and managers
- Profile editing for agents (social media, number, bio, image, area, title)

### Settings
- Admin access only
- Managers with dual roles can toggle between "My Contacts," specific agents, or ALL
- Toggle switch visible on every screen for agent/manager role switching

---

## Integration Points

### Third-party Integrations
Email Marketing Tools, Payment Gateways, Document Signing services, Property Finder

### WordPress Core Integration
User Management to sync with WordPress users; Media Library integration for property images

---

## Performance Considerations
Implement caching for frequently accessed data; Use transients for storing temporary data; Optimize database queries

---

## Security Measures
Implement nonce checks for all form submissions; Sanitize and validate all user inputs; Use WordPress capabilities system

---

## Localization
Ensure all user-facing strings are translatable; Provide a .pot file for translations

---

## Testing

### Unit Tests
Test individual functions and methods; Ensure proper data sanitization and validation

### Integration Tests
Test interactions between different modules; Verify API endpoint functionality

### User Acceptance Testing
Create test scenarios for each user role; Verify workflow completions (e.g., contact to deal conversion)

---

## UI/UX Considerations
- Customize WordPress admin menu to include all necessary pages
- Use WordPress admin notices for important messages
- Leverage WordPress metaboxes for organizing information on edit screens
- Utilize WordPress admin tables for list views with sorting and filtering capabilities

---

## Development Task List

### Plugin Structure and Setup
[ ] Create main plugin file (hero-hub-crm.php) with basic plugin information
[ ] Set up folder structure (includes, admin, assets)
[ ] Create a GitHub repository for version control

### Custom Post Types (CPTs)
[ ] Register Contacts CPT
[ ] Register Deals CPT
[ ] Register Events CPT
[ ] Register Activities CPT
[ ] Register Properties CPT

### User Roles and Capabilities
[ ] Define custom user roles (Admin, Manager, Agent)
[ ] Set up capabilities for each role
[ ] Create role management interface in WordPress admin

### Custom Fields and Metaboxes
[ ] Implement custom fields for Contacts
[ ] Implement custom fields for Deals
[ ] Implement custom fields for Events
[ ] Implement custom fields for Activities
[ ] Implement custom fields for Properties
[ ] Create metaboxes for additional information on edit screens

### Dashboard Development
[ ] Create main dashboard page in WordPress admin
[ ] Implement analytics widgets for Contacts
[ ] Implement analytics widgets for Deals
[ ] Implement analytics widgets for Activities
[ ] Add role-specific views (Admin, Manager, Agent)

### Contact Management
[ ] Create contact list view with filtering and sorting
[ ] Implement contact creation/edit form
[ ] Add functionality to convert contact to deal

### Deal Management
[ ] Create deal list view with filtering and sorting
[ ] Implement deal creation/edit form
[ ] Add relationship between deals and contacts

### Event Management
[ ] Create event list view with filtering and sorting
[ ] Implement event creation/edit form
[ ] Add calendar view for events

### Activity Logging
[ ] Implement activity logging system
[ ] Create activity list view with filtering
[ ] Add activity creation form on relevant pages (contacts, deals)

### Property Management
[ ] Create property list view with filtering and sorting
[ ] Implement property creation/edit form
[ ] Add relationship between properties and deals/contacts

### CSV Import/Export
[ ] Implement CSV import for contacts
[ ] Implement CSV export for contacts
[ ] Add error handling and validation for imports

### Reporting
[ ] Create basic reporting interface
[ ] Implement report generation for contacts
[ ] Implement report generation for deals
[ ] Add export functionality for reports

### UI/UX Enhancements
[ ] Style admin pages to match WordPress admin interface
[ ] Implement responsive design for all custom pages
[ ] Add AJAX functionality for smoother user experience

### Security and Optimization
[ ] Implement nonce checks on all forms
[ ] Add input sanitization and validation
[ ] Optimize database queries for performance

### Testing
[ ] Perform unit testing on core functions
[ ] Conduct integration testing between different modules
[ ] Carry out user acceptance testing with stakeholders

### Documentation
[ ] Write inline code documentation
[ ] Create a README file with setup instructions
[ ] Develop user guide for Admin, Manager, and Agent roles

### Final Steps
[ ] Perform final code review
[ ] Test plugin on a staging site
[ ] Prepare for deployment to production environment

---

**Development Notes**
- Ensure compatibility with WordPress coding standards.
- Maintain modularity for easy updates.
- Use WordPress REST API for future integrations.
- Follow the task list above for systematic development.

---

**End of Documentation**