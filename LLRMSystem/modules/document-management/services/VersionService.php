<?php

class VersionService {
    private $versionModel;
    private $documentModel;
    private $fileStorageService;
    private $logger;
    
    public function __construct($versionModel, $documentModel, $fileStorageService, $logger) {
        $this->versionModel = $versionModel;
        $this->documentModel = $documentModel;
        $this->fileStorageService = $fileStorageService;
        $this->logger = $logger;
    }
    
    /**
     * Get version history for document
     */
    public function getVersionHistory($documentId) {
        return $this->versionModel->getByDocumentId($documentId);
    }
    
    /**
     * Create new version when file is replaced
     */
    public function createVersion($documentId, $file, $changeDescription, $userId) {
        try {
            // Get current document
            $document = $this->documentModel->getById($documentId);
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            // Get next version number
            $latestVersion = $this->versionModel->getLatestVersionNumber($documentId);
            $newVersionNumber = $latestVersion + 1;
            
            // Save current file as version
            $versionData = [
                'document_id' => $documentId,
                'version_number' => $latestVersion > 0 ? $latestVersion : 1,
                'file_path' => $document['file_path'],
                'file_name' => $document['file_name'],
                'file_size' => $document['file_size'],
                'change_description' => 'Previous version archived',
                'created_by' => $userId
            ];
            
            // Only create version if this isn't the first file
            if ($latestVersion > 0 || file_exists($document['file_path'])) {
                $this->versionModel->create($versionData);
            }
            
            // Upload new file
            $uploadResult = $this->fileStorageService->uploadFile(
                $file,
                $document['document_type']
            );
            
            if (!$uploadResult['success']) {
                throw new Exception($uploadResult['error']);
            }
            
            // Update document with new file
            $updateData = [
                'title' => $document['title'],
                'document_type' => $document['document_type'],
                'document_date' => $document['document_date'],
                'status' => $document['status'],
                'description' => $document['description'],
                'tags' => $document['tags']
            ];
            
            $this->documentModel->update($documentId, $updateData);
            
            // Update file info
            $stmt = $this->documentModel->db->prepare("
                UPDATE legislative_documents 
                SET file_path = :file_path,
                    file_name = :file_name,
                    file_size = :file_size,
                    file_type = :file_type,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':file_path' => $uploadResult['file_path'],
                ':file_name' => $uploadResult['file_name'],
                ':file_size' => $uploadResult['file_size'],
                ':file_type' => $uploadResult['mime_type'],
                ':id' => $documentId
            ]);
            
            // Log activity
            $this->logger->log(
                $userId,
                'document_version_created',
                $documentId,
                "Created version {$newVersionNumber}: {$changeDescription}"
            );
            
            return [
                'success' => true,
                'version_number' => $newVersionNumber,
                'message' => 'New version created successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Revert to previous version
     */
    public function revertToVersion($documentId, $versionNumber, $userId) {
        try {
            // Get the version to revert to
            $version = $this->versionModel->getByVersion($documentId, $versionNumber);
            if (!$version) {
                throw new Exception('Version not found');
            }
            
            // Get current document
            $document = $this->documentModel->getById($documentId);
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            // Create version of current state before reverting
            $currentVersionNumber = $this->versionModel->getLatestVersionNumber($documentId);
            $newVersionData = [
                'document_id' => $documentId,
                'version_number' => $currentVersionNumber + 1,
                'file_path' => $document['file_path'],
                'file_name' => $document['file_name'],
                'file_size' => $document['file_size'],
                'change_description' => 'Before reverting to version ' . $versionNumber,
                'created_by' => $userId
            ];
            $this->versionModel->create($newVersionData);
            
            // Copy version file to document location
            if (!file_exists($version['file_path'])) {
                throw new Exception('Version file not found');
            }
            
            $documentType = $document['document_type'];
            $storagePath = __DIR__ . '/../../../storage/documents/' . $documentType . '/';
            $newFileName = $this->fileStorageService->generateUniqueFilename($version['file_name']);
            $newFilePath = $storagePath . $newFileName;
            
            if (!copy($version['file_path'], $newFilePath)) {
                throw new Exception('Failed to copy version file');
            }
            
            // Update document with version file
            $stmt = $this->documentModel->db->prepare("
                UPDATE legislative_documents 
                SET file_path = :file_path,
                    file_name = :file_name,
                    file_size = :file_size,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':file_path' => $newFilePath,
                ':file_name' => $version['file_name'],
                ':file_size' => $version['file_size'],
                ':id' => $documentId
            ]);
            
            // Log activity
            $this->logger->log(
                $userId,
                'document_reverted',
                $documentId,
                "Reverted to version {$versionNumber}"
            );
            
            return [
                'success' => true,
                'message' => "Successfully reverted to version {$versionNumber}"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download specific version
     */
    public function downloadVersion($versionId, $userId) {
        $version = $this->versionModel->getById($versionId);
        
        if (!$version) {
            return ['success' => false, 'error' => 'Version not found'];
        }
        
        if (!file_exists($version['file_path'])) {
            return ['success' => false, 'error' => 'Version file not found'];
        }
        
        // Log access
        $this->logger->logAccess($version['document_id'], $userId, 'download');
        
        return [
            'success' => true,
            'file_path' => $version['file_path'],
            'file_name' => $version['file_name']
        ];
    }
}
