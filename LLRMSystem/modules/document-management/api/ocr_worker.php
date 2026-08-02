<?php
/**
 * OCR Worker Endpoint
 * 
 * Processes documents with pending OCR status. Can be called by:
 * - Cron job: php ocr_worker.php
 * - JavaScript polling from admin panel
 * - Manual trigger via browser
 * 
 * Processes one document at a time to avoid server overload.
 * Secured via session auth or API key.
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(0);

try {
    header('Content-Type: application/json');

    // Authentication: session or API key
    $authenticated = false;

    // Try session auth first
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        $authenticated = true;
    }

    // Try API key auth
    if (!$authenticated) {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if (!empty($apiKey)) {
            require_once __DIR__ . '/../../core/config/database.php';
            require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';
            $authService = new IntegrationAuth(getDatabase());
            if ($authService->validateApiKey($apiKey)) {
                $authenticated = true;
            }
        }
    }

    if (!$authenticated) {
        // Allow CLI execution (cron)
        if (php_sapi_name() === 'cli') {
            $authenticated = true;
        }
    }

    if (!$authenticated) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }

    require_once __DIR__ . '/../../core/config/database.php';
    require_once __DIR__ . '/../models/Document.php';
    require_once __DIR__ . '/../services/DocumentService.php';
    require_once __DIR__ . '/../services/OcrService.php';
    require_once __DIR__ . '/../services/SummarizationService.php';
    require_once __DIR__ . '/../../core/utils/Logger.php';

    $db = getDatabase();
    $documentModel = new Document($db);
    $logger = new Logger($db);
    $documentService = new DocumentService($documentModel, null, $logger);

    // Process all pending documents in a loop
    $processed = 0;
    $results = [];

    while (true) {
        $pending = $documentModel->getPendingOcr(1);
        if (empty($pending)) {
            break;
        }

        $doc = $pending[0];
        $documentId = $doc['id'];

        // Process the document
        $result = $documentService->runOcrOnDocument($documentId);
        $results[] = [
            'document_id' => $documentId,
            'success' => $result['success'],
            'ocr_status' => $result['ocr_status'] ?? null,
            'extracted_text_length' => $result['extracted_text_length'] ?? 0,
            'error' => $result['error'] ?? null
        ];

        if ($result['success']) {
            $processed++;
        }
    }

    ob_clean();
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'results' => $results,
        'message' => $processed > 0 ? "$processed OCR documents processed" : 'No pending OCR documents'
    ]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
