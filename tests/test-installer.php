<?php
namespace HeroHub\CRM\Tests;

use HeroHub\CRM\Installer;
use WP_UnitTestCase;

class Test_Installer extends WP_UnitTestCase {
    private $installer;

    public function setUp(): void {
        parent::setUp();
        $this->installer = new Installer();
    }

    public function test_tables_created() {
        global $wpdb;

        // Run the installer
        Installer::install();

        // Check if tables exist
        $tables = array(
            'herohub_activities',
            'herohub_deals',
            'herohub_leads',
            'herohub_tasks',
        );

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $this->assertTrue(
                $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name,
                "Table $table_name does not exist"
            );
        }
    }

    public function test_roles_created() {
        // Run the installer
        Installer::install();

        // Check if roles exist
        $this->assertTrue(wp_roles()->is_role('real_estate_manager'));
        $this->assertTrue(wp_roles()->is_role('real_estate_agent'));
    }

    public function test_capabilities_added() {
        // Run the installer
        Installer::install();

        // Get admin role
        $admin = get_role('administrator');

        // Check manager capabilities
        $manager_caps = array(
            'manage_real_estate',
            'edit_real_estate',
            'delete_real_estate',
            'manage_agents',
        );

        foreach ($manager_caps as $cap) {
            $this->assertTrue($admin->has_cap($cap));
        }

        // Check agent capabilities
        $agent_caps = array(
            'edit_real_estate',
            'manage_leads',
        );

        foreach ($agent_caps as $cap) {
            $this->assertTrue($admin->has_cap($cap));
        }
    }

    public function test_uninstall() {
        global $wpdb;

        // First install
        Installer::install();

        // Then uninstall
        Installer::uninstall();

        // Check if tables are removed
        $tables = array(
            'herohub_activities',
            'herohub_deals',
            'herohub_leads',
            'herohub_tasks',
        );

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $this->assertFalse(
                $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name,
                "Table $table_name still exists"
            );
        }

        // Check if roles are removed
        $this->assertFalse(wp_roles()->is_role('real_estate_manager'));
        $this->assertFalse(wp_roles()->is_role('real_estate_agent'));

        // Check if capabilities are removed from admin
        $admin = get_role('administrator');
        $caps = array(
            'manage_real_estate',
            'edit_real_estate',
            'delete_real_estate',
            'manage_agents',
            'manage_leads',
        );

        foreach ($caps as $cap) {
            $this->assertFalse($admin->has_cap($cap));
        }
    }
}
