<?php

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class DocumentController {
    private $documentService;
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
        $documentModel = new Document($this->db);
        $fileStorageService = new FileStorageService();
        $logger = new Logger($this->db);
        
        $this->documentService = new DocumentService($documentModel, $fileStorageService, $logger);
    }
    
    /**
     * Display documents list
     */
    public function index() {
        try {
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 10;
            
            $filters = [
                'search' => $_GET['search'] ?? '',
                'type' => $_GET['type'] ?? '',
                'status' => $_GET['status'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'file_size' => $_GET['file_size'] ?? '',
                'tags' => $_GET['tags'] ?? '',
                'category' => $_GET['category'] ?? '',
                'reference' => $_GET['reference'] ?? '',
                'sort_by' => $_GET['sort_by'] ?? 'created_at',
                'sort_dir' => $_GET['sort_dir'] ?? 'DESC',
                'user_role' => strtolower(trim($_SESSION['user_role'] ?? 'viewer'))
            ];
            
            $result = $this->documentService->getDocuments($page, $perPage, $filters);
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Show single document
     */
    public function show($id) {
        try {
            $document = $this->documentService->getDocument($id);
            return ['document' => $document];
            
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle document upload
     */
    public function store() {
        try {
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            // Check if file was uploaded (support both 'document' and 'document_file' field names)
            $fileField = null;
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $fileField = 'document_file';
            } elseif (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
                $fileField = 'document';
            }
            
            if (!$fileField) {
                throw new Exception("No file uploaded");
            }
            
            // Prepare data
            $data = [
                'title' => $_POST['title'] ?? '',
                'document_type' => $_POST['document_type'] ?? '',
                'document_date' => $_POST['document_date'] ?? date('Y-m-d'),
                'status' => $_POST['status'] ?? 'draft',
                'description' => $_POST['description'] ?? '',
                'tags' => $_POST['tags'] ?? '',
                'reference_number' => $_POST['reference_number'] ?? ''
            ];
            
            // Validate required fields
            if (empty($data['title']) || empty($data['document_type'])) {
                throw new Exception("Title and document type are required");
            }
            
            $result = $this->documentService->createDocument($data, $_FILES[$fileField]);
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update document
     */
    public function update($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'document_type' => $_POST['document_type'] ?? '',
                'document_date' => $_POST['document_date'] ?? '',
                'status' => $_POST['status'] ?? '',
                'description' => $_POST['description'] ?? '',
                'tags' => $_POST['tags'] ?? ''
            ];
            
            $result = $this->documentService->updateDocument($id, $data);
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete document
     */
    public function delete($id) {
        try {
            $result = $this->documentService->deleteDocument($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download document
     */
    public function download($id) {
        try {
            $fileData = $this->documentService->downloadDocument($id);
            
            // Set headers for file download
            header('Content-Type: ' . $fileData['type']);
            header('Content-Disposition: attachment; filename="' . $fileData['name'] . '"');
            header('Content-Length: ' . filesize($fileData['path']));
            header('Cache-Control: no-cache');
            
            // Read and output file
            readfile($fileData['path']);
            exit;
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Bulk delete documents
     */
    public function bulkDelete() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            $ids = $_POST['document_ids'] ?? [];
            
            if (empty($ids) || !is_array($ids)) {
                throw new Exception("No documents selected");
            }
            
            $deleted = 0;
            $errors = [];
            
            foreach ($ids as $id) {
                try {
                    $this->documentService->deleteDocument($id);
                    $deleted++;
                } catch (Exception $e) {
                    $errors[] = "Document ID {$id}: " . $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'deleted' => $deleted,
                'errors' => $errors,
                'message' => "{$deleted} document(s) deleted successfully"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
