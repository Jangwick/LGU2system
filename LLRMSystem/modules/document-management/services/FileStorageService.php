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
    
    /**
     * Upload file to storage
     */
    public function uploadFile($file, $documentType) {
        // Create type-specific directory
        $typeDir = $this->documentPath . '/' . $documentType;
        if (!file_exists($typeDir)) {
            mkdir($typeDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
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
            'type' => $file['type'],
            'stored_name' => $filename
        ];
    }
    
    /**
     * Delete file from storage
     */
    public function deleteFile($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    /**
     * Get file for download
     */
    public function getFile($filepath) {
        if (!file_exists($filepath)) {
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
