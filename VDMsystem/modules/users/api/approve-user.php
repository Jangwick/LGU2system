<?php
/**
 * VDMsystem - Approve/Reject User Registration API
 * Admin-only endpoint to approve or reject pending user registrations
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

// Check authentication & admin access
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userRole = strtolower(trim($_SESSION['user_role'] ?? ''));
if (!in_array($userRole, ['admin', 'administrator'])) {
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$userId = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null; // 'approve' or 'reject'

if (!$userId || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID or invalid action']);
    exit;
}

try {
    $db = getDatabase();
    
    // Get the user
    $stmt = $db->prepare("SELECT id, full_name, email, role, approval_status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    if ($user['approval_status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'User is not in pending status']);
        exit;
    }
    
    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE users SET approval_status = 'approved', is_active = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        
        logAudit(
            'user_approved',
            $_SESSION['user_id'],
            'users',
            'users',
            $userId,
            'Approved user registration: ' . $user['full_name'],
            ['email' => $user['email'], 'role' => $user['role']]
        );
        
        echo json_encode(['success' => true, 'message' => $user['full_name'] . ' has been approved and can now sign in.']);
    } else {
        $stmt = $db->prepare("UPDATE users SET approval_status = 'rejected', is_active = 0, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        
        logAudit(
            'user_rejected',
            $_SESSION['user_id'],
            'users',
            'users',
            $userId,
            'Rejected user registration: ' . $user['full_name'],
            ['email' => $user['email'], 'role' => $user['role']]
        );
        
        echo json_encode(['success' => true, 'message' => $user['full_name'] . '\'s registration has been declined.']);
    }
    
} catch (Exception $e) {
    error_log('Approval error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
