<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/IntegrationController.php';

// Simulate API authentication (in production use Bearer tokens)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
// if ($apiKey !== 'LGU-SECRET-KEY') {
//     echo json_encode(['success' => false, 'error' => 'Unauthorized']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Fallback to $_POST if not JSON
    $input = $_POST;
}

$controller = new IntegrationController();
$result = $controller->receive($input);

echo json_encode($result);
