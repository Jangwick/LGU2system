<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/document-management/services/DocumentService.php';

class DocumentServiceTest extends TestCase
{
    private function createServiceWithoutConstructor(): DocumentService
    {
        $reflection = new ReflectionClass(DocumentService::class);
        return $reflection->newInstanceWithoutConstructor();
    }

    public function test_get_source_counts_aggregates_all_and_per_system(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public $calls = [];

            public function getSourceCounts($sourceSystems, $filters): array
            {
                $this->calls[] = $filters;
                $all = 100;
                $counts = [];
                foreach ($sourceSystems as $sys) {
                    $counts[$sys] = $sys === 'orts' ? 40 : 25;
                }
                return ['all' => $all] + $counts;
            }
        };

        $reflection = new ReflectionClass($service);
        $documentModel = $reflection->getProperty('documentModel');
        $documentModel->setAccessible(true);
        $documentModel->setValue($service, $model);

        $counts = $service->getSourceCounts(['orts', 'cms'], ['status' => 'approved']);

        $this->assertSame(100, $counts['all']);
        $this->assertSame(40, $counts['orts']);
        $this->assertSame(25, $counts['cms']);
    }

    public function test_get_documents_pagination_envelope(): void
    {
        $service = $this->createServiceWithoutConstructor();
        $reflection = new ReflectionClass($service);

        $model = new class {
            public function getAll($filters, $includeText = false): array
            {
                return [
                    ['id' => 1, 'title' => 'Doc 1'],
                    ['id' => 2, 'title' => 'Doc 2'],
                ];
            }

            public function getCount($filters): int
            {
                return 25;
            }
        };

        $documentModel = $reflection->getProperty('documentModel');
        $documentModel->setAccessible(true);
        $documentModel->setValue($service, $model);

        $result = $service->getDocuments(2, 10, ['status' => 'approved']);

        $this->assertCount(2, $result['documents']);
        $this->assertSame(2, $result['pagination']['current_page']);
        $this->assertSame(10, $result['pagination']['per_page']);
        $this->assertSame(25, $result['pagination']['total']);
        $this->assertSame(3, (int) $result['pagination']['total_pages']);
    }

    public function test_can_decrypt_document_by_super_admin(): void
    {
        $service = $this->createServiceWithoutConstructor();
        $method = new ReflectionMethod(DocumentService::class, 'canDecryptDocument');
        $method->setAccessible(true);

        $document = ['status' => 'pending'];
        $this->assertTrue($method->invoke($service, $document, 1, 'superadmin'));
        $this->assertTrue($method->invoke($service, $document, 1, 'super_admin'));
        $this->assertTrue($method->invoke($service, $document, 1, 'officer'));
        $this->assertTrue($method->invoke($service, $document, 1, 'admin'));
    }

    public function test_can_decrypt_document_for_viewer_is_always_false(): void
    {
        $service = $this->createServiceWithoutConstructor();
        $method = new ReflectionMethod(DocumentService::class, 'canDecryptDocument');
        $method->setAccessible(true);

        $document = ['status' => 'approved'];
        $this->assertFalse($method->invoke($service, $document, 1, 'viewer'));
    }

    public function test_can_decrypt_document_for_staff_depends_on_status(): void
    {
        $service = $this->createServiceWithoutConstructor();
        $method = new ReflectionMethod(DocumentService::class, 'canDecryptDocument');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service, ['status' => 'approved'], 1, 'staff'));
        $this->assertTrue($method->invoke($service, ['status' => 'pending'], 1, 'staff'));
        $this->assertFalse($method->invoke($service, ['status' => 'archived'], 1, 'staff'));
    }

    public function test_resolve_file_path_for_existing_relative_path(): void
    {
        $service = $this->createServiceWithoutConstructor();
        $method = new ReflectionMethod(DocumentService::class, 'resolveFilePath');
        $method->setAccessible(true);

        $file = tempnam(sys_get_temp_dir(), 'doc_');
        $this->assertSame($file, $method->invoke($service, $file));
        @unlink($file);
    }

    public function test_resolve_file_path_for_windows_style_storage_path_returns_original_when_not_found(): void
    {
        $service = $this->createServiceWithoutConstructor();
        $method = new ReflectionMethod(DocumentService::class, 'resolveFilePath');
        $method->setAccessible(true);

        $original = 'C:/xampp/htdocs/storage/uploads/file.pdf';
        $this->assertSame($original, $method->invoke($service, $original));
    }

    public function test_get_document_returns_document_and_logs_access(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public function getById($id): array
            {
                return ['id' => $id, 'title' => 'Doc ' . $id];
            }
        };

        $logger = new class {
            public $logs = [];
            public function logAccess($id, $userId): void
            {
                $this->logs[] = ['id' => $id, 'userId' => $userId];
            }
        };

        $reflection = new ReflectionClass($service);
        $documentModel = $reflection->getProperty('documentModel');
        $documentModel->setAccessible(true);
        $documentModel->setValue($service, $model);

        $loggerProp = $reflection->getProperty('logger');
        $loggerProp->setAccessible(true);
        $loggerProp->setValue($service, $logger);

        $_SESSION['user_id'] = 42;

        $document = $service->getDocument(7);

        $this->assertSame(7, $document['id']);
        $this->assertSame('Doc 7', $document['title']);
        $this->assertSame(7, $logger->logs[0]['id']);
        $this->assertSame(42, $logger->logs[0]['userId']);
    }

    public function test_get_document_throws_when_not_found(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public function getById($id): ?array
            {
                return null;
            }
        };

        $reflection = new ReflectionClass($service);
        $documentModel = $reflection->getProperty('documentModel');
        $documentModel->setAccessible(true);
        $documentModel->setValue($service, $model);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Document not found');

        $service->getDocument(99);
    }

    public function test_delete_document_removes_file_and_record(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public $calls = [];

            public function getById($id): array
            {
                return ['id' => $id, 'title' => 'Test Document', 'file_path' => 'uploads/test.pdf', 'uploaded_by' => 5];
            }

            public function delete($id): bool
            {
                $this->calls[] = ['delete', $id];
                return true;
            }
        };

        $fileStorage = new class {
            public $calls = [];
            public function deleteFile($path): bool
            {
                $this->calls[] = ['deleteFile', $path];
                return true;
            }
        };

        $logger = new class {
            public $calls = [];
            public function logDocumentActivity($action, $module, $recordId, $description, $metadata = null): void
            {
                $this->calls[] = ['logDocumentActivity', $action, $module, $recordId];
            }
        };

        $reflection = new ReflectionClass($service);

        foreach (['documentModel' => $model, 'fileStorageService' => $fileStorage, 'logger' => $logger] as $name => $value) {
            $prop = $reflection->getProperty($name);
            $prop->setAccessible(true);
            $prop->setValue($service, $value);
        }

        $_SESSION['user_id'] = 5;

        $result = $service->deleteDocument(3);

        $this->assertTrue($result['success']);
        $this->assertSame('uploads/test.pdf', $fileStorage->calls[0][1]);
        $this->assertSame(['delete', 3], $model->calls[0]);
    }

    public function test_update_document_tracks_status_change(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public $calls = [];

            public function getById($id): array
            {
                return [
                    'id' => $id,
                    'title' => 'Doc',
                    'status' => 'pending',
                    'compliance_status' => 'compliant'
                ];
            }

            public function update($id, $data): bool
            {
                $this->calls[] = ['update', $id, $data];
                return true;
            }

            public function addStatusHistory($id, $old, $new, $userId, $notes): void
            {
                $this->calls[] = ['addStatusHistory', $id, $old, $new, $userId];
            }
        };

        $logger = new class {
            public $calls = [];
            public function logDocumentActivity($action, $module, $recordId, $description, $metadata = null, $old = null): void
            {
                $this->calls[] = ['log', $action, $recordId];
            }
        };

        $reflection = new ReflectionClass($service);
        foreach (['documentModel' => $model, 'logger' => $logger] as $name => $value) {
            $prop = $reflection->getProperty($name);
            $prop->setAccessible(true);
            $prop->setValue($service, $value);
        }

        $_SESSION['user_id'] = 7;

        $result = $service->updateDocument(1, ['status' => 'approved', 'title' => 'Updated Title']);

        $this->assertTrue($result['success']);
        $this->assertSame('approved', $model->calls[0][2]['status']);
        $this->assertSame(7, $model->calls[0][2]['approved_by']);
        $this->assertSame(['addStatusHistory', 1, 'pending', 'approved', 7], $model->calls[1]);
    }

    public function test_update_document_rejects_non_compliant_approval(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public function getById($id): array
            {
                return [
                    'id' => $id,
                    'title' => 'Doc',
                    'status' => 'pending',
                    'compliance_status' => 'pending'
                ];
            }
        };

        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('documentModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $model);

        $result = $service->updateDocument(2, ['status' => 'approved']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('compliance', $result['error']);
    }

    public function test_approve_document_succeeds_when_compliant(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public $calls = [];

            public function getById($id): array
            {
                return [
                    'id' => $id,
                    'title' => 'Doc',
                    'status' => 'pending',
                    'compliance_status' => 'compliant'
                ];
            }

            public function update($id, $data): bool
            {
                $this->calls[] = ['update', $id, $data];
                return true;
            }

            public function addStatusHistory($id, $old, $new, $userId, $notes): void
            {
                $this->calls[] = ['history', $id, $old, $new];
            }
        };

        $logger = new class {
            public $calls = [];
            public function logDocumentActivity($action, $module, $recordId, $description, $metadata = null, $old = null): void
            {
                $this->calls[] = ['log', $action, $recordId];
            }
        };

        $reflection = new ReflectionClass($service);
        foreach (['documentModel' => $model, 'logger' => $logger] as $name => $value) {
            $prop = $reflection->getProperty($name);
            $prop->setAccessible(true);
            $prop->setValue($service, $value);
        }

        $_SESSION['user_id'] = 9;

        $result = $service->approveDocument(5);

        $this->assertTrue($result['success']);
        $this->assertSame('Document approved successfully', $result['message']);
        $this->assertSame('approved', $model->calls[0][2]['status']);
    }

    public function test_approve_document_fails_when_not_compliant(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $model = new class {
            public function getById($id): array
            {
                return [
                    'id' => $id,
                    'title' => 'Doc',
                    'status' => 'pending',
                    'compliance_status' => 'non_compliant'
                ];
            }
        };

        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('documentModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $model);

        $result = $service->approveDocument(6);

        $this->assertFalse($result['success']);
        $this->assertSame('non_compliant', $result['compliance_status']);
    }

    public function test_create_document_throws_on_invalid_file_type(): void
    {
        $service = $this->createServiceWithoutConstructor();

        $tmpFile = tempnam(sys_get_temp_dir(), 'doc');
        file_put_contents($tmpFile, 'plain text content');

        $this->expectException(Exception::class);

        $service->createDocument(
            ['title' => 'Test', 'document_type' => 'ordinance'],
            [
                'tmp_name' => $tmpFile,
                'name' => 'test.txt',
                'type' => 'text/plain',
                'size' => 100,
                'error' => UPLOAD_ERR_OK
            ]
        );

        @unlink($tmpFile);
    }
}
