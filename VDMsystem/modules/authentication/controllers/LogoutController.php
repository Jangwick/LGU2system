<?php
/**
 * VDMsystem - Logout Controller
 */
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Log the logout
if (isset($_SESSION['user_id'])) {
    try {
        $db = getDatabase();
        
        // Get previous hash
        $lastLog = dbFetchOne("SELECT current_hash FROM audit_logs ORDER BY id DESC LIMIT 1");
        $previousHash = $lastLog['current_hash'] ?? '';
        
        // Create hash
        $hashData = json_encode([
            'event_type' => 'logout',
            'user_id' => $_SESSION['user_id'],
            'action' => 'User logged out',
            'timestamp' => date('Y-m-d H:i:s'),
            'previous_hash' => $previousHash
        ]);
        $currentHash = hash('sha256', $hashData);
        
        // Log logout
        $stmt = $db->prepare(
            "INSERT INTO audit_logs (event_type, user_id, module, entity_type, entity_id, action, ip_address, user_agent, previous_hash, current_hash) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([
            'logout',
            $_SESSION['user_id'],
            'authentication',
            'user',
            $_SESSION['user_id'],
            'User logged out',
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $previousHash,
            $currentHash
        ]);
    } catch (Exception $e) {
        error_log('Logout audit error: ' . $e->getMessage());
    }
}

// Clear session
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login with success message
redirect(LOGIN_URL . '?logout=success');
