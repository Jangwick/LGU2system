<?php
/**
 * OCR API Endpoint
 * 
 * GET  ?id=X          — Get OCR status, extracted text, and key points for a document
 * POST ?id=X          — Re-run OCR on an existing document (admin/officer/superadmin only)
 * GET  ?status=pending — Get documents with pending OCR (for async worker polling)
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once __DIR__ . '/../../core/config/config.php';

    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
    $method = $_SERVER['REQUEST_METHOD'];

    require_once __DIR__ . '/../models/Document.php';
    require_once __DIR__ . '/../services/DocumentService.php';
    require_once __DIR__ . '/../services/OcrService.php';
    require_once __DIR__ . '/../services/SummarizationService.php';
    require_once __DIR__ . '/../../core/utils/Logger.php';

    $db = getDatabase();
    $documentModel = new Document($db);
    $logger = new Logger($db);
    $documentService = new DocumentService($documentModel, null, $logger);

    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $status = $_GET['status'] ?? null;

        if ($status === 'pending') {
            // Get pending OCR documents for async worker
            $documents = $documentModel->getPendingOcr(5);
            ob_clean();
            echo json_encode(['success' => true, 'documents' => $documents]);
            exit;
        }

        if ($id) {
            $doc = $documentModel->getById($id);
            if (!$doc) {
                ob_clean();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Document not found']);
                exit;
            }

            ob_clean();
            echo json_encode([
                'success' => true,
                'ocr_status' => $doc['ocr_status'] ?? 'pending',
                'extracted_text' => $doc['extracted_text'] ?? null,
                'key_points' => $doc['key_points'] ?? null,
                'ocr_processed_at' => $doc['ocr_processed_at'] ?? null,
                'key_points_generated_at' => $doc['key_points_generated_at'] ?? null
            ]);
            exit;
        }

        ob_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing document ID or status parameter']);
        exit;

    } elseif ($method === 'POST') {
        // Re-run OCR on a document
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            // Try JSON body
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
        }

        if (!$id) {
            ob_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Document ID required']);
            exit;
        }

        // Only admin/officer/superadmin can re-run OCR
        if (!in_array($userRole, ['admin', 'administrator', 'officer', 'superadmin', 'super_admin'])) {
            ob_clean();
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only administrators can re-run OCR']);
            exit;
        }

        $result = $documentService->runOcrOnDocument($id);

        ob_clean();
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }

    ob_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
