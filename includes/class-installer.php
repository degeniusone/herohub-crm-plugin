<?php
namespace HeroHub\CRM;

/**
 * Plugin Installer Class
 * Handles database table creation and plugin initialization
 */
class Installer {
    /**
     * Run the installer
     */
    public static function install() {
        self::create_tables();
        self::create_roles();
        self::create_capabilities();
    }

    /**
     * Create custom database tables
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Activities Table
        $table_activities = $wpdb->prefix . 'herohub_activities';
        $sql_activities = "CREATE TABLE IF NOT EXISTS $table_activities (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            description text NOT NULL,
            time datetime DEFAULT CURRENT_TIMESTAMP,
            meta longtext,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY time (time)
        ) $charset_collate;";

        // Deals Table
        $table_deals = $wpdb->prefix . 'herohub_deals';
        $sql_deals = "CREATE TABLE IF NOT EXISTS $table_deals (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            property_id bigint(20) NOT NULL,
            agent_id bigint(20) NOT NULL,
            manager_id bigint(20) NOT NULL,
            client_id bigint(20) NOT NULL,
            amount decimal(15,2) NOT NULL,
            commission_amount decimal(15,2) NOT NULL,
            status varchar(50) NOT NULL,
            creation_date datetime DEFAULT CURRENT_TIMESTAMP,
            completion_date datetime DEFAULT NULL,
            meta longtext,
            PRIMARY KEY  (id),
            KEY property_id (property_id),
            KEY agent_id (agent_id),
            KEY manager_id (manager_id),
            KEY client_id (client_id),
            KEY status (status),
            KEY creation_date (creation_date),
            KEY completion_date (completion_date)
        ) $charset_collate;";

        // Leads Table
        $table_leads = $wpdb->prefix . 'herohub_leads';
        $sql_leads = "CREATE TABLE IF NOT EXISTS $table_leads (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            agent_id bigint(20) NOT NULL,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50),
            source varchar(50) NOT NULL,
            property_type varchar(50) NOT NULL,
            budget decimal(15,2),
            status varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            meta longtext,
            PRIMARY KEY  (id),
            KEY agent_id (agent_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Tasks Table
        $table_tasks = $wpdb->prefix . 'herohub_tasks';
        $sql_tasks = "CREATE TABLE IF NOT EXISTS $table_tasks (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            agent_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            description text NOT NULL,
            priority varchar(20) NOT NULL,
            due_date datetime NOT NULL,
            completed tinyint(1) DEFAULT 0,
            completed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            meta longtext,
            PRIMARY KEY  (id),
            KEY agent_id (agent_id),
            KEY type (type),
            KEY priority (priority),
            KEY due_date (due_date),
            KEY completed (completed)
        ) $charset_collate;";

        // SMS logs Table
        $sql_sms_logs = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}herohub_crm_sms_log (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            phone_number varchar(20) NOT NULL,
            message text NOT NULL,
            status varchar(20) NOT NULL,
            error text,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY phone_number (phone_number),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Create score log table
        $sql_score_log = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}herohub_crm_score_log (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) NOT NULL,
            score int(11) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id),
            KEY score (score),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_activities);
        dbDelta($sql_deals);
        dbDelta($sql_leads);
        dbDelta($sql_tasks);
        dbDelta($sql_sms_logs);
        dbDelta($sql_score_log);

        update_option('herohub_crm_db_version', HEROHUB_CRM_VERSION);
    }

    /**
     * Create custom user roles
     */
    private static function create_roles() {
        add_role(
            'real_estate_manager',
            __('Real Estate Manager', 'herohub-crm'),
            array(
                'read' => true,
                'manage_real_estate' => true,
                'edit_real_estate' => true,
                'delete_real_estate' => true,
                'manage_agents' => true,
            )
        );

        add_role(
            'real_estate_agent',
            __('Real Estate Agent', 'herohub-crm'),
            array(
                'read' => true,
                'edit_real_estate' => true,
                'manage_leads' => true,
            )
        );
    }

    /**
     * Create custom capabilities
     */
    private static function create_capabilities() {
        $admin = get_role('administrator');
        
        // Add manager capabilities to admin
        $manager_caps = array(
            'manage_real_estate',
            'edit_real_estate',
            'delete_real_estate',
            'manage_agents',
        );

        foreach ($manager_caps as $cap) {
            $admin->add_cap($cap);
        }

        // Add agent capabilities to admin
        $agent_caps = array(
            'edit_real_estate',
            'manage_leads',
        );

        foreach ($agent_caps as $cap) {
            $admin->add_cap($cap);
        }
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }

        global $wpdb;

        // Drop custom tables
        $tables = array(
            'herohub_activities',
            'herohub_deals',
            'herohub_leads',
            'herohub_tasks',
            'herohub_crm_sms_log',
            'herohub_crm_score_log',
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }

        // Remove roles
        remove_role('real_estate_manager');
        remove_role('real_estate_agent');

        // Remove capabilities from admin
        $admin = get_role('administrator');
        $caps = array(
            'manage_real_estate',
            'edit_real_estate',
            'delete_real_estate',
            'manage_agents',
            'manage_leads',
        );

        foreach ($caps as $cap) {
            $admin->remove_cap($cap);
        }

        // Delete options
        delete_option('herohub_crm_db_version');
    }
}
