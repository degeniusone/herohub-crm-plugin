<?php
namespace HeroHub\CRM\Tests;

use HeroHub\CRM\Core\SMS_Manager;
use WP_UnitTestCase;

class Test_SMS_Manager extends WP_UnitTestCase {
    private $sms_manager;

    public function setUp(): void {
        parent::setUp();
        $this->sms_manager = new SMS_Manager();
    }

    public function test_format_phone_number() {
        $reflection = new \ReflectionClass($this->sms_manager);
        $method = $reflection->getMethod('format_phone_number');
        $method->setAccessible(true);

        // Test formatting with 10 digits (US number)
        $this->assertEquals('+11234567890', $method->invoke($this->sms_manager, '1234567890'));
        
        // Test formatting with country code
        $this->assertEquals('+11234567890', $method->invoke($this->sms_manager, '+11234567890'));
        
        // Test formatting with special characters
        $this->assertEquals('+11234567890', $method->invoke($this->sms_manager, '(123) 456-7890'));
    }

    public function test_validate_phone_number() {
        $reflection = new \ReflectionClass($this->sms_manager);
        $method = $reflection->getMethod('validate_phone_number');
        $method->setAccessible(true);

        // Test valid numbers
        $this->assertTrue($method->invoke($this->sms_manager, '+11234567890'));
        $this->assertTrue($method->invoke($this->sms_manager, '+447911123456'));
        
        // Test invalid numbers
        $this->assertFalse($method->invoke($this->sms_manager, '1234567890')); // No plus
        $this->assertFalse($method->invoke($this->sms_manager, '+1123')); // Too short
        $this->assertFalse($method->invoke($this->sms_manager, '+11234567890123456')); // Too long
    }

    public function test_send_message_without_provider() {
        $result = $this->sms_manager->send_message('+11234567890', 'Test message');
        $this->assertWPError($result);
        $this->assertEquals('no_provider', $result->get_error_code());
    }

    public function test_send_message_with_invalid_number() {
        $result = $this->sms_manager->send_message('invalid', 'Test message');
        $this->assertWPError($result);
        $this->assertEquals('invalid_phone', $result->get_error_code());
    }

    public function test_send_bulk_messages() {
        $recipients = array(
            '+11234567890',
            '+12345678901'
        );
        
        $results = $this->sms_manager->send_bulk_messages($recipients, 'Test message');
        
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        
        foreach ($results as $result) {
            $this->assertWPError($result);
        }
    }

    public function test_message_logging() {
        global $wpdb;
        
        // Send a test message (will fail without provider)
        $this->sms_manager->send_message('+11234567890', 'Test message');
        
        // Check if log entry was created
        $log = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}herohub_crm_sms_log 
                WHERE phone_number = %s AND message = %s",
                '+11234567890',
                'Test message'
            )
        );
        
        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertNotEmpty($log->error);
    }
}
