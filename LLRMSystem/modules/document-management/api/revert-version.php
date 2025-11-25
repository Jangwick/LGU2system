<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/DocumentVersion.php';
require_once __DIR__ . '/../services/VersionService.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$documentModel = new Document($db);
$versionModel = new DocumentVersion($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$versionService = new VersionService($versionModel, $documentModel, $fileStorageService, $logger);

$data = json_decode(file_get_contents('php://input'), true);

$documentId = $data['document_id'] ?? null;
$versionNumber = $data['version_number'] ?? null;

if (!$documentId || !$versionNumber) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    $result = $versionService->revertToVersion($documentId, $versionNumber, $_SESSION['user_id']);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
