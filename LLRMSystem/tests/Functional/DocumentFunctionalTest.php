<?php

require_once __DIR__ . '/../../modules/document-management/services/DocumentService.php';
require_once __DIR__ . '/../../modules/document-management/controllers/DocumentController.php';

use PHPUnit\Framework\TestCase;

class DocumentFunctionalTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'administrator'];

        $mockService = $this->getMockBuilder('DocumentService')
            ->disableOriginalConstructor()
            ->onlyMethods(['createDocument', 'updateDocument', 'deleteDocument', 'getDocuments'])
            ->getMock();

        $reflection = new ReflectionClass('DocumentController');
        $this->controller = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('documentService')->setValue($this->controller, $mockService);
    }

    protected function tearDown(): void
    {
        $_GET = [];
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
        $_FILES = [
            'document' => ['error' => UPLOAD_ERR_OK, 'tmp_name' => '', 'name' => 'test.txt', 'size' => 0, 'type' => 'text/plain']
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

    public function testIndexPaginatesWithTenPerPage()
    {
        $_GET = ['page' => 2, 'per_page' => 10];

        $expected = [
            'documents' => array_fill(0, 10, ['id' => 1, 'title' => 'Test Document']),
            'pagination' => [
                'current_page' => 2,
                'per_page' => 10,
                'total' => 25,
                'total_pages' => 3
            ]
        ];

        $reflection = new ReflectionClass($this->controller);
        $service = $reflection->getProperty('documentService')->getValue($this->controller);
        $service->method('getDocuments')->willReturn($expected);

        $result = $this->controller->index();

        $this->assertCount(10, $result['documents']);
        $this->assertEquals(2, $result['pagination']['current_page']);
        $this->assertEquals(10, $result['pagination']['per_page']);
        $this->assertEquals(25, $result['pagination']['total']);
        $this->assertEquals(3, $result['pagination']['total_pages']);
    }
}
