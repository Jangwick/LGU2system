<?php
/**
 * Get Document Compliance Analysis Results
 */

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/DocumentComplianceResult.php';

header('Content-Type: application/json');

CsrfMiddleware::requireValidToken();

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$documentId = Sanitizer::int($data['document_id'] ?? 0, 0);

if (!$documentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    $db = getDatabase();
    $documentModel = new Document($db);
    $document = $documentModel->getById($documentId);

    if (!$document) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }

    $resultModel = new DocumentComplianceResult($db);
    $results = $resultModel->getByDocumentId($documentId);

    echo json_encode([
        'success' => true,
        'compliance_status' => $document['compliance_status'] ?? 'pending',
        'compliance_checked_at' => $document['compliance_checked_at'] ?? null,
        'rejection_notes' => $document['rejection_notes'] ?? null,
        'document_status' => $document['status'] ?? 'draft',
        'results' => $results
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
