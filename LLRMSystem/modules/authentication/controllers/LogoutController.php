<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include configuration
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . LOGIN_URL);
    exit;
}

try {
    // Get database connection
    $conn = getDatabase();
    
    // Log logout activity
    $stmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        'logout',
        'User logged out',
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ]);
    
    // Clear remember me cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
        
        // Clear token from database
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
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
