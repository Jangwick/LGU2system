<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/IntegrationController.php';

// Simulate API authentication (in production use Bearer tokens)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
// if ($apiKey !== 'LGU-SECRET-KEY') {
//     echo json_encode(['success' => false, 'error' => 'Unauthorized']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Handle both JSON and FormData (multipart)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    // FormData submission (with file)
    $input = [
        'module_type' => $_POST['module_type'] ?? null,
        'source_system' => $_POST['source_system'] ?? null,
        'external_id' => $_POST['external_id'] ?? null,
        'title' => $_POST['title'] ?? null,
        'summary' => $_POST['summary'] ?? null,
        'payload' => [
            'document_date' => $_POST['document_date'] ?? date('Y-m-d'),
            'tags' => $_POST['tags'] ?? ''
        ]
    ];
}

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

// Handle file upload
$fileData = null;
if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['document_file'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'File too large. Maximum 10MB.']);
        exit;
    }
    
    // Validate file type (MIME + extension fallback)
    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/octet-stream',
        'application/vnd.ms-office',
        'application/x-ole-storage',
        'application/zip'
    ];
    
    $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file extension. Allowed: PDF, Word, Excel, PowerPoint.']);
        exit;
    }
    
    // Map extension to proper MIME if finfo returns generic type
    $extToMime = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];
    
    // Use the proper MIME based on extension if finfo returned a generic/wrong type
    $genericMimes = [
        'application/octet-stream', 'application/vnd.ms-office', 'application/x-ole-storage',
        'application/zip', 'text/html', 'text/plain', 'application/x-empty',
        'application/CDFV2', 'application/x-cfb'
    ];
    if (in_array($mimeType, $genericMimes)) {
        $mimeType = $extToMime[$ext] ?? $mimeType;
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeFilename = 'INT_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    // Ensure upload directory exists
    $uploadDir = __DIR__ . '/../../../storage/documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destPath = $uploadDir . $safeFilename;
    
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
        exit;
    }
    
    $fileData = [
        'file_path' => 'storage/documents/' . $safeFilename,
        'file_name' => $file['name'],
        'file_size' => $file['size'],
        'file_type' => $mimeType
    ];
}

$controller = new IntegrationController();
$result = $controller->receive($input, $fileData);

echo json_encode($result);
