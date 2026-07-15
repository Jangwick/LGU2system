<?php

use PHPUnit\Framework\TestCase;

class FileUploadTest extends TestCase
{
    private $service;
    private $testStorage;
    private $testFile;

    protected function setUp(): void
    {
        $this->service = new FileStorageService();

        // Get storage base path via reflection
        $reflection = new ReflectionClass($this->service);
        $prop = $reflection->getProperty('storageBasePath');
        $prop->setAccessible(true);
        $this->testStorage = $prop->getValue($this->service);

        // Create a test temp file with valid PNG magic bytes
        $this->testFile = tempnam(sys_get_temp_dir(), 'llrm_test_');
        file_put_contents($this->testFile, pack('H*', '89504E470D0A1A0A0000000D49484452000000010000000108060000001F15C4890000000D4944415408D763F8FFFFFF3F0005FE02FAD5AB48D600000049454E44AE426082'));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }

        // Clean up any files created during tests
        $this->cleanupTestFiles();
    }

    private function cleanupTestFiles()
    {
        $dirs = [
            $this->testStorage . '/documents/testtype',
            $this->testStorage . '/documents/general'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && strpos(basename($file), 'llrm_test_') !== false) {
                    unlink($file);
                }
            }
        }
    }

    public function testRejectsFileExceedingMaxSize()
    {
        $reflection = new ReflectionClass($this->service);
        $prop = $reflection->getProperty('maxFileSize');
        $prop->setAccessible(true);
        $maxSize = $prop->getValue($this->service);

        $file = [
            'tmp_name' => $this->testFile,
            'name' => 'large.pdf',
            'size' => $maxSize + 1,
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('maximum size');
        $this->service->uploadFile($file, 'testtype');
    }

    public function testRejectsDisallowedExtension()
    {
        $file = [
            'tmp_name' => $this->testFile,
            'name' => 'malicious.exe',
            'size' => 100,
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File type not allowed');
        $this->service->uploadFile($file, 'testtype');
    }

    public function testRejectsPathTraversalDocumentType()
    {
        $file = [
            'tmp_name' => $this->testFile,
            'name' => 'valid.png',
            'size' => 100,
            'error' => UPLOAD_ERR_OK
        ];

        $result = $this->service->uploadFile($file, '../../../etc/passwd');

        $this->assertStringNotContainsString('..', $result['path']);
        $this->assertStringNotContainsString('etc/passwd', $result['path']);
        $this->assertStringContainsString('/documents/', $result['path']);
    }

    public function testDeleteFileRejectsPathTraversal()
    {
        $this->assertFalse($this->service->deleteFile('/etc/passwd'));
        $this->assertFalse($this->service->deleteFile($this->testStorage . '/../evil.txt'));
    }

    public function testGetFileRejectsPathTraversal()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not found');
        $this->service->getFile('/etc/passwd');
    }
}
