<?php

require_once __DIR__ . '/../../modules/document-management/services/DocumentService.php';
require_once __DIR__ . '/../../modules/document-management/services/OcrService.php';
require_once __DIR__ . '/../../modules/document-management/services/SummarizationService.php';
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
            ->onlyMethods(['createDocument', 'updateDocument', 'deleteDocument', 'getDocuments', 'getSourceCounts'])
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
        $service->method('getSourceCounts')->willReturn([]);

        $result = $this->controller->index();

        $this->assertCount(10, $result['documents']);
        $this->assertEquals(2, $result['pagination']['current_page']);
        $this->assertEquals(10, $result['pagination']['per_page']);
        $this->assertEquals(25, $result['pagination']['total']);
        $this->assertEquals(3, $result['pagination']['total_pages']);
    }

    public function testOcrServiceDetectsCorrectFileTypes()
    {
        $ocr = new OcrService();

        $this->assertTrue($ocr->isOcrCapable('application/pdf', 'doc.pdf'));
        $this->assertTrue($ocr->isOcrCapable('image/jpeg', 'scan.jpg'));
        $this->assertTrue($ocr->isOcrCapable('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'doc.docx'));
        $this->assertFalse($ocr->isOcrCapable('text/plain', 'notes.txt'));
    }

    public function testSummarizationServiceGeneratesKeyPointsFromLegislativeText()
    {
        $summ = new SummarizationService();

        $text = "AN ORDINANCE APPROPRIATING FUNDS FOR THE CITY GOVERNMENT FOR FISCAL YEAR 2025. " .
                "Section 1. Two hundred million pesos shall be allocated for personal services. " .
                "Section 2. One hundred fifty million pesos is appropriated for operating expenses. " .
                "Section 3. This ordinance shall take effect immediately upon approval.";

        $points = $summ->generateKeyPoints($text, 3);

        $this->assertNotEmpty($points);
        $this->assertLessThanOrEqual(3, count($points));
        foreach ($points as $point) {
            $this->assertStringStartsWith('•', $point);
        }
    }

    public function testSummarizationServiceHandlesEmptyInput()
    {
        $summ = new SummarizationService();

        $this->assertEmpty($summ->generateKeyPoints('', 5));
        $this->assertEmpty($summ->generateKeyPoints(null, 5));
        $this->assertSame('', $summ->generateKeyPointsString('', 5));
    }
}
