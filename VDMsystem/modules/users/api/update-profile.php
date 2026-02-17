<?php
/**
 * VDMsystem - Update Profile API
 * Handles profile updates for the currently logged-in user
 */
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

header('Content-Type: application/json');

// Check authentication
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

try {
    // Get current user data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    // Collect update data
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');

    // Validation
    if (empty($fullName)) {
        echo json_encode(['success' => false, 'error' => 'Full name is required']);
        exit;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'A valid email is required']);
        exit;
    }

    // Check email uniqueness (excluding current user)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Email is already in use by another account']);
        exit;
    }

    // Handle profile picture upload
    $profilePicture = $currentUser['profile_picture'] ?? null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: JPEG, PNG, GIF, WebP']);
            exit;
        }

        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File size must be under 5MB']);
            exit;
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $uploadDir = BASE_PATH . '/storage/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Delete old profile picture
        if ($profilePicture && file_exists($uploadDir . $profilePicture)) {
            unlink($uploadDir . $profilePicture);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $profilePicture = $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload profile picture']);
            exit;
        }
    }

    // Build update data
    $updateData = [
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'position' => $position,
        'department' => $department,
        'profile_picture' => $profilePicture,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    dbUpdate('users', $updateData, 'id = ?', [$userId]);

    // Update session data
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email'] = $email;

    // Log audit
    logAudit(
        'profile_updated',
        $userId,
        'users',
        'users',
        $userId,
        'Updated own profile',
        ['name' => $fullName, 'email' => $email]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'profile_picture' => $profilePicture
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to update profile: ' . $e->getMessage()]);
}
