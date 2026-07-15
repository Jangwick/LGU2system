<?php

use PHPUnit\Framework\TestCase;

class DocumentFunctionalTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $_POST = [];
        $_FILES = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'administrator'];

        $mockService = $this->getMockBuilder('DocumentService')
            ->disableOriginalConstructor()
            ->onlyMethods(['createDocument', 'updateDocument', 'deleteDocument'])
            ->getMock();

        $reflection = new ReflectionClass('DocumentController');
        $this->controller = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('documentService')->setValue($this->controller, $mockService);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
        $_SESSION = [];
    }

    public function testStoreValidatesRequiredFields()
    {
        $_POST = [
            'title' => '',
            'document_type' => '',
            'document_date' => '2026-07-15',
            'status' => 'draft'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $result = $this->controller->store();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('required', $result['error']);
    }

    public function testStoreSanitizesStatusBeforePublishing()
    {
        $_POST = [
            'title' => 'Ordinance 123',
            'document_type' => 'Ordinance',
            'document_date' => '2026-07-15',
            'status' => 'published'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Use reflection to call private method
        $method = new ReflectionMethod($this->controller, 'store');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No file uploaded', $result['error']);
    }
}
