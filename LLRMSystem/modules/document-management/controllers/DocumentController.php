<?php

require_once __DIR__ . '/../../core/config/config.php';
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
            $page = Sanitizer::int($_GET['page'] ?? 1, 1);
            $perPage = Sanitizer::int($_GET['per_page'] ?? 10, 10);
            
            $filters = [
                'search' => Sanitizer::plainText($_GET['search'] ?? ''),
                'type' => Sanitizer::plainText($_GET['type'] ?? ''),
                'status' => Sanitizer::enum($_GET['status'] ?? '', ['draft', 'pending', 'approved', 'rejected', 'archived'], ''),
                'compliance_status' => Sanitizer::enum($_GET['compliance_status'] ?? '', ['pending', 'compliant', 'non_compliant'], ''),
                'date_from' => Sanitizer::date($_GET['date_from'] ?? ''),
                'date_to' => Sanitizer::date($_GET['date_to'] ?? ''),
                'file_size' => Sanitizer::plainText($_GET['file_size'] ?? ''),
                'tags' => Sanitizer::plainText($_GET['tags'] ?? ''),
                'category' => Sanitizer::plainText($_GET['category'] ?? ''),
                'reference' => Sanitizer::plainText($_GET['reference'] ?? ''),
                'sort_by' => Sanitizer::enum($_GET['sort_by'] ?? 'created_at', ['created_at', 'title', 'document_date', 'file_size', 'reference_number'], 'created_at'),
                'sort_dir' => Sanitizer::enum($_GET['sort_dir'] ?? 'DESC', ['ASC', 'DESC'], 'DESC'),
                'source_system' => Sanitizer::plainText($_GET['source_system'] ?? ''),
                'user_role' => strtolower(trim($_SESSION['user_role'] ?? 'viewer'))
            ];
            
            $result = $this->documentService->getDocuments($page, $perPage, $filters);
            
            // Build source-system tab list from integration config files
            $integrationConfigPath = __DIR__ . '/../../integration/config';
            $sourceSystems = [];
            $sourceLabels = [];
            foreach (['cms', 'lacs', 'orts', 'pcms', 'phms', 'las'] as $configName) {
                $configFile = $integrationConfigPath . '/' . $configName . '.php';
                if (file_exists($configFile)) {
                    $config = require $configFile;
                    if (!empty($config['source_system'])) {
                        $sys = $config['source_system'];
                        $sourceSystems[] = $sys;
                        $sourceLabels[$sys] = strtoupper($sys);
                    }
                }
            }
            
            $result['source_systems'] = $sourceSystems;
            $result['source_labels'] = $sourceLabels;
            $result['source_counts'] = $this->documentService->getSourceCounts($sourceSystems, $filters);
            
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
            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
            
            if ($userRole === 'viewer' && !in_array($document['status'] ?? '', ['approved'], true)) {
                throw new Exception("Access denied. Viewers can only view approved documents.");
            }
            
            // Fetch extra info
            $model = new Document($this->db);
            $versions = $model->getVersions($id);
            $related = $model->getRelated($id);
            $activity = $model->getActivity($id);
            
            return [
                'document' => $document,
                'versions' => $versions,
                'related' => $related,
                'activity' => $activity
            ];
            
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
            
            $status = Sanitizer::enum($_POST['status'] ?? 'draft', ['draft', 'pending', 'approved', 'rejected', 'archived', 'published'], 'draft');
            if ($status === 'published') {
                $status = 'approved';
            }

            // Prepare data (sanitized)
            $data = [
                'title' => Sanitizer::plainText($_POST['title'] ?? ''),
                'document_type' => Sanitizer::plainText($_POST['document_type'] ?? ''),
                'document_date' => Sanitizer::date($_POST['document_date'] ?? date('Y-m-d')),
                'status' => $status,
                'description' => Sanitizer::richText($_POST['description'] ?? ''),
                'tags' => Sanitizer::plainText($_POST['tags'] ?? ''),
                'reference_number' => Sanitizer::plainText($_POST['reference_number'] ?? '')
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
                'title' => Sanitizer::plainText($_POST['title'] ?? ''),
                'document_type' => Sanitizer::plainText($_POST['document_type'] ?? ''),
                'document_date' => Sanitizer::date($_POST['document_date'] ?? ''),
                'status' => Sanitizer::enum($_POST['status'] ?? '', ['draft', 'pending', 'approved', 'rejected', 'archived'], ''),
                'description' => Sanitizer::richText($_POST['description'] ?? ''),
                'tags' => Sanitizer::plainText($_POST['tags'] ?? ''),
                'confidentiality_level' => Sanitizer::enum($_POST['confidentiality_level'] ?? 'public', ['public', 'internal', 'confidential', 'restricted'], 'public')
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
            // Viewers cannot download documents
            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
            if ($userRole === 'viewer') {
                http_response_code(403);
                echo json_encode(['error' => 'Viewers do not have permission to download documents']);
                exit;
            }

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
     * Preview document inline (serves decrypted file for browser rendering).
     * DOCX files are converted to HTML for in-browser preview.
     */
    public function preview($id) {
        try {
            $fileData = $this->documentService->previewDocument($id);
            $fileType = strtolower($fileData['type']);
            $fileName = $fileData['name'];

            // Convert DOCX to HTML for browser preview
            if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
                str_ends_with(strtolower($fileName), '.docx')) {

                require_once __DIR__ . '/../services/DocxToHtml.php';
                $converter = new DocxToHtml();
                $result = $converter->convert($fileData['path']);

                if ($result['success']) {
                    header('Content-Type: text/html; charset=utf-8');
                    header('Content-Disposition: inline; filename="' . $fileName . '.html"');
                    header('Cache-Control: no-cache, must-revalidate');
                    header('X-Content-Type-Options: nosniff');

                    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>
                        html, body { margin: 0; padding: 0; background: #f3f4f6; overflow-wrap: break-word; word-wrap: break-word; -webkit-text-size-adjust: 100%; overflow-x: hidden; }
                        .docx-preview { max-width: 100% !important; width: 100% !important; box-sizing: border-box !important; overflow-x: hidden !important; padding: 1em !important; margin: 0 !important; font-size: 12pt !important; }
                        .docx-preview * { box-sizing: border-box !important; max-width: 100% !important; word-wrap: break-word !important; overflow-wrap: break-word !important; }
                        .docx-preview img { max-width: 100% !important; height: auto !important; display: block; }
                        .docx-preview [style*="margin-left"] { margin-left: 0 !important; }
                        .docx-preview [style*="font-size"] { font-size: inherit !important; }
                        .docx-preview h1, .docx-preview h2, .docx-preview h3 { font-size: 1.3em !important; }
                        .docx-preview p, .docx-preview div, .docx-preview span { line-height: 1.6 !important; }
                    </style></head><body>';
                    echo $result['html'];
                    echo '</body></html>';
                    exit;
                }
            }

            header('Content-Type: ' . $fileData['type']);
            header('Content-Disposition: inline; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($fileData['path']));
            header('Cache-Control: no-cache, must-revalidate');
            header('X-Content-Type-Options: nosniff');

            readfile($fileData['path']);
            exit;

        } catch (Throwable $e) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
            $message = htmlspecialchars($e->getMessage());
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>',
                 'html,body{margin:0;padding:0;font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f3f4f6;color:#1f2937;height:100%;display:flex;align-items:center;justify-content:center;text-align:center;}',
                 '.container{padding:2rem;max-width:500px;}.icon{font-size:3rem;margin-bottom:1rem;}',
                 'h1{font-size:1.25rem;margin-bottom:0.5rem;}p{font-size:0.875rem;color:#6b7280;line-height:1.5;}',
                 'a{color:#dc2626;text-decoration:underline;font-weight:600;}</style></head><body>',
                 '<div class="container"><div class="icon">⚠️</div><h1>Preview not available</h1>',
                 '<p>' . $message . '</p>',
                 '</div></body></html>';
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
                    $cleanId = Sanitizer::int($id, 0);
                    if ($cleanId <= 0) continue;
                    $this->documentService->deleteDocument($cleanId);
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
    /**
     * Generate reference number for a document type
     */
    public function generateReference($type, $year = null) {
        try {
            if (empty($type)) {
                throw new Exception("Document type is required");
            }
            
            $model = new Document($this->db);
            $referenceNumber = $model->generateReferenceNumber($type, $year);
            
            return [
                'success' => true,
                'reference_number' => $referenceNumber
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
