<?php
/**
 * External Document Tracking Event Endpoint
 *
 * Accepts tracking events from external systems.
 *
 * Headers:
 *   X-API-Key: <integration_api_key>
 *   Content-Type: application/json
 *
 * Body (JSON):
 *   tracking_id       : Reference number from LRMS
 *   source_system     : e.g. orts, cms, phms, pcms
 *   activity          : e.g. Transferred, Received, Approved
 *   local_document_id : optional ID in the source system
 *   status            : optional (defaults to activity)
 *   performed_by      : optional
 *   department        : optional
 *   remarks           : optional
 *   timestamp         : ISO 8601 (defaults to now)
 *   metadata          : optional object
 *   idempotency_key   : optional
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';
require_once __DIR__ . '/../../document-management/services/DocumentTrackingService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

// Authenticate API key
$authService = new IntegrationAuth($GLOBALS['db'] ?? getDatabase());
$authResult = $authService->validateApiKey();

if (!$authResult) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'API key required. Provide X-API-Key header or Authorization: Bearer token.']);
    exit;
}

// Read JSON or form body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data) || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON or empty body.']);
    exit;
}

// Map event_action / activity for flexibility
if (empty($data['activity']) && !empty($data['event_action'])) {
    $data['activity'] = $data['event_action'];
}

$required = ['tracking_id', 'source_system', 'activity'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
        exit;
    }
}

// Sanitize
$data['tracking_id'] = preg_replace('/[^a-zA-Z0-9\-_]/', '', $data['tracking_id']);
$data['source_system'] = preg_replace('/[^a-zA-Z0-9_]/', '', $data['source_system']);

$trackingService = new DocumentTrackingService();

try {
    $result = $trackingService->addExternalTrackingEvent($data);
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'event_id' => $result['event_id'],
        'idempotency_key' => $result['idempotency_key'],
        'message' => 'Tracking event recorded.',
    ]);
} catch (Exception $e) {
    $code = $e->getMessage() === 'Duplicate tracking event detected.' ? 409 : 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
