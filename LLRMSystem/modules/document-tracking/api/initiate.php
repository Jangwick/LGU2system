<?php
/**
 * Reserve a tracking ID before sending a document.
 *
 * Headers:
 *   X-API-Key: <integration_api_key>
 *   Content-Type: application/json
 *
 * Body (JSON):
 *   document_type   : one of the LRMS document types (ordinance, resolution, session, agenda, committee, voting, hearing, archive, consultation, research)
 *   source_system   : e.g. orts, cms, phms, pcms
 *   external_id     : optional
 *   title           : optional (defaults to Reserved)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

$authService = new IntegrationAuth($GLOBALS['db'] ?? getDatabase());
$authResult = $authService->validateApiKey();

if (!$authResult) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'API key required.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['document_type']) || empty($data['source_system'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: document_type and source_system.']);
    exit;
}

$documentType = strtolower(preg_replace('/[^a-z_]/', '', $data['document_type']));
$sourceSystem = strtolower(preg_replace('/[^a-z0-9_]/', '', $data['source_system']));
$externalId = $data['external_id'] ?? null;

$allowedTypes = ['ordinance', 'resolution', 'session', 'agenda', 'committee', 'voting', 'hearing', 'archive', 'consultation', 'research'];
if (!in_array($documentType, $allowedTypes, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid document_type. Allowed: ' . implode(', ', $allowedTypes)]);
    exit;
}

$db = getDatabase();

// Generate a unique reference number
$prefix = strtoupper(substr($documentType, 0, 3));
do {
    $refNum = $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    $check = $db->prepare('SELECT 1 FROM legislative_documents WHERE reference_number = ?');
    $check->execute([$refNum]);
} while ($check->fetch());

$title = 'Reserved for ' . strtoupper($sourceSystem);
if (!empty($data['title'])) {
    $title = $data['title'];
}

$stmt = $db->prepare("
    INSERT INTO legislative_documents (
        reference_number, title, document_type, document_date,
        status, compliance_status,
        file_path, file_name, file_size, file_type,
        description, tags, source_module, source_id,
        uploaded_by, is_encrypted, ocr_status
    ) VALUES (
        :reference_number, :title, :document_type, :document_date,
        'draft', 'pending',
        '', 'Reserved', 0, 'application/octet-stream',
        :description, :tags, :source_module, :source_id,
        1, 0, 'skipped'
    )
");

$stmt->execute([
    ':reference_number' => $refNum,
    ':title' => $title,
    ':document_type' => $documentType,
    ':document_date' => $data['document_date'] ?? date('Y-m-d'),
    ':description' => 'Reserved document placeholder for external integration.',
    ':tags' => 'reserved,external',
    ':source_module' => $sourceSystem,
    ':source_id' => $externalId ? (int) $externalId : null,
]);

$documentId = $db->lastInsertId();

http_response_code(201);
echo json_encode([
    'success' => true,
    'tracking_id' => $refNum,
    'tracking_url' => 'https://llrm.spvalenzuela.com/modules/document-tracking/api/document-events.php',
    'events_url' => 'https://llrm.spvalenzuela.com/modules/document-tracking/api/document-events.php',
    'receive_url' => 'https://llrm.spvalenzuela.com/modules/integration/api/receive_document.php',
    'document_id' => $documentId,
    'expires_at' => date('c', strtotime('+24 hours')),
    'message' => 'Tracking ID reserved. Use it when uploading the document and sending tracking events.',
]);
