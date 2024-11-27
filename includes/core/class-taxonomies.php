<?php
namespace HeroHub\CRM\Core;

class Taxonomies {
    public function register() {
        $this->register_contact_status_taxonomy();
        $this->register_deal_status_taxonomy();
        $this->register_property_type_taxonomy();
        $this->register_activity_type_taxonomy();
    }

    private function register_contact_status_taxonomy() {
        $labels = array(
            'name'              => __('Contact Statuses', 'herohub-crm'),
            'singular_name'     => __('Contact Status', 'herohub-crm'),
            'search_items'      => __('Search Contact Statuses', 'herohub-crm'),
            'all_items'         => __('All Contact Statuses', 'herohub-crm'),
            'edit_item'         => __('Edit Contact Status', 'herohub-crm'),
            'update_item'       => __('Update Contact Status', 'herohub-crm'),
            'add_new_item'      => __('Add New Contact Status', 'herohub-crm'),
            'new_item_name'     => __('New Contact Status Name', 'herohub-crm'),
            'menu_name'         => __('Contact Status', 'herohub-crm'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'contact-status'),
        );

        register_taxonomy('contact_status', array('contact'), $args);

        // Add default terms
        $default_terms = array('New', 'Qualified', 'Not Qualified', 'Customer');
        foreach ($default_terms as $term) {
            if (!term_exists($term, 'contact_status')) {
                wp_insert_term($term, 'contact_status');
            }
        }
    }

    private function register_deal_status_taxonomy() {
        $labels = array(
            'name'              => __('Deal Statuses', 'herohub-crm'),
            'singular_name'     => __('Deal Status', 'herohub-crm'),
            'search_items'      => __('Search Deal Statuses', 'herohub-crm'),
            'all_items'         => __('All Deal Statuses', 'herohub-crm'),
            'edit_item'         => __('Edit Deal Status', 'herohub-crm'),
            'update_item'       => __('Update Deal Status', 'herohub-crm'),
            'add_new_item'      => __('Add New Deal Status', 'herohub-crm'),
            'new_item_name'     => __('New Deal Status Name', 'herohub-crm'),
            'menu_name'         => __('Deal Status', 'herohub-crm'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'deal-status'),
        );

        register_taxonomy('deal_status', array('deal'), $args);

        // Add default terms
        $default_terms = array('New', 'In Progress', 'Negotiation', 'Won', 'Lost');
        foreach ($default_terms as $term) {
            if (!term_exists($term, 'deal_status')) {
                wp_insert_term($term, 'deal_status');
            }
        }
    }

    private function register_property_type_taxonomy() {
        $labels = array(
            'name'              => __('Property Types', 'herohub-crm'),
            'singular_name'     => __('Property Type', 'herohub-crm'),
            'search_items'      => __('Search Property Types', 'herohub-crm'),
            'all_items'         => __('All Property Types', 'herohub-crm'),
            'edit_item'         => __('Edit Property Type', 'herohub-crm'),
            'update_item'       => __('Update Property Type', 'herohub-crm'),
            'add_new_item'      => __('Add New Property Type', 'herohub-crm'),
            'new_item_name'     => __('New Property Type Name', 'herohub-crm'),
            'menu_name'         => __('Property Type', 'herohub-crm'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-type'),
        );

        register_taxonomy('property_type', array('property'), $args);

        // Add default terms
        $default_terms = array('Residential', 'Commercial', 'Industrial', 'Land');
        foreach ($default_terms as $term) {
            if (!term_exists($term, 'property_type')) {
                wp_insert_term($term, 'property_type');
            }
        }
    }

    private function register_activity_type_taxonomy() {
        $labels = array(
            'name'              => __('Activity Types', 'herohub-crm'),
            'singular_name'     => __('Activity Type', 'herohub-crm'),
            'search_items'      => __('Search Activity Types', 'herohub-crm'),
            'all_items'         => __('All Activity Types', 'herohub-crm'),
            'edit_item'         => __('Edit Activity Type', 'herohub-crm'),
            'update_item'       => __('Update Activity Type', 'herohub-crm'),
            'add_new_item'      => __('Add New Activity Type', 'herohub-crm'),
            'new_item_name'     => __('New Activity Type Name', 'herohub-crm'),
            'menu_name'         => __('Activity Type', 'herohub-crm'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'activity-type'),
        );

        register_taxonomy('activity_type', array('activity'), $args);

        // Add default terms
        $default_terms = array('Call', 'Meeting', 'Follow-Up', 'Task');
        foreach ($default_terms as $term) {
            if (!term_exists($term, 'activity_type')) {
                wp_insert_term($term, 'activity_type');
            }
        }
    }
}
