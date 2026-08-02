<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/document-management/services/FileStorageService.php';

class FileStorageServiceTest extends TestCase
{
    private FileStorageService $service;
    private string $tempStorage;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->service = new FileStorageService();

        $this->tempStorage = sys_get_temp_dir() . '/file_storage_test_' . uniqid();
        @mkdir($this->tempStorage . '/documents', 0755, true);
        @mkdir($this->tempStorage . '/versions', 0755, true);

        // Reflect and point service at temp storage
        $reflection = new ReflectionClass($this->service);
        $paths = [
            'storageBasePath' => $this->tempStorage,
            'documentPath' => $this->tempStorage . '/documents',
            'versionsPath' => $this->tempStorage . '/versions',
            'tempPath' => $this->tempStorage . '/temp',
        ];
        foreach ($paths as $propName => $path) {
            $prop = $reflection->getProperty($propName);
            $prop->setAccessible(true);
            $prop->setValue($this->service, $path);
        }

        $this->tempFile = $this->tempStorage . '/documents/test.txt';
        file_put_contents($this->tempFile, 'test content');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            @unlink($this->tempFile);
        }
        $this->removeDir($this->tempStorage);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    public function test_delete_file_removes_file_within_storage(): void
    {
        $this->assertTrue($this->service->deleteFile($this->tempFile));
        $this->assertFalse(file_exists($this->tempFile));
    }

    public function test_delete_file_rejects_outside_storage(): void
    {
        $outside = sys_get_temp_dir() . '/outside.txt';
        file_put_contents($outside, 'outside');

        $this->assertFalse($this->service->deleteFile($outside));

        @unlink($outside);
    }

    public function test_get_file_returns_metadata(): void
    {
        $result = $this->service->getFile($this->tempFile);

        $this->assertSame($this->tempFile, $result['path']);
        $this->assertSame(12, $result['size']);
        $this->assertNotEmpty($result['mime']);
    }

    public function test_get_file_throws_for_outside_storage(): void
    {
        $this->expectException(Exception::class);
        $this->service->getFile(sys_get_temp_dir() . '/outside.txt');
    }

    public function test_create_version_copies_file(): void
    {
        $versionPath = $this->service->createVersion($this->tempFile, 2);

        $this->assertFileExists($versionPath);
        $this->assertStringContainsString('_v2', $versionPath);

        @unlink($versionPath);
    }

    public function test_get_storage_stats_counts_files(): void
    {
        $stats = $this->service->getStorageStats();

        $this->assertGreaterThanOrEqual(1, $stats['file_count']);
        $this->assertGreaterThanOrEqual(12, $stats['total_size']);
    }
}
