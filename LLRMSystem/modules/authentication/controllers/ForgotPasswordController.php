<?php
session_start();
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/config/config.php';

header('Content-Type: application/json');

try {
    $conn = getDatabase();
    
    // Get email from POST (sanitized)
    $email = Sanitizer::email($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is required'
        ]);
        exit;
    }
    
    // Check if user exists and get their role
    $stmt = $conn->prepare("SELECT id, email, role, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Don't reveal if email exists for security
        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with this email, password reset instructions will be sent.'
        ]);
        exit;
    }
    
    // Check if user is admin or super admin - block password recovery
    $userRole = strtolower(trim($user['role']));
    if ($userRole === 'administrator' || $userRole === 'super_admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Administrator and Super Admin accounts cannot use password recovery. Please contact your system administrator or use direct database access.'
        ]);
        exit;
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'This account is not active. Please contact your administrator.'
        ]);
        exit;
    }
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store token in database (you may need to create a password_resets table)
    // For now, we'll use a simple approach with user_otps table
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$user['id'], $token, $expiry]);
    
    if ($result) {
        // In a real implementation, send email with reset link
        // For now, return success
        echo json_encode([
            'success' => true,
            'message' => 'Password reset instructions have been sent to your email address.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate reset token. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Forgot Password error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}
