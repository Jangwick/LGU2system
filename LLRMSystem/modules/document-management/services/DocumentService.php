<?php

require_once __DIR__ . '/EncryptionService.php';
require_once __DIR__ . '/OcrService.php';
require_once __DIR__ . '/SummarizationService.php';

class DocumentService {
    private $documentModel;
    private $fileStorageService;
    private $logger;
    private $encryptionService;
    private $ocrService;
    private $summarizationService;
    private $db;
    
    public function __construct($documentModel, $fileStorageService, $logger) {
        $this->documentModel = $documentModel;
        $this->fileStorageService = $fileStorageService;
        $this->logger = $logger;
        $this->encryptionService = new EncryptionService();
        $this->ocrService = new OcrService();
        $this->summarizationService = new SummarizationService();
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

            // Run OCR BEFORE encryption (file is still plaintext at this point)
            $ocrResult = $this->runOcrOnFile($fileData['path'], $fileData['type'], $fileData['size']);

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

            $userId = $_SESSION['user_id'] ?? null;
            $status = $data['status'] ?? 'draft';
            $timestamp = date('Y-m-d H:i:s');

            // Prepare document data
            $documentData = [
                'reference_number' => $data['reference_number'],
                'title' => $data['title'],
                'document_type' => $data['document_type'],
                'document_date' => $data['document_date'],
                'status' => $status,
                'description' => $data['description'] ?? '',
                'tags' => $data['tags'] ?? '',
                'file_path' => $fileData['path'],
                'file_name' => $fileData['name'],
                'file_size' => $fileData['size'],
                'file_type' => $fileData['type'],
                'source_module' => $data['source_module'] ?? 'manual',
                'source_id' => $data['source_id'] ?? null,
                'uploaded_by' => $userId,
                'is_encrypted' => true,
                'encryption_key' => $encryptedFileKey,
                'status_changed_by' => $userId,
                'status_changed_at' => $timestamp,
                'approved_by' => $status === 'approved' ? $userId : null,
                'approved_at' => $status === 'approved' ? $timestamp : null,
                'extracted_text' => $ocrResult['text'],
                'ocr_status' => $ocrResult['status'],
                'ocr_processed_at' => $ocrResult['status'] === 'completed' ? date('Y-m-d H:i:s') : null,
                'key_points' => $ocrResult['key_points'],
                'key_points_generated_at' => $ocrResult['key_points'] ? date('Y-m-d H:i:s') : null
            ];

            // Create document record
            $documentId = $this->documentModel->create($documentData);

            // Record initial status history
            $this->documentModel->addStatusHistory($documentId, null, $status, $userId, 'Document created');

            // Notify all active users about the new document
            try {
                require_once __DIR__ . '/../../notifications/models/Notification.php';
                $notification = new Notification();
                $notification->notifyAllUsers([
                    'type' => Notification::TYPE_FILE,
                    'title' => 'New document uploaded',
                    'message' => $data['title'] . ' (' . $data['document_type'] . ')',
                    'source_module' => 'document-management',
                    'source_id' => $documentId,
                    'priority' => Notification::PRIORITY_NORMAL,
                    'data' => ['link' => 'modules/document-management/views/index.php']
                ]);
            } catch (Exception $e) {
                error_log("Failed to create document notification: " . $e->getMessage());
            }

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
                'ocr_status' => $ocrResult['status'],
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

        $oldStatus = $document['status'] ?? '';
        $newStatus = $data['status'] ?? $oldStatus;

        // Track status changes
        if ($oldStatus !== $newStatus) {
            $userId = $_SESSION['user_id'] ?? null;
            $timestamp = date('Y-m-d H:i:s');
            $data['status_changed_by'] = $userId;
            $data['status_changed_at'] = $timestamp;

            if ($newStatus === 'approved') {
                $data['approved_by'] = $userId;
                $data['approved_at'] = $timestamp;
            } else {
                $data['approved_by'] = null;
                $data['approved_at'] = null;
            }
        }

        // Update document
        $success = $this->documentModel->update($id, $data);

        if ($success) {
            if ($oldStatus !== $newStatus) {
                $this->documentModel->addStatusHistory($id, $oldStatus, $newStatus, $_SESSION['user_id'] ?? null, 'Status updated');
            }

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
     * Approve document
     */
    public function approveDocument($id) {
        $result = $this->updateDocument($id, ['status' => 'approved']);
        if ($result['success']) {
            $result['message'] = 'Document approved successfully';
        }
        return $result;
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
        $this->fileStorageService->deleteFile($this->resolveFilePath($document['file_path']));
        
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
     * Resolve a file path from the database to an absolute filesystem path.
     * Handles relative paths (storage/documents/...), absolute paths, and legacy Windows paths.
     */
    private function resolveFilePath($filePath) {
        if (file_exists($filePath)) {
            return $filePath;
        }
        // Try prepending BASE_PATH for relative paths
        if (defined('BASE_PATH')) {
            $fullPath = BASE_PATH . '/' . $filePath;
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
        // Try extracting storage/... portion from Windows-style paths
        if (preg_match('#(storage/.+)$#', $filePath, $matches)) {
            $relative = $matches[1];
            if (defined('BASE_PATH') && file_exists(BASE_PATH . '/' . $relative)) {
                return BASE_PATH . '/' . $relative;
            }
        }
        return $filePath;
    }

    /**
     * Preview document with access control (allows viewers for approved docs)
     */
    public function previewDocument($id) {
        $document = $this->documentModel->getById($id);

        if (!$document) {
            throw new Exception("Document not found");
        }

        $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

        // Viewers can only preview approved documents
        if ($userRole === 'viewer') {
            if (!in_array($document['status'] ?? '', ['approved'], true)) {
                throw new Exception("You do not have permission to preview this document");
            }
        } else {
            // Non-viewers use the standard decrypt permission check
            if (!$this->canDecryptDocument($document, $_SESSION['user_id'], $userRole)) {
                throw new Exception("You do not have permission to access this document");
            }
        }

        // Decrypt file if encrypted
        $filePath = $this->resolveFilePath($document['file_path']);

        if (empty($document['is_encrypted']) && $this->encryptionService->isEncrypted($filePath)) {
            throw new Exception("Document file is encrypted but the encryption flag is not set. Please re-upload the document.");
        }

        if ($document['is_encrypted'] ?? false) {
            if (!empty($document['encryption_key'])) {
                $keyDecryptionResult = $this->encryptionService->decryptFileKey($document['encryption_key']);
                if (!$keyDecryptionResult['success']) {
                    throw new Exception("File key decryption failed: " . $keyDecryptionResult['error']);
                }
                $fileKey = $keyDecryptionResult['file_key'];
            } else {
                $fileKey = null;
            }

            $decryptionResult = $this->encryptionService->decryptFile($filePath, $fileKey);
            if (!$decryptionResult['success']) {
                throw new Exception("File decryption failed: " . $decryptionResult['error']);
            }
            $tempPath = sys_get_temp_dir() . '/' . basename($filePath);
            file_put_contents($tempPath, $decryptionResult['content']);
            $filePath = $tempPath;
        }

        // Log preview with enhanced logging
        $this->logger->logDocumentActivity($id, Logger::ACTION_DOCUMENT_VIEW, $document['title'], [
            'file_name' => $document['file_name'],
            'file_type' => $document['file_type']
        ]);
        $this->logger->logAccess($id, $_SESSION['user_id'], 'preview');

        return [
            'path' => $filePath,
            'name' => $document['file_name'],
            'type' => $document['file_type']
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
        $filePath = $this->resolveFilePath($document['file_path']);

        // Detect corrupted records where the file is encrypted but the flag is not set
        if (empty($document['is_encrypted']) && $this->encryptionService->isEncrypted($filePath)) {
            throw new Exception("Document file is encrypted but the encryption flag is not set. Please re-upload the document.");
        }

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

        $genericOpenXmlTypes = [
            'application/zip',
            'application/octet-stream'
        ];

        $allowedOpenXmlExtensions = ['docx', 'xlsx', 'pptx'];

        $detectedMime = mime_content_type($file['tmp_name']);
        $uploadedName = $file['name'] ?? 'unknown';
        $ext = strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION));

        if (!in_array($detectedMime, $allowedTypes, true)) {
            // Some systems report OOXML files as application/zip or application/octet-stream
            if (in_array($detectedMime, $genericOpenXmlTypes, true) && in_array($ext, $allowedOpenXmlExtensions, true)) {
                // Accept the file; FileStorageService will normalize the stored MIME type
            } else {
                $hint = $ext !== '' ? " (uploaded: .$ext, detected type: $detectedMime)" : " (detected type: $detectedMime)";
                throw new Exception("Invalid file type{$hint}. Only PDF, Word, Excel, and PowerPoint files are allowed.");
            }
        }
        
        return true;
    }
    
    /**
     * Run OCR on a file (before encryption)
     * Returns extracted text, OCR status, and key points
     */
    private function runOcrOnFile($filePath, $mimeType, $fileSize) {
        $asyncThreshold = defined('OCR_ASYNC_THRESHOLD') ? OCR_ASYNC_THRESHOLD : 5242880; // 5MB
        $result = [
            'text' => null,
            'status' => 'pending',
            'key_points' => null
        ];

        // Check if OCR is enabled
        if (!defined('OCR_ENABLED') || !OCR_ENABLED) {
            $result['status'] = 'skipped';
            return $result;
        }

        // Check if file type is OCR-capable
        if (!$this->ocrService->isOcrCapable($mimeType, basename($filePath))) {
            $result['status'] = 'skipped';
            return $result;
        }

        // Large files: mark as pending for async processing
        if ($fileSize >= $asyncThreshold) {
            $result['status'] = 'pending';
            return $result;
        }

        // Small files: run OCR synchronously
        try {
            $ocrResult = $this->ocrService->extractText($filePath, $mimeType);
            $result['text'] = $ocrResult['text'];
            $result['status'] = $ocrResult['status'];

            // Generate key points if OCR succeeded
            if ($ocrResult['status'] === 'completed' && !empty($ocrResult['text'])) {
                $result['key_points'] = $this->summarizationService->generateKeyPointsString($ocrResult['text'], 7);
            }
        } catch (Exception $e) {
            error_log("OCR failed for $filePath: " . $e->getMessage());
            $result['status'] = 'failed';
        }

        return $result;
    }
    
    /**
     * Re-run OCR on an existing document (decrypts file first)
     */
    public function runOcrOnDocument($documentId) {
        $document = $this->documentModel->getById($documentId);
        if (!$document) {
            return ['success' => false, 'error' => 'Document not found'];
        }

        // Mark as processing
        $this->documentModel->updateOcrResult($documentId, 'processing');

        // Get file path — decrypt if necessary
        $filePath = $this->resolveFilePath($document['file_path']);
        $tempFile = null;

        if ($document['is_encrypted'] ?? false) {
            // Decrypt file to temp location for OCR
            if (!empty($document['encryption_key'])) {
                $keyDecryptionResult = $this->encryptionService->decryptFileKey($document['encryption_key']);
                if (!$keyDecryptionResult['success']) {
                    $this->documentModel->updateOcrResult($documentId, 'failed');
                    return ['success' => false, 'error' => 'File key decryption failed'];
                }
                $fileKey = $keyDecryptionResult['file_key'];
            } else {
                $fileKey = null;
            }

            $decryptionResult = $this->encryptionService->decryptFile($filePath, $fileKey);
            if (!$decryptionResult['success']) {
                $this->documentModel->updateOcrResult($documentId, 'failed');
                return ['success' => false, 'error' => 'File decryption failed'];
            }

            // Write decrypted content to temp file
            $tempFile = sys_get_temp_dir() . '/ocr_' . $documentId . '_' . uniqid();
            file_put_contents($tempFile, $decryptionResult['content']);
            $filePath = $tempFile;
        }

        // Run OCR
        $ocrResult = $this->ocrService->extractText($filePath, $document['file_type']);

        // Update document with OCR results
        $this->documentModel->updateOcrResult($documentId, $ocrResult['status'], $ocrResult['text']);

        // Generate key points if OCR succeeded
        if ($ocrResult['status'] === 'completed' && !empty($ocrResult['text'])) {
            $keyPoints = $this->summarizationService->generateKeyPointsString($ocrResult['text'], 7);
            $this->documentModel->updateKeyPoints($documentId, $keyPoints);
        }

        // Clean up temp file
        if ($tempFile && file_exists($tempFile)) {
            @unlink($tempFile);
        }

        return [
            'success' => true,
            'ocr_status' => $ocrResult['status'],
            'extracted_text_length' => strlen($ocrResult['text']),
            'error' => $ocrResult['error'] ?? null
        ];
    }
}
