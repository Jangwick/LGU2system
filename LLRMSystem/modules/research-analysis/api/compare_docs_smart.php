<?php
/**
 * Smart Algorithmic Document Comparison API (Non-LLM)
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../services/AnalysisService.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (count($ids) < 2) {
    echo json_encode(['success' => false, 'error' => 'Select at least two documents']);
    exit;
}

try {
    $service = new AnalysisService();
    $analysis = $service->compareDocumentsAlgorithmic($ids);

    if ($analysis) {
        echo json_encode(['success' => true, 'analysis' => $analysis]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No analysis data generated.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
