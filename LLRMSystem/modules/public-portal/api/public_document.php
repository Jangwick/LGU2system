<?php
/**
 * Public Document Details API - No authentication required
 * Only returns details for approved documents
 * Never exposes file_path
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid document ID']);
    exit;
}

try {
    $db = getDatabase();

    $stmt = $db->prepare("
        SELECT d.id, d.reference_number, d.title, d.document_type, d.document_date,
               d.status, d.file_name, d.file_size, d.file_type, d.description, d.tags,
               d.created_at, d.updated_at, u.full_name
        FROM legislative_documents d
        LEFT JOIN users u ON d.uploaded_by = u.id
        WHERE d.id = :id
          AND d.deleted_at IS NULL
          AND d.status IN ('approved')
    ");

    $stmt->execute([':id' => (int)$id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        http_response_code(404);
        echo json_encode(['error' => 'Document not found or not publicly available']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'document' => $document
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while retrieving document details.']);
}
