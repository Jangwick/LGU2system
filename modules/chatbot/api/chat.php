<?php
/**
 * Chatbot API Endpoint
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/ChatbotController.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['message'])) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit;
}

$message = $input['message'];
$history = $input['history'] ?? [];

$controller = new ChatbotController();
$result = $controller->ask($message, $history);

if (isset($result['details'])) {
    // Log for debugging
    error_log("Chatbot Detail: " . json_encode($result['details']));
}

echo json_encode($result);
