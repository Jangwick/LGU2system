<?php
session_start();
require_once __DIR__ . '/../../core/config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDatabase();
$query = $_GET['q'] ?? '';

$sql = "SELECT id, title, reference_number, document_type, document_date 
        FROM legislative_documents 
        WHERE deleted_at IS NULL";

if (!empty($query)) {
    $sql .= " AND (title LIKE :q OR reference_number LIKE :q)";
    $stmt = $db->prepare($sql);
    $stmt->execute([':q' => "%$query%"]);
} else {
    $sql .= " ORDER BY created_at DESC LIMIT 20";
    $stmt = $db->prepare($sql);
    $stmt->execute();
}

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results);
