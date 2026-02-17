<?php
/**
 * VDMsystem - Change Password API
 */
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = getDatabase();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'error' => 'All password fields are required']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'error' => 'New passwords do not match']);
    exit;
}

try {
    // Verify current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        exit;
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    dbUpdate('users', [
        'password' => $hashedPassword,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$userId]);

    // Log audit
    logAudit(
        'password_changed',
        $userId,
        'users',
        'users',
        $userId,
        'Changed own password',
        []
    );

    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to change password: ' . $e->getMessage()]);
}
