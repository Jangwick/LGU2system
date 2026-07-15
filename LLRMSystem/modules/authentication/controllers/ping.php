<?php
require_once __DIR__ . '/../../core/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$_SESSION['last_activity'] = time();

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'time' => time()]);
