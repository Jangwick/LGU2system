<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(0);

try {
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
    CsrfMiddleware::requireValidToken();

    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
    if ($userRole === 'viewer') {
        ob_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied. Viewers cannot upload documents.']);
        exit;
    }

    require_once __DIR__ . '/../../core/config/config.php';
    require_once __DIR__ . '/../controllers/DocumentController.php';

    $controller = new DocumentController();
    $result = $controller->store();

    // Start the OCR worker in the background if the upload succeeded
    if (!empty($result['success'])) {
        $workerPath = __DIR__ . '/ocr_worker.php';
        $binary = defined('PHP_BINARY') ? PHP_BINARY : 'php8.2';
        $command = 'nohup ' . escapeshellarg($binary) . ' ' . escapeshellarg($workerPath) . ' > /dev/null 2>&1 &';
        @shell_exec($command);
    }

    ob_clean();

    if (!isset($result['success'])) {
        $result = [
            'success' => false,
            'error' => $result['error'] ?? 'Unknown upload error'
        ];
    }

    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
