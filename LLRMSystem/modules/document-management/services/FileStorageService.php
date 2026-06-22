<?php

class FileStorageService {
    private $storageBasePath;
    private $documentPath;
    private $versionsPath;
    private $tempPath;
    
    public function __construct() {
        $this->storageBasePath = dirname(dirname(dirname(__DIR__))) . '/storage';
        $this->documentPath = $this->storageBasePath . '/documents';
        $this->versionsPath = $this->storageBasePath . '/versions';
        $this->tempPath = $this->storageBasePath . '/temp';
        
        $this->ensureDirectories();
    }
    
    private $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'];
    private $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif'
    ];
    private $maxFileSize = 10485760; // 10MB
    
    /**
     * Upload file to storage
     */
    public function uploadFile($file, $documentType) {
        // Validate file upload
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Invalid file upload");
        }
        
        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File exceeds maximum size of 10MB");
        }
        
        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception("File type not allowed. Allowed: " . implode(', ', $this->allowedExtensions));
        }
        
        // Verify MIME type using finfo (more reliable than browser-supplied type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Map extensions to expected MIME types for cross-check
        $extToMime = [
            'pdf'  => ['application/pdf'],
            'doc'  => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream', 'application/zip'],
            'xls'  => ['application/vnd.ms-excel', 'application/octet-stream'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream', 'application/zip'],
            'ppt'  => ['application/vnd.ms-powerpoint', 'application/octet-stream'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/octet-stream', 'application/zip'],
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'gif'  => ['image/gif']
        ];
        
        $expectedMimes = $extToMime[$extension] ?? [];
        if (!empty($expectedMimes) && !in_array($detectedMime, $expectedMimes) && !in_array($detectedMime, $this->allowedMimes)) {
            throw new Exception("File MIME type mismatch. Detected: {$detectedMime}");
        }
        
        // Sanitize document type for directory path (prevent path traversal)
        $documentType = preg_replace('/[^a-zA-Z0-9_-]/', '', $documentType);
        if (empty($documentType)) {
            $documentType = 'general';
        }
        
        // Create type-specific directory
        $typeDir = $this->documentPath . '/' . $documentType;
        if (!file_exists($typeDir)) {
            mkdir($typeDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = $this->generateUniqueFilename($extension);
        $filepath = $typeDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to upload file");
        }
        
        // Set proper permissions
        chmod($filepath, 0644);
        
        return [
            'path' => $filepath,
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $detectedMime,
            'stored_name' => $filename
        ];
    }
    
    /**
     * Delete file from storage
     */
    public function deleteFile($filepath) {
        // Prevent path traversal — resolve real path and ensure it's within storage
        $realPath = realpath($filepath);
        $storageReal = realpath($this->storageBasePath);
        if ($realPath === false || $storageReal === false || strpos($realPath, $storageReal) !== 0) {
            return false;
        }
        if (file_exists($realPath)) {
            return unlink($realPath);
        }
        return false;
    }
    
    /**
     * Get file for download
     */
    public function getFile($filepath) {
        // Prevent path traversal — resolve real path and ensure it's within storage
        $realPath = realpath($filepath);
        $storageReal = realpath($this->storageBasePath);
        if ($realPath === false || $storageReal === false || strpos($realPath, $storageReal) !== 0) {
            throw new Exception("File not found");
        }
        if (!file_exists($realPath)) {
            throw new Exception("File not found");
        }
        
        return [
            'path' => $filepath,
            'size' => filesize($filepath),
            'mime' => mime_content_type($filepath)
        ];
    }
    
    /**
     * Create version backup
     */
    public function createVersion($filepath, $versionNumber) {
        if (!file_exists($filepath)) {
            throw new Exception("Source file not found");
        }
        
        $filename = basename($filepath);
        $versionFilename = pathinfo($filename, PATHINFO_FILENAME) . 
                          '_v' . $versionNumber . '.' . 
                          pathinfo($filename, PATHINFO_EXTENSION);
        
        $versionPath = $this->versionsPath . '/' . $versionFilename;
        
        if (!copy($filepath, $versionPath)) {
            throw new Exception("Failed to create version backup");
        }
        
        return $versionPath;
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($extension) {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories() {
        $directories = [
            $this->storageBasePath,
            $this->documentPath,
            $this->versionsPath,
            $this->tempPath
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Get storage statistics
     */
    public function getStorageStats() {
        $totalSize = 0;
        $fileCount = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->documentPath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
                $fileCount++;
            }
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount
        ];
    }
}
