# HeroHub CRM Plugin Documentation

## Project Overview
HeroHub CRM is a comprehensive WordPress plugin designed for real estate professionals to manage their contacts, properties, deals, and activities.

## Project Structure
```
herohub_plugin/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── admin/
│   └── core/
└── templates/
```

## Version Control
The project uses Git for version control. Important branches:
- main: Production-ready code
- develop: Development and integration
- feature/*: New features

## Task Tracking

### Core Features
- [x] Custom Post Types
  - [x] Contacts
  - [x] Deals
  - [x] Properties
  - [x] Events
  - [x] Activities

- [x] User Roles and Permissions
  - [x] Administrator role
  - [x] Manager role
  - [x] Agent role
  - [x] Role management interface
  - [x] Role-based access control

- [ ] Dashboard Views
  - [ ] Admin dashboard
  - [ ] Manager dashboard
  - [ ] Agent dashboard
  - [ ] Analytics widgets

- [ ] List Management
  - [ ] Contact list with filtering
  - [ ] Deal list with filtering
  - [ ] Property list with filtering
  - [ ] Event calendar view
  - [ ] Activity log view

- [ ] Data Management
  - [ ] CSV import/export
  - [ ] Report generation
  - [ ] Data relationships
  - [ ] Bulk operations

### Technical Tasks
- [x] Project setup
- [x] Version control setup
- [x] Documentation
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Testing
  - [ ] Unit tests
  - [ ] Integration tests
  - [ ] User acceptance testing

## Custom Fields

### Contact Fields
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

### Deal Fields
- Field Name: deal_name | Label: Deal Name | Type: Text | Options: -
- Field Name: associated_contacts | Label: Associated Contacts | Type: Relation | Options: Linked Contacts
- Field Name: associated_properties | Label: Associated Properties | Type: Relation | Options: Linked Properties
- Field Name: deal_stage | Label: Stage | Type: Dropdown | Options: New, In Progress, Won, Lost
- Field Name: deal_type | Label: Deal Type | Type: Dropdown | Options: Sale, Rental, Lease
- Field Name: asking_price | Label: Asking Price | Type: Number | Options: -
- Field Name: documents | Label: Documents | Type: File Upload | Options: Multiple

### Property Fields
- Field Name: address | Label: Address | Type: Text | Options: -
- Field Name: area | Label: Area | Type: Text | Options: -
- Field Name: property_type | Label: Property Type | Type: Dropdown | Options: Villa, Apartment, Townhouse, Offplan
- Field Name: purchase_price | Label: Purchase Price | Type: Number | Options: -
- Field Name: notes | Label: Notes | Type: Textarea | Options: -
- Field Name: status | Label: Status | Type: Dropdown | Options: Available, Sold
- Field Name: beds | Label: Bedrooms | Type: Number | Options: -
- Field Name: baths | Label: Bathrooms | Type: Number | Options: -
- Field Name: property_size | Label: Property Size | Type: Number | Options: -

### Event Fields
- Field Name: event_title | Label: Event Title | Type: Text | Options: -
- Field Name: event_date | Label: Event Date | Type: Date | Options: -
- Field Name: event_status | Label: Status | Type: Dropdown | Options: Scheduled, Completed, Missed
- Field Name: linked_contact | Label: Linked Contact | Type: Relation | Options: Associated Contact
- Field Name: linked_agent | Label: Linked Agent | Type: Relation | Options: Associated Agent
- Field Name: event_type | Label: Event Type | Type: Dropdown | Options: Scheduled Calls, Follow Up Calls, Schedule Meeting, Actions, Task

### Activity Fields
- Field Name: activity_type | Label: Activity Type | Type: Dropdown | Options: Call, Meeting, Follow-Up
- Field Name: activity_date | Label: Activity Date | Type: Date | Options: -
- Field Name: activity_notes | Label: Notes | Type: Textarea | Options: -
- Field Name: activity_status | Label: Status | Type: Dropdown | Options: [List of all status options provided]
- Field Name: linked_contact | Label: Linked Contact | Type: Relation | Options: Associated Contact
- Field Name: linked_agent | Label: Linked Agent | Type: Relation | Options: Associated Agent (hidden)

### Manager Fields
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

## Integration Points

### Third-party Integrations
Email Marketing Tools, Payment Gateways, Document Signing services, Property Finder

### WordPress Core Integration
User Management to sync with WordPress users; Media Library integration for property images

## Performance Considerations
Implement caching for frequently accessed data; Use transients for storing temporary data; Optimize database queries

## Security Measures
Implement nonce checks for all form submissions; Sanitize and validate all user inputs; Use WordPress capabilities system

## Localization
Ensure all user-facing strings are translatable; Provide a .pot file for translations

## Testing

### Unit Tests
Test individual functions and methods; Ensure proper data sanitization and validation

### Integration Tests
Test interactions between different modules; Verify API endpoint functionality

### User Acceptance Testing
Create test scenarios for each user role; Verify workflow completions (e.g., contact to deal conversion)

## UI/UX Considerations
- Customize WordPress admin menu to include all necessary pages
- Use WordPress admin notices for important messages
- Leverage WordPress metaboxes for organizing information on edit screens
- Utilize WordPress admin tables for list views with sorting and filtering capabilities

## Development Notes
- Ensure compatibility with WordPress coding standards.
- Maintain modularity for easy updates.
- Use WordPress REST API for future integrations.
- Follow the task list above for systematic development.