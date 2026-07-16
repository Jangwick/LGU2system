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
    
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $department = $_POST['department'] ?? '';
    $position = $_POST['position'] ?? '';
    
    // Get current profile for comparison
    $currentStmt = $db->prepare("SELECT full_name, username, email, phone, department, position FROM users WHERE id = ?");
    $currentStmt->execute([$_SESSION['user_id']]);
    $currentProfile = $currentStmt->fetch(PDO::FETCH_ASSOC);
    
    // Validate required fields
    if (empty($full_name) || empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        exit;
    }
    
    // Check if email is already taken by another user
    $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->execute([$email, $_SESSION['user_id']]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
        exit;
    }
    
    // Check if username is already taken by another user
    $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkStmt->execute([$username, $_SESSION['user_id']]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already in use']);
        exit;
    }
    
    // Update user profile
    $stmt = $db->prepare("
        UPDATE users 
        SET full_name = ?, 
            username = ?, 
            email = ?, 
            phone = ?, 
            department = ?, 
            position = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $full_name,
        $username,
        $email,
        $phone,
        $department,
        $position,
        $_SESSION['user_id']
    ]);
    
    // Update session variables
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_department'] = $department;
    
    // Log activity with detailed changes
    $newValues = [
        'full_name' => $full_name,
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'department' => $department,
        'position' => $position
    ];
    
    $logger->logActivity(Logger::ACTION_PROFILE_UPDATE, 'users', $_SESSION['user_id'],
        "User updated their profile", $newValues, $currentProfile);
    
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
}
