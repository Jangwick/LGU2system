<?php
/**
 * Legislative Archive RESTful API
 *
 * External-facing API for interacting with the Legislative Archive.
 * Uses API key authentication via IntegrationAuth (X-API-Key or Bearer token).
 *
 * Endpoints:
 *   GET    ?action=list            List documents (paginated, filterable)
 *   GET    ?action=get&id={id}      Get single document with details
 *   GET    ?action=search&q={query} Full-text search documents
 *   GET    ?action=download&id={id} Download document file
 *   GET    ?action=stats             Get archive statistics
 *   GET    ?action=types             List available document types
 *   POST   ?action=create            Create/upload a document
 *   PUT    ?action=update&id={id}    Update document metadata
 *   DELETE ?action=delete&id={id}    Soft-delete a document
 *
 * Headers:
 *   X-API-Key: <api_key>   or   Authorization: Bearer <api_key>
 *
 * @package Legislative Archive API
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-API-Name: Legislative Archive API');
header('X-API-Version: v1');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

// Load archive API config
$archiveConfig = require __DIR__ . '/../config/archive_api.php';

// ─── Authentication ──────────────────────────────────────────────
$authService = new IntegrationAuth();
$authResult = $authService->validateApiKey();

if (!$authResult) {
    http_response_code(401);
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required. Provide X-API-Key header or Authorization: Bearer token.',
        'api_name' => $archiveConfig['api_name'],
        'version' => $archiveConfig['api_version'],
    ]);
    exit;
}

$apiKeyId = $authResult['id'] ?? null;
$apiKeyModule = $authResult['module_name'] ?? 'unknown';

// ─── Permission Helper ─────────────────────────────────────────
// Accepts dedicated archive_* permissions, 'all', or the legacy
// 'send_file' permission (which existing integration keys already have).
function hasArchivePermission($authService, $authResult, $permission) {
    return $authService->hasPermission($authResult, $permission) ||
           $authService->hasPermission($authResult, 'all') ||
           $authService->hasPermission($authResult, 'send_file');
}

// ─── Request Parsing ────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$action = Sanitizer::plainText($_GET['action'] ?? '');

// For PUT/DELETE, parse the body
$putData = [];
if ($method === 'PUT' || $method === 'PATCH') {
    $rawInput = file_get_contents('php://input');
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $putData = json_decode($rawInput, true) ?? [];
    } else {
        parse_str($rawInput, $putData);
    }
}

// ─── Database Setup ─────────────────────────────────────────────
$db = getDatabase();
$documentModel = new Document($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$documentService = new DocumentService($documentModel, $fileStorageService, $logger);

// ─── Route Handler ──────────────────────────────────────────────
try {
    switch (strtoupper($method)) {
        case 'GET':
            handleGet($action, $archiveConfig, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
            break;
        case 'POST':
            handlePost($action, $archiveConfig, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
            break;
        case 'PUT':
        case 'PATCH':
            handlePut($action, $putData, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
            break;
        case 'DELETE':
            handleDelete($action, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  GET Handlers
// ═══════════════════════════════════════════════════════════════

function handleGet($action, $config, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    switch ($action) {
        case 'list':
            apiListDocuments($config, $authService, $authResult, $documentModel);
            break;
        case 'get':
            apiGetDocument($authService, $authResult, $documentModel, $db);
            break;
        case 'search':
            apiSearchDocuments($config, $authService, $authResult, $documentModel);
            break;
        case 'download':
            apiDownloadDocument($authService, $authResult, $documentModel, $documentService);
            break;
        case 'stats':
            apiGetStats($authService, $authResult, $documentModel, $db);
            break;
        case 'types':
            apiGetTypes($authService, $authResult);
            break;
        case '':
            // API info / health check
            echo json_encode([
                'success' => true,
                'api_name' => $config['api_name'],
                'version' => $config['api_version'],
                'authenticated_as' => $authResult['module_name'] ?? 'unknown',
                'endpoints' => [
                    'GET  ?action=list'            => 'List documents (paginated, filterable)',
                    'GET  ?action=get&id={id}'      => 'Get single document with details',
                    'GET  ?action=search&q={query}' => 'Full-text search documents',
                    'GET  ?action=download&id={id}' => 'Download document file',
                    'GET  ?action=stats'             => 'Get archive statistics',
                    'GET  ?action=types'             => 'List available document types',
                    'POST ?action=create'            => 'Create/upload a document',
                    'PUT  ?action=update&id={id}'    => 'Update document metadata',
                    'DELETE ?action=delete&id={id}' => 'Soft-delete a document',
                ],
            ]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Unknown action: {$action}"]);
    }
}

/**
 * GET ?action=list
 * List documents with pagination and filters
 */
