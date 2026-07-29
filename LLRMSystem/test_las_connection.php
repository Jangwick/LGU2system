<?php
// Test LAS connection and sendToLAS flow
require_once __DIR__ . '/modules/core/config/config.php';

echo "=== LAS Connection Test ===\n";

// Check curl availability
echo "curl_init: " . (function_exists('curl_init') ? 'YES' : 'NO') . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'YES' : 'NO') . "\n";

// Load LAS config
$lasConfigPath = __DIR__ . '/modules/integration/config/las.php';
if (!file_exists($lasConfigPath)) {
    echo "ERROR: LAS config not found at $lasConfigPath\n";
    exit(1);
}
$lasConfig = require $lasConfigPath;
echo "LAS base_url: " . $lasConfig['base_url'] . "\n";
echo "LAS create_endpoint: " . $lasConfig['create_endpoint'] . "\n";
echo "LAS bearer_token: " . substr($lasConfig['bearer_token'], 0, 6) . "...\n";

// Build full URL
$apiUrl = rtrim($lasConfig['base_url'], '/') . '/' . ltrim($lasConfig['create_endpoint'], '/');
echo "Full API URL: $apiUrl\n";

// Test basic connectivity
echo "\n=== Testing API connectivity ===\n";
if (function_exists('curl_init')) {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $lasConfig['bearer_token']]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "HTTP Code: $httpCode\n";
    echo "Error: " . ($err ?: 'none') . "\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
} else {
    echo "curl not available, trying file_get_contents...\n";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'X-API-Key: ' . $lasConfig['bearer_token'],
            'timeout' => 15,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    $response = @file_get_contents($apiUrl, false, $context);
    if ($response === false) {
        echo "file_get_contents FAILED\n";
    } else {
        echo "Response: " . substr($response, 0, 500) . "\n";
        if (isset($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (strpos($h, 'HTTP') === 0) echo "Status: $h\n";
            }
        }
    }
}

// Now test the actual sendToLAS flow
echo "\n=== Testing sendToLAS flow ===\n";
require_once __DIR__ . '/modules/core/config/database.php';
require_once __DIR__ . '/modules/document-management/models/Document.php';
require_once __DIR__ . '/modules/document-management/services/DocumentService.php';
require_once __DIR__ . '/modules/document-management/services/FileStorageService.php';
require_once __DIR__ . '/modules/document-management/services/EncryptionService.php';
require_once __DIR__ . '/modules/document-management/services/OcrService.php';
require_once __DIR__ . '/modules/document-management/services/SummarizationService.php';
require_once __DIR__ . '/modules/core/utils/Logger.php';

$db = getDatabase();
$documentModel = new Document($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$documentService = new DocumentService($documentModel, $fileStorageService, $logger);

// Find a document to test with
$docId = $argv[1] ?? null;
if (!$docId) {
    // Find the most recent document
    $stmt = $db->query("SELECT id, title, status, file_path, is_encrypted FROM legislative_documents WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5");
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Recent documents:\n";
    foreach ($docs as $d) {
        echo "  ID: {$d['id']}, Title: {$d['title']}, Status: {$d['status']}, Encrypted: " . ($d['is_encrypted'] ? 'YES' : 'NO') . "\n";
    }
    if (empty($docs)) {
        echo "No documents found.\n";
        exit(1);
    }
    echo "\nUsage: php test_las_connection.php <document_id>\n";
    exit(0);
}

echo "Testing with document ID: $docId\n";
$doc = $documentModel->getById($docId);
if (!$doc) {
    echo "Document not found.\n";
    exit(1);
}
echo "Title: " . $doc['title'] . "\n";
echo "Status: " . $doc['status'] . "\n";
echo "File path: " . $doc['file_path'] . "\n";
echo "Is encrypted: " . ($doc['is_encrypted'] ? 'YES' : 'NO') . "\n";
echo "File exists: " . (file_exists($doc['file_path']) ? 'YES' : 'NO') . "\n";

// Use reflection to call private sendToLAS
$reflection = new ReflectionClass($documentService);
$method = $reflection->getMethod('sendToLAS');
$method->setAccessible(true);
echo "\nCalling sendToLAS...\n";
$result = $method->invoke($documentService, $docId);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
