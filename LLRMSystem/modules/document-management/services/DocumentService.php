<?php

require_once __DIR__ . '/EncryptionService.php';

class DocumentService {
    private $documentModel;
    private $fileStorageService;
    private $logger;
    private $encryptionService;
    private $db;
    
    public function __construct($documentModel, $fileStorageService, $logger) {
        $this->documentModel = $documentModel;
        $this->fileStorageService = $fileStorageService;
        $this->logger = $logger;
        $this->encryptionService = new EncryptionService();
        $this->db = getDatabase();
    }
    
    /**
     * Get paginated documents with filters
     */
    public function getDocuments($page = 1, $perPage = 10, $filters = []) {
        $offset = ($page - 1) * $perPage;
        $filters['limit'] = $perPage;
        $filters['offset'] = $offset;
        
        $documents = $this->documentModel->getAll($filters);
        $total = $this->documentModel->getCount($filters);
        
        return [
            'documents' => $documents,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
    
    /**
     * Get single document
     */
    public function getDocument($id) {
        $document = $this->documentModel->getById($id);
        
        if (!$document) {
            throw new Exception("Document not found");
        }
        
        // Log access
        $this->logger->logAccess($id, $_SESSION['user_id']);
        
        return $document;
    }
    
    /**
     * Create document with file upload
     */
    public function createDocument($data, $file) {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate reference number if not provided
            if (empty($data['reference_number'])) {
                $year = !empty($data['document_date']) ? date('Y', strtotime($data['document_date'])) : null;
                $data['reference_number'] = $this->documentModel->generateReferenceNumber($data['document_type'], $year);
            }

            // Validate naming convention (optional - only if user wants to follow it)
            // Commented out to make it optional - users can choose to follow the convention
            /*
            $namingValidation = $this->documentModel->validateNamingConvention(
                $data['title'],
                $data['document_type'],
                $data['reference_number']
            );
            if (!$namingValidation['valid']) {
                return [
                    'success' => false,
                    'error' => $namingValidation['error'],
                    'expected_format' => $namingValidation['expected_format']
                ];
            }
            */

            // Validate reference number uniqueness
            $stmt = $this->db->prepare("SELECT id FROM legislative_documents WHERE reference_number = ?");
            $stmt->execute([$data['reference_number']]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'Reference number already exists. Please use a different reference number.'
                ];
            }

            // Upload file
            $fileData = $this->fileStorageService->uploadFile($file, $data['document_type']);

            // Generate a unique file key for this document
            $fileKey = $this->encryptionService->generateFileKey();
            
            // Encrypt the file with the unique file key
            $encryptionResult = $this->encryptionService->encryptFile($fileData['path'], $fileKey);
            if (!$encryptionResult['success']) {
                throw new Exception("File encryption failed: " . $encryptionResult['error']);
            }
            
            // Encrypt the file key with the master key for storage
            $keyEncryptionResult = $this->encryptionService->encryptFileKey($fileKey);
            if (!$keyEncryptionResult['success']) {
                throw new Exception("File key encryption failed: " . $keyEncryptionResult['error']);
            }
            $encryptedFileKey = $keyEncryptionResult['encrypted_key'];

            // Prepare document data
            $documentData = [
                'reference_number' => $data['reference_number'],
                'title' => $data['title'],
                'document_type' => $data['document_type'],
                'document_date' => $data['document_date'],
                'status' => $data['status'] ?? 'draft',
                'description' => $data['description'] ?? '',
                'tags' => $data['tags'] ?? '',
                'file_path' => $fileData['path'],
                'file_name' => $fileData['name'],
                'file_size' => $fileData['size'],
                'file_type' => $fileData['type'],
                'source_module' => $data['source_module'] ?? 'manual',
                'source_id' => $data['source_id'] ?? null,
                'uploaded_by' => $_SESSION['user_id'],
                'is_encrypted' => true,
                'encryption_key' => $encryptedFileKey
            ];
            
            // Create document record
            $documentId = $this->documentModel->create($documentData);
            
            // Log activity with detailed info
            $this->logger->logDocumentActivity($documentId, Logger::ACTION_DOCUMENT_UPLOAD, $data['title'], [
                'reference_number' => $data['reference_number'],
                'document_type' => $data['document_type'],
                'file_size' => $fileData['size']
            ]);
            
            return [
                'success' => true,
                'document_id' => $documentId,
                'reference_number' => $data['reference_number'],
                'message' => 'Document uploaded successfully'
            ];
            
        } catch (Exception $e) {
            // Clean up uploaded file if exists
            if (isset($fileData['path'])) {
                $this->fileStorageService->deleteFile($fileData['path']);
            }
            
            throw $e;
        }
    }
    