function apiListDocuments($config, $authService, $authResult, $documentModel) {
    if (!hasArchivePermission($authService, $authResult, 'archive_read')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_read permission.']);
        return;
    }

    $page = Sanitizer::int($_GET['page'] ?? 1, 1);
    $perPage = Sanitizer::int($_GET['per_page'] ?? $config['default_per_page'], $config['default_per_page']);
    $perPage = min($perPage, $config['max_per_page']);

    $filters = [
        'search' => Sanitizer::plainText($_GET['search'] ?? ''),
        'type' => Sanitizer::plainText($_GET['type'] ?? ''),
        'status' => Sanitizer::enum($_GET['status'] ?? '', ['draft', 'pending', 'approved', 'rejected', 'archived'], ''),
        'compliance_status' => Sanitizer::enum($_GET['compliance_status'] ?? '', ['pending', 'compliant', 'non_compliant'], ''),
        'date_from' => Sanitizer::date($_GET['date_from'] ?? ''),
        'date_to' => Sanitizer::date($_GET['date_to'] ?? ''),
        'tags' => Sanitizer::plainText($_GET['tags'] ?? ''),
        'reference' => Sanitizer::plainText($_GET['reference'] ?? ''),
        'source_system' => Sanitizer::plainText($_GET['source_system'] ?? ''),
        'sort_by' => Sanitizer::enum($_GET['sort_by'] ?? 'created_at', ['created_at', 'title', 'document_date', 'file_size', 'reference_number'], 'created_at'),
        'sort_dir' => Sanitizer::enum($_GET['sort_dir'] ?? 'DESC', ['ASC', 'DESC'], 'DESC'),
        'limit' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];

    $documents = $documentModel->getAll($filters);
    $total = $documentModel->getCount($filters);

    // Strip sensitive fields from output
    $safeDocuments = array_map(function($doc) {
        unset($doc['encryption_key'], $doc['extracted_text']);
        return $doc;
    }, $documents);

    echo json_encode([
        'success' => true,
        'documents' => $safeDocuments,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => (int)$total,
            'total_pages' => $total > 0 ? (int)ceil($total / $perPage) : 0,
        ],
        'filters_applied' => array_filter($filters, function($v) { return $v !== '' && $v !== null; }),
    ]);
}

/**
 * GET ?action=get&id={id}
 * Get single document with full details, versions, related docs, activity
 */
function apiGetDocument($authService, $authResult, $documentModel, $db) {
    if (!hasArchivePermission($authService, $authResult, 'archive_read')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_read permission.']);
        return;
    }

    $id = Sanitizer::int($_GET['id'] ?? 0, 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Document ID is required. Use ?id={id}']);
        return;
    }

    $document = $documentModel->getById($id);
    if (!$document) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        return;
    }

    // Strip sensitive fields
    unset($document['encryption_key']);

    $versions = $documentModel->getVersions($id);
    $related = $documentModel->getRelated($id);
    $activity = $documentModel->getActivity($id);

    echo json_encode([
        'success' => true,
        'document' => $document,
        'versions' => $versions,
        'related' => $related,
        'activity' => $activity,
    ]);
}

/**
 * GET ?action=search&q={query}
 * Full-text search across title, reference_number, description, extracted_text
 */
function apiSearchDocuments($config, $authService, $authResult, $documentModel) {
    if (!hasArchivePermission($authService, $authResult, 'archive_read')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_read permission.']);
        return;
    }

    $query = Sanitizer::plainText($_GET['q'] ?? '');
    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Search query is required. Use ?q={query}']);
        return;
    }

    $page = Sanitizer::int($_GET['page'] ?? 1, 1);
    $perPage = Sanitizer::int($_GET['per_page'] ?? $config['default_per_page'], $config['default_per_page']);
    $perPage = min($perPage, $config['max_per_page']);

    $filters = [
        'search' => $query,
        'type' => Sanitizer::plainText($_GET['type'] ?? ''),
        'status' => Sanitizer::enum($_GET['status'] ?? '', ['draft', 'pending', 'approved', 'rejected', 'archived'], ''),
        'date_from' => Sanitizer::date($_GET['date_from'] ?? ''),
        'date_to' => Sanitizer::date($_GET['date_to'] ?? ''),
        'tags' => Sanitizer::plainText($_GET['tags'] ?? ''),
        'sort_by' => Sanitizer::enum($_GET['sort_by'] ?? 'created_at', ['created_at', 'title', 'document_date', 'file_size', 'reference_number'], 'created_at'),
        'sort_dir' => Sanitizer::enum($_GET['sort_dir'] ?? 'DESC', ['ASC', 'DESC'], 'DESC'),
        'limit' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];

    $documents = $documentModel->getAll($filters);
    $total = $documentModel->getCount($filters);

    $safeDocuments = array_map(function($doc) {
        unset($doc['encryption_key'], $doc['extracted_text']);
        return $doc;
    }, $documents);

    echo json_encode([
        'success' => true,
        'query' => $query,
        'documents' => $safeDocuments,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => (int)$total,
            'total_pages' => $total > 0 ? (int)ceil($total / $perPage) : 0,
        ],
    ]);
}

