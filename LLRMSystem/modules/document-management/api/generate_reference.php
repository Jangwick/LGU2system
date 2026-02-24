<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../controllers/DocumentController.php';

$type = $_GET['type'] ?? '';
$year = $_GET['year'] ?? null;

$controller = new DocumentController();
$result = $controller->generateReference($type, $year);

if ($result['success']) {
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
