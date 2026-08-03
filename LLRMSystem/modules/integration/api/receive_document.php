<?php
/**
 * Integration API: Receive Document from External System
 * 
 * Accepts file uploads from external ERP systems via API key authentication.
 * Creates both an integrated_records entry (for tracking) and a 
 * legislative_documents entry (for admin panel visibility).
 * Runs OCR on the file before encryption and generates key points.
 * 
 * Headers:
 *   X-API-Key: <integration_api_key>
 * 
 * Body (multipart/form-data):
 *   file: <document file>
 *   title: <document title>
 *   document_type: ordinance|resolution|session|agenda|committee|etc.
 *   source_system: <name of external system>
 *   external_id: <ID from external system>
 *   document_date: YYYY-MM-DD (optional, defaults to today)
 *   description: <document description> (optional)
 *   tags: <comma-separated tags> (optional)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';
require_once __DIR__ . '/../controllers/IntegrationController.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

// Validate API key
$authService = new IntegrationAuth($GLOBALS['db'] ?? getDatabase());
$authResult = $authService->validateApiKey();

if (!$authResult) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'API key required. Provide X-API-Key header or Authorization: Bearer token.']);
    exit;
}

// Check permissions for document receive (accept dedicated document_receive or existing send_file permission)
if (!$authService->hasPermission($authResult, 'document_receive') && !$authService->hasPermission($authResult, 'send_file') && !$authService->hasPermission($authResult, 'all')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'API key does not have document_receive or send_file permission.']);
    exit;
}

// Validate required fields
$requiredFields = ['title', 'document_type'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
        exit;
    }
}

// Validate file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
    exit;
}

$file = $_FILES['file'];
$maxSize = 50 * 1024 * 1024; // 50MB

if ($file['size'] > $maxSize) {
    http_response_code(413);
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum 50MB.']);
    exit;
}

// Validate file extension
$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)]);
    exit;
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
    'application/CDFV2', 'application/x-cfb'
];

if (in_array($mimeType, $genericMimes)) {
    $mimeType = $extToMime[$ext] ?? $mimeType;
}

// Prepare data for controller
$data = [
    'title' => Sanitizer::plainText($_POST['title']),
    'document_type' => Sanitizer::plainText($_POST['document_type']),
    'source_system' => Sanitizer::plainText($_POST['source_system'] ?? 'External ERP'),
    'external_id' => Sanitizer::plainText($_POST['external_id'] ?? null),
    'document_date' => Sanitizer::date($_POST['document_date'] ?? date('Y-m-d')),
    'description' => Sanitizer::richText($_POST['description'] ?? ''),
    'tags' => Sanitizer::plainText($_POST['tags'] ?? ''),
    'api_key_id' => $authResult['id'] ?? null,
    'tracking_id' => Sanitizer::plainText($_POST['tracking_id'] ?? null),
    'tracking_history' => $_POST['tracking_history'] ?? null,
];

$fileData = [
    'tmp_name' => $file['tmp_name'],
    'name' => $file['name'],
    'size' => $file['size'],
    'type' => $mimeType,
    'error' => $file['error'],
];

// Process via IntegrationController
$controller = new IntegrationController();
$result = $controller->receiveDocument($data, $fileData);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