/**
 * GET ?action=download&id={id}
 * Download the actual document file
 */
function apiDownloadDocument($authService, $authResult, $documentModel, $documentService) {
    if (!hasArchivePermission($authService, $authResult, 'archive_read')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_read permission.']);
        return;
    }

    $id = Sanitizer::int($_GET['id'] ?? 0, 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Document ID is required.']);
        return;
    }

    try {
        $document = $documentModel->getById($id);
        if (!$document) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Document not found']);
            return;
        }

        // Resolve file path
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));
        $filePath = $basePath . '/' . $document['file_path'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'File not found on disk']);
            return;
        }

        // Decrypt if encrypted
        if ($document['is_encrypted'] ?? false) {
            $encryptionService = new EncryptionService();
            $fileKey = null;

            if (!empty($document['encryption_key'])) {
                $keyResult = $encryptionService->decryptFileKey($document['encryption_key']);
                if ($keyResult['success']) {
                    $fileKey = $keyResult['file_key'];
                }
            }

            $decryptionResult = $encryptionService->decryptFile($filePath, $fileKey);
            if (!$decryptionResult['success']) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'File decryption failed: ' . $decryptionResult['error']]);
                return;
            }

            // Write decrypted content to temp file
            $tempPath = sys_get_temp_dir() . '/' . basename($filePath);
            file_put_contents($tempPath, $decryptionResult['content']);
            $filePath = $tempPath;
        }

        // Stream the file
        header('Content-Type: ' . $document['file_type']);
        header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');

        ob_clean();
        readfile($filePath);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * GET ?action=stats
 * Get archive statistics (counts by type, status, compliance)
 */
function apiGetStats($authService, $authResult, $documentModel, $db) {
    if (!hasArchivePermission($authService, $authResult, 'archive_read')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_read permission.']);
        return;
    }

    // Total count
    $total = $documentModel->getCount([]);

    // Counts by status
    $stmt = $db->prepare("
        SELECT status, COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL 
        GROUP BY status
    ");
    $stmt->execute();
    $byStatus = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byStatus[$row['status']] = (int)$row['count'];
    }

    // Counts by document type
    $stmt = $db->prepare("
        SELECT document_type, COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL 
        GROUP BY document_type
        ORDER BY count DESC
    ");
    $stmt->execute();
    $byType = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byType[$row['document_type']] = (int)$row['count'];
    }

    // Counts by compliance status
    $stmt = $db->prepare("
        SELECT compliance_status, COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL 
        GROUP BY compliance_status
    ");
    $stmt->execute();
    $byCompliance = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byCompliance[$row['compliance_status'] ?? 'pending'] = (int)$row['count'];
    }

    // Counts by source system
    $stmt = $db->prepare("
        SELECT ir.source_system, COUNT(*) as count 
        FROM legislative_documents ld
        LEFT JOIN integrated_records ir ON ld.source_id = ir.id
        WHERE ld.deleted_at IS NULL AND ld.source_module = 'integration'
        GROUP BY ir.source_system
        ORDER BY count DESC
    ");
    $stmt->execute();
    $bySource = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $bySource[$row['source_system'] ?? 'unknown'] = (int)$row['count'];
    }

    // Recent uploads (last 7 days)
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $recentUploads = (int)$stmt->fetchColumn();

    // OCR status counts
    $stmt = $db->prepare("
        SELECT ocr_status, COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL 
        GROUP BY ocr_status
    ");
    $stmt->execute();
    $byOcrStatus = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byOcrStatus[$row['ocr_status'] ?? 'pending'] = (int)$row['count'];
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_documents' => (int)$total,
            'recent_uploads_7d' => $recentUploads,
            'by_status' => $byStatus,
            'by_type' => $byType,
            'by_compliance' => $byCompliance,
            'by_source_system' => $bySource,
            'by_ocr_status' => $byOcrStatus,
        ],
    ]);
}

