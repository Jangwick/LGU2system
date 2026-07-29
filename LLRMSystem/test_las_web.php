<?php
// Web-based LAS transfer test — run via browser
session_start();
header('Content-Type: text/plain');

require_once __DIR__ . '/modules/core/config/config.php';
require_once __DIR__ . '/modules/core/config/database.php';
require_once __DIR__ . '/modules/document-management/models/Document.php';
require_once __DIR__ . '/modules/document-management/services/DocumentService.php';
require_once __DIR__ . '/modules/document-management/services/FileStorageService.php';
require_once __DIR__ . '/modules/document-management/services/EncryptionService.php';
require_once __DIR__ . '/modules/document-management/services/OcrService.php';
require_once __DIR__ . '/modules/document-management/services/SummarizationService.php';
require_once __DIR__ . '/modules/core/utils/Logger.php';

echo "=== LAS Transfer Test (Web) ===\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'YES' : 'NO') . "\n";
echo "curl_init: " . (function_exists('curl_init') ? 'YES' : 'NO') . "\n";

// Test API connectivity
echo "\n--- Testing Archive API ---\n";
$apiUrl = 'https://llrm.spvalenzuela.com/modules/document-management/api/archive.php?action=types';
$ctx = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "X-API-Key: ar_c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8",
        'timeout' => 10,
        'ignore_errors' => true
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);
$response = @file_get_contents($apiUrl, false, $ctx);
$httpCode = 0;
if (isset($http_response_header)) {
    foreach ($http_response_header as $h) {
        if (preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $h, $m)) $httpCode = (int)$m[1];
    }
}
echo "Archive API HTTP code: $httpCode\n";
echo "Archive API response: " . substr($response ?: 'false', 0, 200) . "\n";

// Get a document to test
$db = getDatabase();
$documentModel = new Document($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$documentService = new DocumentService($documentModel, $fileStorageService, $logger);

$docId = $_GET['id'] ?? null;
if (!$docId) {
    $stmt = $db->query("SELECT id, title, status, file_name FROM legislative_documents WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5");
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n--- Recent documents ---\n";
    foreach ($docs as $d) {
        echo "  ID: {$d['id']}, Status: {$d['status']}, File: {$d['file_name']}\n";
    }
    echo "\nAdd ?id=<document_id> to test sendToLAS\n";
    exit;
}

echo "\n--- Testing sendToLAS with document ID: $docId ---\n";
$doc = $documentModel->getById($docId);
if (!$doc) {
    echo "ERROR: Document not found\n";
    exit;
}

echo "Title: " . $doc['title'] . "\n";
echo "Status: " . $doc['status'] . "\n";
echo "File path: " . $doc['file_path'] . "\n";
echo "File exists: " . (file_exists($doc['file_path']) ? 'YES' : 'NO') . "\n";
echo "Is encrypted: " . ($doc['is_encrypted'] ? 'YES' : 'NO') . "\n";

$reflection = new ReflectionClass($documentService);
$method = $reflection->getMethod('sendToLAS');
$method->setAccessible(true);

echo "\nCalling sendToLAS...\n";
$result = $method->invoke($documentService, $docId);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

if ($result['success']) {
    echo "\nSUCCESS: Document sent to LAS! LAS Document ID: " . ($result['las_document_id'] ?? 'N/A') . "\n";
} else {
    echo "\nFAILED: " . ($result['error'] ?? 'Unknown error') . "\n";
}
