<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/document-management/controllers/DocumentController.php';

class DocumentControllerTest extends TestCase
{
    private function createControllerWithoutConstructor(): DocumentController
    {
        $reflection = new ReflectionClass(DocumentController::class);
        return $reflection->newInstanceWithoutConstructor();
    }

    public function test_index_builds_document_list_envelope(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public $calls = [];

            public function getDocuments($page, $perPage, $filters): array
            {
                $this->calls[] = ['getDocuments', $page, $perPage, $filters];
                return [
                    'documents' => [
                        ['id' => 1, 'title' => 'Ordinance 1'],
                        ['id' => 2, 'title' => 'Resolution 1'],
                    ],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 10,
                        'total' => 25,
                        'total_pages' => 3
                    ]
                ];
            }

            public function getSourceCounts($sourceSystems, $filters): array
            {
                $this->calls[] = ['getSourceCounts', $sourceSystems, $filters];
                $counts = ['all' => 25];
                foreach ($sourceSystems as $sys) {
                    $counts[$sys] = 5;
                }
                return $counts;
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $_GET = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'staff'];

        $result = $controller->index();

        $this->assertCount(2, $result['documents']);
        $this->assertSame(25, $result['pagination']['total']);
        $this->assertArrayHasKey('source_systems', $result);
        $this->assertArrayHasKey('source_counts', $result);
        $this->assertSame(25, $result['source_counts']['all']);
    }

    public function test_show_denies_viewer_access_to_non_approved(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public function getDocument($id): array
            {
                return ['id' => $id, 'status' => 'pending'];
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $_SESSION = ['user_id' => 1, 'user_role' => 'viewer'];

        $result = $controller->show(1);

        $this->assertTrue($result['error']);
        $this->assertStringContainsString('Access denied', $result['message']);
    }

    public function test_index_returns_error_on_exception(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public function getDocuments($page, $perPage, $filters): array
            {
                throw new Exception('Database connection failed');
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $_GET = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'staff'];

        $result = $controller->index();

        $this->assertTrue($result['error']);
        $this->assertSame('Database connection failed', $result['message']);
    }
}
