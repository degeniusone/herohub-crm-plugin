<?php
namespace HeroHub\CRM\Tests;

use HeroHub\CRM\Error_Handler;
use WP_UnitTestCase;

class Test_Error_Handler extends WP_UnitTestCase {
    private $error_handler;

    public function setUp(): void {
        parent::setUp();
        $this->error_handler = $this->getMockForTrait(Error_Handler::class);
    }

    public function test_required_fields_validation() {
        $data = array(
            'name' => 'John Doe',
            'email' => '',
            'phone' => '1234567890'
        );

        $required = array('name', 'email');
        $result = $this->error_handler->validate_required_fields($data, $required);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_email_validation() {
        $valid_email = 'test@example.com';
        $invalid_email = 'invalid-email';

        $this->assertTrue($this->error_handler->validate_email($valid_email));
        $this->assertFalse($this->error_handler->validate_email($invalid_email));
    }

    public function test_numeric_validation() {
        $valid_number = '12345';
        $invalid_number = 'abc123';

        $this->assertTrue($this->error_handler->validate_numeric($valid_number));
        $this->assertFalse($this->error_handler->validate_numeric($invalid_number));
    }

    public function test_date_validation() {
        $valid_date = '2023-12-31';
        $invalid_date = '2023-13-45';

        $this->assertTrue($this->error_handler->validate_date($valid_date));
        $this->assertFalse($this->error_handler->validate_date($invalid_date));
    }

    public function test_sanitize_input() {
        $input = array(
            'name' => ' John Doe ',
            'email' => 'TEST@EXAMPLE.COM',
            'website' => 'http://example.com',
            'description' => '<script>alert("XSS")</script>Test description'
        );

        $sanitized = $this->error_handler->sanitize_input($input);

        $this->assertEquals('John Doe', $sanitized['name']);
        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertEquals('http://example.com', $sanitized['website']);
        $this->assertEquals('Test description', $sanitized['description']);
    }

    public function test_ajax_error_response() {
        $error = 'Test error message';
        $response = $this->error_handler->ajax_error_response($error);

        $this->assertFalse($response['success']);
        $this->assertEquals($error, $response['error']);
    }

    public function test_collect_errors() {
        $this->error_handler->add_error('field1', 'Error 1');
        $this->error_handler->add_error('field2', 'Error 2');

        $errors = $this->error_handler->get_errors();

        $this->assertCount(2, $errors);
        $this->assertEquals('Error 1', $errors['field1']);
        $this->assertEquals('Error 2', $errors['field2']);
    }
}
