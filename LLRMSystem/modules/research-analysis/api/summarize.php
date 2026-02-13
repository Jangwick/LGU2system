<?php
/**
 * Summarization API
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../services/AnalysisService.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$documentId = $_GET['id'] ?? null;
if (!$documentId) {
    echo json_encode(['success' => false, 'error' => 'Missing document ID']);
    exit;
}

$service = new AnalysisService();
$summary = $service->summarizeDocument($documentId);

echo json_encode(['success' => true, 'summary' => $summary]);
