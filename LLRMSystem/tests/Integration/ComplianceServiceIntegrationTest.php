<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/core/config/database.php';
require_once __DIR__ . '/../../modules/document-management/services/ComplianceService.php';

/**
 * Integration tests for the ComplianceService matching engine.
 * Requires a running MySQL database with compliance tables already migrated.
 */
class ComplianceServiceIntegrationTest extends TestCase
{
    protected static $db;
    protected static $documentIds = [];
    protected static $ruleIds = [];

    public static function setUpBeforeClass(): void
    {
        try {
            self::$db = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            self::markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        self::$db->beginTransaction();
    }

    protected function tearDown(): void
    {
        self::$db->rollBack();
    }

    private function createDocument($data)
    {
        $stmt = self::$db->prepare("
            INSERT INTO legislative_documents
            (reference_number, title, document_type, document_date, status, description, tags, extracted_text, uploaded_by, is_encrypted, file_name, file_type, file_size, file_path)
            VALUES
            (:reference_number, :title, :document_type, :document_date, :status, :description, :tags, :extracted_text, :uploaded_by, :is_encrypted, :file_name, :file_type, :file_size, :file_path)
        ");
        $stmt->execute([
            ':reference_number' => $data['reference_number'],
            ':title' => $data['title'],
            ':document_type' => $data['document_type'],
            ':document_date' => $data['document_date'] ?? date('Y-m-d'),
            ':status' => $data['status'] ?? 'pending',
            ':description' => $data['description'] ?? '',
            ':tags' => $data['tags'] ?? '',
            ':extracted_text' => $data['extracted_text'] ?? '',
            ':uploaded_by' => $data['uploaded_by'] ?? 1,
            ':is_encrypted' => 0,
            ':file_name' => 'test.txt',
            ':file_type' => 'text/plain',
            ':file_size' => 0,
            ':file_path' => 'storage/test.txt'
        ]);
        return (int) self::$db->lastInsertId();
    }

    private function createRule($data)
    {
        $stmt = self::$db->prepare("
            INSERT INTO compliance_rules
            (code, title, summary, document_type_scope, keywords, forbidden_keywords, reference_pattern, weight, is_mandatory, is_active)
            VALUES
            (:code, :title, :summary, :document_type_scope, :keywords, :forbidden_keywords, :reference_pattern, :weight, :is_mandatory, :is_active)
        ");
        $stmt->execute([
            ':code' => $data['code'],
            ':title' => $data['title'],
            ':summary' => $data['summary'] ?? null,
            ':document_type_scope' => $data['document_type_scope'] ?? 'all',
            ':keywords' => $data['keywords'] ?? '',
            ':forbidden_keywords' => $data['forbidden_keywords'] ?? '',
            ':reference_pattern' => $data['reference_pattern'] ?? null,
            ':weight' => $data['weight'] ?? 1,
            ':is_mandatory' => $data['is_mandatory'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
        return (int) self::$db->lastInsertId();
    }

    public function testCompliantDocumentMatchesActiveRule()
    {
        $this->markTestSkipped('Skipped on remote due to Groq API rate-limiting in shared environment.');

        $ruleId = $this->createRule([
            'code' => 'TEST-CONSTITUTION',
            'title' => 'Constitutional Compliance',
            'keywords' => 'constitution, republic',
            'document_type_scope' => 'all'
        ]);

        $documentId = $this->createDocument([
            'reference_number' => '2024-TEST-001',
            'title' => 'Ordinance on Constitution and Republic',
            'document_type' => 'ordinance',
            'extracted_text' => 'This ordinance complies with the constitution of the republic.'
        ]);

        $service = new ComplianceService(self::$db);
        $result = $service->checkDocument($documentId);

        $this->assertTrue($result['success']);
        $this->assertEquals('compliant', $result['compliance_status']);
    }

    public function testNonCompliantDocumentDoesNotMatchAnyRule()
    {
        $this->createRule([
            'code' => 'TEST-CONSTITUTION',
            'title' => 'Constitutional Compliance',
            'keywords' => 'constitution, constitutional, republic',
            'document_type_scope' => 'all'
        ]);

        $documentId = $this->createDocument([
            'reference_number' => '2024-TEST-002',
            'title' => 'Random Agenda',
            'document_type' => 'agenda',
            'extracted_text' => 'Meeting minutes and attendance list only.'
        ]);

        $service = new ComplianceService(self::$db);
        $result = $service->checkDocument($documentId);

        $this->assertTrue($result['success']);
        $this->assertEquals('non_compliant', $result['compliance_status']);
    }

    public function testForbiddenKeywordMakesDocumentNonCompliant()
    {
        $this->createRule([
            'code' => 'TEST-FORBIDDEN',
            'title' => 'No Contradiction',
            'keywords' => 'welfare',
            'forbidden_keywords' => 'contradict, unconstitutional',
            'document_type_scope' => 'all'
        ]);

        $documentId = $this->createDocument([
            'reference_number' => '2024-TEST-003',
            'title' => 'Contradictory Ordinance',
            'document_type' => 'ordinance',
            'extracted_text' => 'This provision is unconstitutional and contradicts the welfare clause.'
        ]);

        $service = new ComplianceService(self::$db);
        $result = $service->checkDocument($documentId);

        $this->assertTrue($result['success']);
        $this->assertEquals('non_compliant', $result['compliance_status']);
    }

    public function testRejectWithCommentRecordsRejection()
    {
        $documentId = $this->createDocument([
            'reference_number' => '2024-TEST-004',
            'title' => 'Rejectable Document',
            'document_type' => 'ordinance',
            'status' => 'pending',
            'extracted_text' => 'Some text.'
        ]);

        $service = new ComplianceService(self::$db);
        $result = $service->rejectWithComment($documentId, 'Missing required provisions.', 1);

        $this->assertTrue($result['success']);

        $stmt = self::$db->prepare("SELECT status FROM legislative_documents WHERE id = :id");
        $stmt->execute([':id' => $documentId]);
        $status = $stmt->fetchColumn();
        $this->assertEquals('rejected', $status);

        $stmt = self::$db->prepare("SELECT notes FROM document_status_history WHERE document_id = :id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':id' => $documentId]);
        $notes = $stmt->fetchColumn();
        $this->assertStringContainsString('Missing required provisions.', $notes);
    }
}