    /**
     * Update document
     */
    public function updateDocument($id, $data) {
        // Check if document exists
        $document = $this->documentModel->getById($id);
        if (!$document) {
            throw new Exception("Document not found");
        }
        
        // Store old values for audit trail
        $oldValues = [
            'title' => $document['title'],
            'description' => $document['description'] ?? '',
            'status' => $document['status'] ?? ''
        ];
        
        // Update document
        $success = $this->documentModel->update($id, $data);
        
        if ($success) {
            $this->logger->logDocumentActivity($id, Logger::ACTION_DOCUMENT_UPDATE, $document['title'], [
                'changes' => array_intersect_key($data, $oldValues)
            ], $oldValues);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Document updated successfully' : 'Failed to update document'
        ];
    }
    
    /**
     * Delete document
     */
    public function deleteDocument($id) {
        // Check if document exists
        $document = $this->documentModel->getById($id);
        if (!$document) {
            throw new Exception("Document not found");
        }
        
        // Delete file from storage
        $this->fileStorageService->deleteFile($document['file_path']);
        
        // Delete document record
        $success = $this->documentModel->delete($id);
        
        if ($success) {
            $this->logger->logDocumentActivity($id, Logger::ACTION_DOCUMENT_DELETE, $document['title'], [
                'document_type' => $document['document_type'] ?? 'unknown',
                'reference_number' => $document['reference_number'] ?? ''
            ]);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Document deleted successfully' : 'Failed to delete document'
        ];
    }
    
    /**
     * Download document with access control
     */
    public function downloadDocument($id) {
        $document = $this->documentModel->getById($id);
        
        if (!$document) {
            throw new Exception("Document not found");
        }
        
        // Check user permissions for decryption
        if (!$this->canDecryptDocument($document, $_SESSION['user_id'], strtolower(trim($_SESSION['user_role'] ?? '')))) {
            throw new Exception("You do not have permission to access this document");
        }
        
        // Decrypt file if encrypted
        $filePath = $document['file_path'];
        if ($document['is_encrypted'] ?? false) {
            // Decrypt the file key first
            if (!empty($document['encryption_key'])) {
                $keyDecryptionResult = $this->encryptionService->decryptFileKey($document['encryption_key']);
                if (!$keyDecryptionResult['success']) {
                    throw new Exception("File key decryption failed: " . $keyDecryptionResult['error']);
                }
                $fileKey = $keyDecryptionResult['file_key'];
            } else {
                // Fallback to master key for backward compatibility
                $fileKey = null;
            }
            
            $decryptionResult = $this->encryptionService->decryptFile($filePath, $fileKey);
            if (!$decryptionResult['success']) {
                throw new Exception("File decryption failed: " . $decryptionResult['error']);
            }
            // Save decrypted content to temporary file for download
            $tempPath = sys_get_temp_dir() . '/' . basename($filePath);
            file_put_contents($tempPath, $decryptionResult['content']);
            $filePath = $tempPath;
        }
        
        // Log download with enhanced logging
        $this->logger->logDocumentActivity($id, Logger::ACTION_DOCUMENT_DOWNLOAD, $document['title'], [
            'file_name' => $document['file_name'],
            'file_type' => $document['file_type']
        ]);
        $this->logger->logAccess($id, $_SESSION['user_id'], 'download');
        
        return [
            'path' => $filePath,
            'name' => $document['file_name'],
            'type' => $document['file_type']
        ];
    }
    
    /**
     * Check if user has permission to decrypt a document
     */
    private function canDecryptDocument($document, $userId, $userRole) {
        // Viewers cannot download any documents - view only
        if ($userRole === 'viewer') {
            return false;
        }
        
        // Super admins can access all documents
        if ($userRole === 'superadmin' || $userRole === 'super_admin') {
            return true;
        }
        
        // Officers can access all documents
        if ($userRole === 'officer') {
            return true;
        }
        
        // Admins can access all documents
        if ($userRole === 'admin' || $userRole === 'administrator') {
            return true;
        }
        
        // Staff can access approved and pending documents
        if ($userRole === 'staff') {
            return in_array($document['status'] ?? '', ['approved', 'pending']);
        }
        
        return false;
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        // Check file size (50MB max)
        $maxSize = 50 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception("File size exceeds maximum limit of 50MB");
        }
        
        // Check file type using server-side content inspection — never trust
        // $_FILES['type'], which is supplied by the browser and trivially forgeable.
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];

        $detectedMime = mime_content_type($file['tmp_name']);
        if (!in_array($detectedMime, $allowedTypes, true)) {
            $uploadedName = $file['name'] ?? 'unknown';
            $ext = strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION));
            $hint = $ext !== '' ? " (uploaded: .$ext, detected type: $detectedMime)" : " (detected type: $detectedMime)";
            throw new Exception("Invalid file type{$hint}. Only PDF, Word, Excel, and PowerPoint files are allowed.");
        }
        
        return true;
    }
}
