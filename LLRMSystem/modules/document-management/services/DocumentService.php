<?php

class DocumentService {
    private $documentModel;
    private $fileStorageService;
    private $logger;
    
    public function __construct($documentModel, $fileStorageService, $logger) {
        $this->documentModel = $documentModel;
        $this->fileStorageService = $fileStorageService;
        $this->logger = $logger;
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
            
            // Upload file
            $fileData = $this->fileStorageService->uploadFile($file, $data['document_type']);
            
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
                'uploaded_by' => $_SESSION['user_id']
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
     * Download document
     */
    public function downloadDocument($id) {
        $document = $this->documentModel->getById($id);
        
        if (!$document) {
            throw new Exception("Document not found");
        }
        
        // Log download with enhanced logging
        $this->logger->logDocumentActivity($id, Logger::ACTION_DOCUMENT_DOWNLOAD, $document['title'], [
            'file_name' => $document['file_name'],
            'file_type' => $document['file_type']
        ]);
        $this->logger->logAccess($id, $_SESSION['user_id'], 'download');
        
        return [
            'path' => $document['file_path'],
            'name' => $document['file_name'],
            'type' => $document['file_type']
        ];
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
