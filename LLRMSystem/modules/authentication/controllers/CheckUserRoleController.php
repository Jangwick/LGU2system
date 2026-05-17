<?php
session_start();
require_once __DIR__ . '/../../core/config/database.php';

header('Content-Type: application/json');

try {
    $conn = getDatabase();
    
    // Get email from POST
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode(['is_admin' => false]);
        exit;
    }
    
    // Check if user exists and get their role
    $stmt = $conn->prepare("SELECT role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['is_admin' => false]);
        exit;
    }
    
    // Check if user is admin or super admin
    $userRole = strtolower(trim($user['role']));
    $isAdmin = ($userRole === 'administrator' || $userRole === 'super_admin');
    
    echo json_encode(['is_admin' => $isAdmin]);
    
} catch (Exception $e) {
    error_log("Check User Role error: " . $e->getMessage());
    echo json_encode(['is_admin' => false]);
}
