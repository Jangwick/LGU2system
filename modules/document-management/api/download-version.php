<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/DocumentVersion.php';
require_once __DIR__ . '/../services/VersionService.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$versionModel = new DocumentVersion($db);
$documentModel = new Document($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$versionService = new VersionService($versionModel, $documentModel, $fileStorageService, $logger);

$versionId = $_GET['id'] ?? null;

if (!$versionId) {
    http_response_code(400);
    exit('Version ID is required');
}

$result = $versionService->downloadVersion($versionId, $_SESSION['user_id']);

if (!$result['success']) {
    http_response_code(404);
    exit($result['error']);
}

$filePath = $result['file_path'];
$fileName = $result['file_name'];

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($filePath);
exit;
