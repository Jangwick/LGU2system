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

    public function test_delete_calls_service_delete(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public $calls = [];
            public function deleteDocument($id): array
            {
                $this->calls[] = ['deleteDocument', $id];
                return ['success' => true, 'message' => 'Document deleted'];
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $result = $controller->delete(1);

        $this->assertTrue($result['success']);
        $this->assertSame('Document deleted', $result['message']);
        $this->assertSame(['deleteDocument', 1], $service->calls[0]);
    }

    public function test_store_creates_document(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public $calls = [];
            public function createDocument($data, $file, $runOcr = true): array
            {
                $this->calls[] = ['createDocument', $data['reference_number'], $file['name'] ?? ''];
                return ['success' => true, 'document_id' => 42];
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'reference_number' => '2025-01',
            'title' => 'Budget',
            'document_type' => 'ordinance',
            'document_date' => '2025-01-01',
            'description' => 'Budget doc',
            'tags' => 'budget,finance',
            'status' => 'draft',
        ];
        $tmpFile = tempnam(sys_get_temp_dir(), 'doc');
        file_put_contents($tmpFile, 'dummy');
        $_FILES['document'] = ['tmp_name' => $tmpFile, 'name' => 'budget.pdf', 'type' => 'application/pdf', 'size' => 5, 'error' => UPLOAD_ERR_OK];

        $result = $controller->store();

        $this->assertTrue($result['success']);
        $this->assertSame(42, $result['document_id']);
        $this->assertSame('createDocument', $service->calls[0][0]);
    }

    public function test_store_rejects_non_post_request(): void
    {
        $controller = $this->createControllerWithoutConstructor();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $result = $controller->store();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid request method', $result['error']);
    }

    public function test_update_updates_document(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public $calls = [];
            public function updateDocument($id, $data): array
            {
                $this->calls[] = ['updateDocument', $id, $data['title']];
                return ['success' => true, 'message' => 'Updated'];
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['title' => 'Updated Budget', 'status' => 'approved'];

        $result = $controller->update(1);

        $this->assertTrue($result['success']);
        $this->assertSame('Updated', $result['message']);
        $this->assertSame('Updated Budget', $service->calls[0][2]);
    }

    public function test_bulk_delete_deletes_selected_documents(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public $calls = [];
            public function deleteDocument($id): array
            {
                $this->calls[] = ['deleteDocument', $id];
                return ['success' => true];
            }
        };

        $reflection = new ReflectionClass($controller);
        $documentService = $reflection->getProperty('documentService');
        $documentService->setAccessible(true);
        $documentService->setValue($controller, $service);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['document_ids' => ['1', '2', 'abc', '3']];

        $result = $controller->bulkDelete();

        $this->assertTrue($result['success']);
        $this->assertSame(3, $result['deleted']);
        $this->assertCount(3, $service->calls);
    }

    public function test_bulk_delete_rejects_empty_selection(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['document_ids' => []];

        $result = $controller->bulkDelete();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No documents selected', $result['error']);
    }
}
