<?php

require_once __DIR__ . '/../../../modules/document-management/models/Document.php';
require_once __DIR__ . '/../../../modules/search/services/SearchService.php';

class OcrDatabaseIntegrationTest extends DatabaseTestCase
{
    private $documentModel;
    private $searchService;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentModel = new Document(self::$db);
        $this->searchService = new SearchService(self::$db);
    }

    // --- Column existence ---

    public function testOcrStatusColumnExists()
    {
        $stmt = self::$db->query("SHOW COLUMNS FROM legislative_documents LIKE 'ocr_status'");
        $this->assertNotFalse($stmt->fetch());
    }

    public function testExtractedTextColumnExists()
    {
        $stmt = self::$db->query("SHOW COLUMNS FROM legislative_documents LIKE 'extracted_text'");
        $this->assertNotFalse($stmt->fetch());
    }

    public function testKeyPointsColumnExists()
    {
        $stmt = self::$db->query("SHOW COLUMNS FROM legislative_documents LIKE 'key_points'");
        $this->assertNotFalse($stmt->fetch());
    }

    public function testOcrProcessedAtColumnExists()
    {
        $stmt = self::$db->query("SHOW COLUMNS FROM legislative_documents LIKE 'ocr_processed_at'");
        $this->assertNotFalse($stmt->fetch());
    }

    public function testKeyPointsGeneratedAtColumnExists()
    {
        $stmt = self::$db->query("SHOW COLUMNS FROM legislative_documents LIKE 'key_points_generated_at'");
        $this->assertNotFalse($stmt->fetch());
    }

    // --- Document model OCR methods ---

    public function testCreateDocumentWithOcrFields()
    {
        $docId = $this->insertTestDocument([
            'ocr_status' => 'completed',
            'extracted_text' => 'This is OCR extracted text for testing.',
            'key_points' => "• Point one\n• Point two",
        ]);

        $this->assertNotEmpty($docId);
        $doc = $this->documentModel->getById($docId);

        $this->assertEquals('completed', $doc['ocr_status']);
        $this->assertEquals('This is OCR extracted text for testing.', $doc['extracted_text']);
        $this->assertStringContainsString('Point one', $doc['key_points']);
    }

    public function testUpdateOcrResult()
    {
        $docId = $this->insertTestDocument(['ocr_status' => 'pending']);
        $this->documentModel->updateOcrResult($docId, 'completed', 'OCR text from update.');

        $doc = $this->documentModel->getById($docId);
        $this->assertEquals('completed', $doc['ocr_status']);
        $this->assertEquals('OCR text from update.', $doc['extracted_text']);
        $this->assertNotNull($doc['ocr_processed_at']);
    }

    public function testUpdateKeyPoints()
    {
        $docId = $this->insertTestDocument(['ocr_status' => 'completed']);
        $this->documentModel->updateKeyPoints($docId, "• Key point A\n• Key point B");

        $doc = $this->documentModel->getById($docId);
        $this->assertEquals("• Key point A\n• Key point B", $doc['key_points']);
        $this->assertNotNull($doc['key_points_generated_at']);
    }

    public function testGetPendingOcrReturnsOnlyPending()
    {
        $pendingId = $this->insertTestDocument(['ocr_status' => 'pending']);
        $completedId = $this->insertTestDocument(['ocr_status' => 'completed']);

        // Verify documents were created with correct ocr_status
        $pendingDoc = $this->documentModel->getById($pendingId);
        $this->assertEquals('pending', $pendingDoc['ocr_status'], 'Pending doc should have ocr_status=pending');
        $this->assertNull($pendingDoc['deleted_at'], 'Pending doc should have deleted_at=NULL');

        // Use large limit to include our test doc (DB may have many pre-existing pending docs)
        $pending = $this->documentModel->getPendingOcr(1000);
        $pendingIds = array_map('strval', array_column($pending, 'id'));

        $this->assertContains((string)$pendingId, $pendingIds);
        $this->assertNotContains((string)$completedId, $pendingIds);
    }

    public function testGetPendingOcrRespectsLimit()
    {
        $this->insertTestDocument(['ocr_status' => 'pending']);
        $this->insertTestDocument(['ocr_status' => 'pending']);
        $this->insertTestDocument(['ocr_status' => 'pending']);

        $pending = $this->documentModel->getPendingOcr(2);
        $this->assertLessThanOrEqual(2, count($pending));
    }

    // --- Search integration ---

    public function testSearchIncludesExtractedText()
    {
        $uniqueTerm = 'ZXCQR_TEST_OCR_SEARCH_TERM_' . uniqid();
        $docId = $this->insertTestDocument([
            'ocr_status' => 'completed',
            'extracted_text' => 'This document contains the unique term ' . $uniqueTerm . ' for search testing.',
        ]);

        $results = $this->searchService->search($uniqueTerm, ['limit' => 100, 'status' => 'approved']);
        $resultIds = array_map('strval', array_column($results, 'id'));

        $this->assertContains((string)$docId, $resultIds);
    }

    public function testGetCountIncludesExtractedText()
    {
        $uniqueTerm = 'ZXCQR_COUNT_OCR_TERM_' . uniqid();
        $docId = $this->insertTestDocument([
            'ocr_status' => 'completed',
            'extracted_text' => 'Count test with ' . $uniqueTerm . ' inside.',
        ]);

        $count = $this->searchService->getCount($uniqueTerm, ['status' => 'approved']);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    // --- Helpers ---

    private function insertTestDocument(array $overrides = [])
    {
        $refNum = 'TEST-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $data = array_merge([
            'reference_number' => $refNum,
            'title' => 'Test OCR Document ' . uniqid(),
            'document_type' => 'ordinance',
            'document_date' => date('Y-m-d'),
            'status' => 'approved',
            'description' => 'Test description',
            'tags' => '',
            'uploaded_by' => 1,
            'file_path' => 'storage/documents/test.txt',
            'file_name' => 'test.txt',
            'file_size' => 1024,
            'file_type' => 'text/plain',
            'is_encrypted' => 0,
            'ocr_status' => 'pending',
            'extracted_text' => null,
            'key_points' => null,
        ], $overrides);

        return $this->documentModel->create($data);
    }
}