/**
 * GET ?action=types
 * List available document types and their reference prefixes
 */
function apiGetTypes($authService, $authResult) {
    if (!hasArchivePermission($authService, $authResult, 'archive_read')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_read permission.']);
        return;
    }

    $types = [
        ['type' => 'ordinance', 'prefix' => 'ORD', 'label' => 'Ordinance'],
        ['type' => 'resolution', 'prefix' => 'RES', 'label' => 'Resolution'],
        ['type' => 'session', 'prefix' => 'SES', 'label' => 'Session'],
        ['type' => 'agenda', 'prefix' => 'AGD', 'label' => 'Agenda'],
        ['type' => 'committee', 'prefix' => 'COM', 'label' => 'Committee'],
        ['type' => 'voting', 'prefix' => 'VOT', 'label' => 'Voting'],
        ['type' => 'hearing', 'prefix' => 'HRG', 'label' => 'Hearing'],
        ['type' => 'archive', 'prefix' => 'ARC', 'label' => 'Archive'],
        ['type' => 'consultation', 'prefix' => 'CON', 'label' => 'Consultation'],
        ['type' => 'research', 'prefix' => 'RSC', 'label' => 'Research'],
    ];

    $statuses = [
        ['status' => 'draft', 'label' => 'Draft'],
        ['status' => 'pending', 'label' => 'Pending Review'],
        ['status' => 'approved', 'label' => 'Approved'],
        ['status' => 'rejected', 'label' => 'Rejected'],
        ['status' => 'archived', 'label' => 'Archived'],
    ];

    $complianceStatuses = [
        ['status' => 'pending', 'label' => 'Compliance Pending'],
        ['status' => 'compliant', 'label' => 'Compliant'],
        ['status' => 'non_compliant', 'label' => 'Non-Compliant'],
    ];

    echo json_encode([
        'success' => true,
        'document_types' => $types,
        'statuses' => $statuses,
        'compliance_statuses' => $complianceStatuses,
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  POST Handler - Create Document
// ═══════════════════════════════════════════════════════════════

function handlePost($action, $config, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    if ($action === 'create') {
        apiCreateDocument($config, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unknown POST action: {$action}"]);
    }
}

/**
 * POST ?action=create
 * Create a new document with file upload
 * Content-Type: multipart/form-data
 * Fields: file, title, document_type, document_date, description, tags, reference_number (optional)
 */
function apiCreateDocument($config, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    if (!hasArchivePermission($authService, $authResult, 'archive_write')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_write permission.']);
        return;
    }

    // Validate required fields
    $title = Sanitizer::plainText($_POST['title'] ?? '');
    $documentType = Sanitizer::plainText($_POST['document_type'] ?? '');

    if (empty($title) || empty($documentType)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields: title, document_type']);
        return;
    }

    // Validate file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
        return;
    }

    $file = $_FILES['file'];

    // Validate file size
    if ($file['size'] > $config['max_file_size']) {
        http_response_code(413);
        echo json_encode(['success' => false, 'error' => 'File too large. Maximum ' . ($config['max_file_size'] / 1048576) . 'MB.']);
        return;
    }

    // Validate file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $config['allowed_file_types'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $config['allowed_file_types'])]);
        return;
    }

    // Detect MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // Map extension to proper MIME if finfo returns generic type
    $extToMime = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    $genericMimes = [
        'application/octet-stream', 'application/vnd.ms-office', 'application/x-ole-storage',
        'application/zip', 'text/html', 'text/plain', 'application/x-empty',
        'application/CDFV2', 'application/x-cfb',
    ];

    if (in_array($mimeType, $genericMimes)) {
        $mimeType = $extToMime[$ext] ?? $mimeType;
    }

    // Prepare data
    $data = [
        'title' => $title,
        'document_type' => $documentType,
        'document_date' => Sanitizer::date($_POST['document_date'] ?? date('Y-m-d')),
        'status' => Sanitizer::enum($_POST['status'] ?? 'draft', ['draft', 'pending', 'approved', 'rejected', 'archived'], 'draft'),
        'description' => Sanitizer::richText($_POST['description'] ?? ''),
        'tags' => Sanitizer::plainText($_POST['tags'] ?? ''),
        'reference_number' => Sanitizer::plainText($_POST['reference_number'] ?? ''),
    ];

    // Override session user with API key owner for audit
    $_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;

    $fileData = [
        'tmp_name' => $file['tmp_name'],
        'name' => $file['name'],
        'size' => $file['size'],
        'type' => $mimeType,
        'error' => $file['error'],
    ];

    try {
        // External API clients time out on long synchronous OCR; queue for worker
        $result = $documentService->createDocument($data, $fileData, false);

        // Log API access
        $logger->logActivity(
            'ARCHIVE_API_CREATE',
            'legislative_documents',
            $result['document_id'] ?? 0,
            "Document '{$title}' created via Archive API by key #{$apiKeyId} ({$authResult['module_name']})"
        );

        if ($result['success'] ?? false) {
            $workerPath = __DIR__ . '/ocr_worker.php';
            $binary = defined('PHP_BINARY') ? PHP_BINARY : 'php8.2';
            $command = 'nohup ' . escapeshellarg($binary) . ' ' . escapeshellarg($workerPath) . ' > /dev/null 2>&1 &';
            @shell_exec($command);

            http_response_code(201);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create document: ' . $e->getMessage()]);
    }
}

// ═══════════════════════════════════════════════════════════════
//  PUT/PATCH Handler - Update Document
// ═══════════════════════════════════════════════════════════════

function handlePut($action, $putData, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    if ($action === 'update') {
        apiUpdateDocument($putData, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unknown PUT action: {$action}"]);
    }
}

/**
 * PUT ?action=update&id={id}
 * Update document metadata (title, type, date, status, description, tags)
 * Content-Type: application/json or application/x-www-form-urlencoded
 */
function apiUpdateDocument($putData, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    if (!hasArchivePermission($authService, $authResult, 'archive_write')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_write permission.']);
        return;
    }

    $id = Sanitizer::int($_GET['id'] ?? 0, 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Document ID is required. Use ?id={id}']);
        return;
    }

    $document = $documentModel->getById($id);
    if (!$document) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        return;
    }

    // Build update data from PUT body
    $data = [];
    if (isset($putData['title'])) $data['title'] = Sanitizer::plainText($putData['title']);
    if (isset($putData['document_type'])) $data['document_type'] = Sanitizer::plainText($putData['document_type']);
    if (isset($putData['document_date'])) $data['document_date'] = Sanitizer::date($putData['document_date']);
    if (isset($putData['status'])) $data['status'] = Sanitizer::enum($putData['status'], ['draft', 'pending', 'approved', 'rejected', 'archived'], '');
    if (isset($putData['description'])) $data['description'] = Sanitizer::richText($putData['description']);
    if (isset($putData['tags'])) $data['tags'] = Sanitizer::plainText($putData['tags']);

    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No fields to update. Provide at least one of: title, document_type, document_date, status, description, tags']);
        return;
    }

    $result = $documentModel->update($id, $data);

    if ($result) {
        // Log the update
        $logger->logActivity(
            'ARCHIVE_API_UPDATE',
            'legislative_documents',
            $id,
            "Document #{$id} updated via Archive API by key #{$apiKeyId} ({$authResult['module_name']}). Fields: " . implode(', ', array_keys($data))
        );

        // Return updated document
        $updated = $documentModel->getById($id);
        unset($updated['encryption_key']);

        echo json_encode([
            'success' => true,
            'message' => 'Document updated successfully',
            'document' => $updated,
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update document']);
    }
}

// ═══════════════════════════════════════════════════════════════
//  DELETE Handler - Soft Delete Document
// ═══════════════════════════════════════════════════════════════

function handleDelete($action, $authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    if ($action === 'delete') {
        apiDeleteDocument($authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unknown DELETE action: {$action}"]);
    }
}

/**
 * DELETE ?action=delete&id={id}
 * Soft-delete a document (moves to trash, can be restored)
 */
function apiDeleteDocument($authService, $authResult, $documentModel, $documentService, $db, $logger, $apiKeyId) {
    if (!hasArchivePermission($authService, $authResult, 'archive_delete')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied. Requires archive_delete permission.']);
        return;
    }

    $id = Sanitizer::int($_GET['id'] ?? 0, 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Document ID is required. Use ?id={id}']);
        return;
    }

    $document = $documentModel->getById($id);
    if (!$document) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        return;
    }

    $result = $documentModel->delete($id);

    if ($result) {
        $logger->logActivity(
            'ARCHIVE_API_DELETE',
            'legislative_documents',
            $id,
            "Document #{$id} ('{$document['title']}') soft-deleted via Archive API by key #{$apiKeyId} ({$authResult['module_name']})"
        );

        echo json_encode([
            'success' => true,
            'message' => 'Document moved to trash successfully',
            'document_id' => $id,
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete document']);
    }
}
