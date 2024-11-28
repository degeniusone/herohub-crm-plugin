<?php
namespace HeroHub\CRM\Tests;

use HeroHub\CRM\Logger;
use WP_UnitTestCase;

class Test_Logger extends WP_UnitTestCase {
    private $logger;
    private $log_file;

    public function setUp(): void {
        parent::setUp();
        $this->logger = new Logger();
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/herohub-crm/logs/debug.log';
    }

    public function tearDown(): void {
        parent::tearDown();
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }

    public function test_log_creation() {
        $this->logger->log('Test message', 'INFO');
        $this->assertTrue(file_exists($this->log_file));
    }

    public function test_log_levels() {
        $levels = array('ERROR', 'WARNING', 'INFO', 'DEBUG');
        
        foreach ($levels as $level) {
            $message = "Test {$level} message";
            $this->logger->log($message, $level);
            
            $log_content = file_get_contents($this->log_file);
            $this->assertStringContainsString($level, $log_content);
            $this->assertStringContainsString($message, $log_content);
        }
    }

    public function test_error_email() {
        // Mock wp_mail
        add_filter('wp_mail', function($args) {
            $this->assertStringContainsString('Critical Error', $args['subject']);
            $this->assertStringContainsString('Test critical error', $args['message']);
            return false;
        });

        $this->logger->log('Test critical error', 'ERROR', true);
    }

    public function test_log_rotation() {
        // Write enough logs to trigger rotation
        for ($i = 0; $i < 1000; $i++) {
            $this->logger->log("Test message {$i}", 'INFO');
        }

        // Check if backup log exists
        $backup_log = $this->log_file . '.1';
        $this->assertTrue(file_exists($backup_log));

        // Clean up backup log
        unlink($backup_log);
    }
}
