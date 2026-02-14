<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include configuration
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

try {
    // Get database connection
    $conn = getDatabase();
    $logger = new Logger($conn);
    
    // Calculate session duration
    $sessionDuration = isset($_SESSION['login_time']) ? time() - $_SESSION['login_time'] : 0;
    
    // Log logout activity with enhanced logger
    $logger->logSession($_SESSION['user_id'], Logger::ACTION_LOGOUT, [
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'session_duration_seconds' => $sessionDuration,
        'session_duration_formatted' => gmdate("H:i:s", $sessionDuration)
    ]);
    
    // Clear session tracking and remember me cookie
    $stmt = $conn->prepare("UPDATE users SET last_session_id = NULL, remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
} catch (PDOException $e) {
    // Log error but continue with logout
    error_log("Logout error: " . $e->getMessage());
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ' . LOGIN_URL . '?logout=success');
exit;
