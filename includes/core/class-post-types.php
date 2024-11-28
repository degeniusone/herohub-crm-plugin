<?php
namespace HeroHub\CRM\Core;

class Post_Types {
    public function register() {
        $this->register_contact_post_type();
        $this->register_deal_post_type();
        $this->register_event_post_type();
        $this->register_property_post_type();
        $this->register_activity_post_type();
    }

    private function register_contact_post_type() {
        $labels = array(
            'name'               => __('Contacts', 'herohub-crm'),
            'singular_name'      => __('Contact', 'herohub-crm'), 
            'menu_name'          => __('Contacts', 'herohub-crm'),
            'add_new'            => __('Add New Contact', 'herohub-crm'),
            'add_new_item'       => __('Add New Contact', 'herohub-crm'),
            'edit_item'          => __('Edit Contact', 'herohub-crm'),
            'new_item'           => __('New Contact', 'herohub-crm'),
            'view_item'          => __('View Contact', 'herohub-crm'),
            'search_items'       => __('Search Contacts', 'herohub-crm'),
            'not_found'          => __('No contacts found', 'herohub-crm'),
            'not_found_in_trash' => __('No contacts found in Trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'contact'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-businessperson',
            'supports'           => array('title', 'thumbnail', 'custom-fields'),
        );

        register_post_type('contact', $args);
    }

    private function register_deal_post_type() {
        $labels = array(
            'name'               => __('Deals', 'herohub-crm'),
            'singular_name'      => __('Deal', 'herohub-crm'),
            'menu_name'          => __('Deals', 'herohub-crm'),
            'add_new'            => __('Add New Deal', 'herohub-crm'),
            'add_new_item'       => __('Add New Deal', 'herohub-crm'),
            'edit_item'          => __('Edit Deal', 'herohub-crm'),
            'new_item'           => __('New Deal', 'herohub-crm'),
            'view_item'          => __('View Deal', 'herohub-crm'),
            'search_items'       => __('Search Deals', 'herohub-crm'),
            'not_found'          => __('No deals found', 'herohub-crm'),
            'not_found_in_trash' => __('No deals found in Trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'deal'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-money-alt',
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
        );

        register_post_type('deal', $args);
    }

    private function register_event_post_type() {
        $labels = array(
            'name'               => __('Events', 'herohub-crm'),
            'singular_name'      => __('Event', 'herohub-crm'),
            'menu_name'          => __('Events', 'herohub-crm'),
            'add_new'            => __('Add New Event', 'herohub-crm'),
            'add_new_item'       => __('Add New Event', 'herohub-crm'),
            'edit_item'          => __('Edit Event', 'herohub-crm'),
            'new_item'           => __('New Event', 'herohub-crm'),
            'view_item'          => __('View Event', 'herohub-crm'),
            'search_items'       => __('Search Events', 'herohub-crm'),
            'not_found'          => __('No events found', 'herohub-crm'),
            'not_found_in_trash' => __('No events found in Trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'event'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 7,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title', 'editor', 'custom-fields'),
        );

        register_post_type('event', $args);
    }

    private function register_property_post_type() {
        $labels = array(
            'name'               => __('Properties', 'herohub-crm'),
            'singular_name'      => __('Property', 'herohub-crm'),
            'menu_name'          => __('Properties', 'herohub-crm'),
            'add_new'            => __('Add New Property', 'herohub-crm'),
            'add_new_item'       => __('Add New Property', 'herohub-crm'),
            'edit_item'          => __('Edit Property', 'herohub-crm'),
            'new_item'           => __('New Property', 'herohub-crm'),
            'view_item'          => __('View Property', 'herohub-crm'),
            'search_items'       => __('Search Properties', 'herohub-crm'),
            'not_found'          => __('No properties found', 'herohub-crm'),
            'not_found_in_trash' => __('No properties found in Trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'property'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 8,
            'menu_icon'          => 'dashicons-admin-home',
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
        );

        register_post_type('property', $args);
    }

    private function register_activity_post_type() {
        $labels = array(
            'name'               => __('Activities', 'herohub-crm'),
            'singular_name'      => __('Activity', 'herohub-crm'),
            'menu_name'          => __('Activities', 'herohub-crm'),
            'add_new'            => __('Add New Activity', 'herohub-crm'),
            'add_new_item'       => __('Add New Activity', 'herohub-crm'),
            'edit_item'          => __('Edit Activity', 'herohub-crm'),
            'new_item'           => __('New Activity', 'herohub-crm'),
            'view_item'          => __('View Activity', 'herohub-crm'),
            'search_items'       => __('Search Activities', 'herohub-crm'),
            'not_found'          => __('No activities found', 'herohub-crm'),
            'not_found_in_trash' => __('No activities found in Trash', 'herohub-crm'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'activity'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 9,
            'menu_icon'          => 'dashicons-list-view',
            'supports'           => array('title', 'editor', 'custom-fields'),
        );

        register_post_type('activity', $args);
    }
}
