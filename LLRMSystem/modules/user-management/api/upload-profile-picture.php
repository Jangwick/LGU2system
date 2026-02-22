<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

CsrfMiddleware::requireValidToken();

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['profile_picture'];
$userId = $_SESSION['user_id'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed']);
    exit;
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = __DIR__ . '/../../../storage/profiles/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    exit;
}

// Update database
try {
    $db = getDatabase();
    $logger = new Logger($db);
    
    // Get old profile picture
    $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $oldPicture = $stmt->fetchColumn();
    
    // Delete old profile picture if exists
    if ($oldPicture && file_exists(__DIR__ . '/../../../storage/profiles/' . $oldPicture)) {
        unlink(__DIR__ . '/../../../storage/profiles/' . $oldPicture);
    }
    
    // Update user record
    $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$filename, $userId]);
    
    // Log activity with enhanced logger
    $logger->logActivity(Logger::ACTION_PROFILE_UPDATE, 'users', $userId,
        "User updated their profile picture", [
            'new_picture' => $filename,
            'file_size' => $file['size'],
            'file_type' => $fileType
        ], [
            'old_picture' => $oldPicture
        ]);
    
    // Return success with image URL
    $imageUrl = BASE_URL . '/storage/profiles/' . $filename;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'image_url' => $imageUrl,
        'filename' => $filename
    ]);
    
} catch (PDOException $e) {
    // Delete uploaded file if database update fails
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}