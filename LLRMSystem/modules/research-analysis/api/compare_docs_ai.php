<?php
/**
 * AI Document Comparison API
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

$service = new AnalysisService();
$analysis = $service->compareDocumentsAI($ids);

echo json_encode(['success' => true, 'analysis' => $analysis]);
