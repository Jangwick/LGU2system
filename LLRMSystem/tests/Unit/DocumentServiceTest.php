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
}
