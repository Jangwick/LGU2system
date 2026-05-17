<?php

class SessionTimeoutMiddleware {
    private $db;
    
    public function __construct($database = null) {
        $this->db = $database;
    }
    
    /**
     * Check if session has timed out and logout if needed
     */
    public function checkSessionTimeout() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // If user is not logged in, no need to check timeout
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        // Get session timeout from config (default 2 minutes)
        $timeoutMinutes = defined('SESSION_TIMEOUT_MINUTES') ? SESSION_TIMEOUT_MINUTES : 2;
        $timeoutSeconds = $timeoutMinutes * 60;
        
        // Set or update last activity time
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
        
        // Check if session has timed out
        $inactiveTime = time() - $_SESSION['last_activity'];
        
        if ($inactiveTime >= $timeoutSeconds) {
            // Session has timed out, destroy session and redirect
            $this->logoutDueToTimeout();
        } else {
            // Update last activity time
            $_SESSION['last_activity'] = time();
            
            // Calculate remaining time for client-side countdown
            $remainingTime = $timeoutSeconds - $inactiveTime;
            $_SESSION['session_timeout_remaining'] = $remainingTime;
        }
    }
    
    /**
     * Logout user due to session timeout
     */
    private function logoutDueToTimeout() {
        // Log the timeout if logger is available
        if (isset($_SESSION['user_id'])) {
            try {
                require_once __DIR__ . '/../utils/Logger.php';
                $logger = new Logger($this->db);
                $logger->logSession($_SESSION['user_id'], 'SESSION_TIMEOUT', [
                    'inactive_duration' => (time() - $_SESSION['last_activity']) . ' seconds'
                ]);
            } catch (Exception $e) {
                // Logger not available, continue
            }
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Redirect to login with timeout message
        $loginUrl = defined('AUTH_URL') ? AUTH_URL . '/views/login.php' : '/modules/authentication/views/login.php';
        header('Location: ' . $loginUrl . '?error=session_timeout');
        exit;
    }
    
    /**
     * Get remaining session time in seconds
     */
    public function getRemainingTime() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $timeoutMinutes = defined('SESSION_TIMEOUT_MINUTES') ? SESSION_TIMEOUT_MINUTES : 2;
        $timeoutSeconds = $timeoutMinutes * 60;
        $inactiveTime = time() - $_SESSION['last_activity'];
        $remainingTime = max(0, $timeoutSeconds - $inactiveTime);
        
        return $remainingTime;
    }
}
