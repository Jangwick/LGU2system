<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

try {
    $db = getDatabase();
    $logger = new Logger($db);
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Validate password match
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }
    
    // Validate password strength (minimum 8 characters)
    if (strlen($new_password) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters']);
        exit;
    }
    
    // Get current user password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        // Log failed password change attempt
        $logger->logActivity(Logger::ACTION_PASSWORD_CHANGE, 'users', $_SESSION['user_id'],
            "Failed password change attempt - incorrect current password", null, null, Logger::SEVERITY_WARNING);
        
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Update password
    $updateStmt = $db->prepare("
        UPDATE users 
        SET password = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$hashed_password, $_SESSION['user_id']]);
    
    // Log successful password change
    $logger->logActivity(Logger::ACTION_PASSWORD_CHANGE, 'users', $_SESSION['user_id'],
        "User successfully changed their password");
    
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error changing password: ' . $e->getMessage()]);
}
