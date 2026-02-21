<?php
/**
 * Application Configuration
 * Dynamic path detection that works regardless of folder nesting or location
 */

// Prevent direct access
if (!defined('APP_CONFIG_LOADED')) {
    define('APP_CONFIG_LOADED', true);
}

/**
 * Detect the base directory of the application
 * This works by finding the root directory containing the 'modules' folder
 */
function detectBasePath() {
    static $basePath = null;
    
    if ($basePath === null) {
        $currentDir = __DIR__;
        
        // Walk up the directory tree until we find the root (containing 'modules' folder)
        while ($currentDir !== dirname($currentDir)) {
            // Check if this directory contains the 'modules' folder
            if (is_dir($currentDir . DIRECTORY_SEPARATOR . 'modules')) {
                $basePath = $currentDir;
                break;
            }
            $currentDir = dirname($currentDir);
        }
        
        if ($basePath === null) {
            die('Error: Could not detect application base path. Please ensure the directory structure is intact.');
        }
    }
    
    return $basePath;
}

/**
 * Detect the base URL of the application
 * This works regardless of how many folders deep the application is deployed
 */
function detectBaseUrl() {
    static $baseUrl = null;
    
    if ($baseUrl === null) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $basePath = str_replace('\\', '/', detectBasePath());
        
        // Calculate the relative path from document root
        $relativePath = str_replace($documentRoot, '', $basePath);
        
        // Construct base URL
        $baseUrl = $protocol . '://' . $host . $relativePath;
    }
    
    return $baseUrl;
}

// Define constants for paths
define('BASE_PATH', detectBasePath());
define('BASE_URL', detectBaseUrl());

// Directory paths
define('MODULES_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'modules');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');
define('DATABASE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'database');

// Core module paths
define('CORE_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'core');
define('CONFIG_PATH', CORE_PATH . DIRECTORY_SEPARATOR . 'config');
define('LAYOUTS_PATH', CORE_PATH . DIRECTORY_SEPARATOR . 'layouts');
define('MIDDLEWARE_PATH', CORE_PATH . DIRECTORY_SEPARATOR . 'middleware');
define('UTILS_PATH', CORE_PATH . DIRECTORY_SEPARATOR . 'utils');

// Module paths
define('AUTH_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'authentication');

// --- Email Security / OTP Configuration ---
// For Gmail: Use an App Password (not your main password)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'Johnrick1214@gmail.com');
define('SMTP_PASS', 'imok xero ttaf mypf'); // Gmail App Password
define('SMTP_FROM', 'Johnrick1214@gmail.com');
define('SMTP_FROM_NAME', 'LLRM System Security');

// OTP Settings
define('OTP_EXPIRY_MINUTES', 10);
define('OTP_RESEND_COOLDOWN', 60); // Seconds
// ------------------------------------------

define('DASHBOARD_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'dashboard');
define('DOCUMENTS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'document-management');
define('USERS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'user-management');
define('REPORTS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'reports-analytics');
define('SEARCH_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'search');
define('AUDIT_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'audit');
define('HELP_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'help');
define('NOTIFICATIONS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'notifications');
define('RESEARCH_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'research-analysis');

// URL paths (for use in HTML/JavaScript)
define('ASSETS_URL', BASE_URL . '/public/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');

// Module URLs
define('AUTH_URL', BASE_URL . '/modules/authentication');
define('DASHBOARD_URL', BASE_URL . '/modules/dashboard');
define('DOCUMENTS_URL', BASE_URL . '/modules/document-management');
define('RESEARCH_URL', BASE_URL . '/modules/research-analysis');
define('USERS_URL', BASE_URL . '/modules/user-management');
define('REPORTS_URL', BASE_URL . '/modules/reports-analytics');
define('SEARCH_URL', BASE_URL . '/modules/search');
define('AUDIT_URL', BASE_URL . '/modules/audit');
define('HELP_URL', BASE_URL . '/modules/help');
define('NOTIFICATIONS_URL', BASE_URL . '/modules/notifications');
define('INTEGRATION_URL', BASE_URL . '/modules/integration');

// Common page URLs
define('LOGIN_URL', AUTH_URL . '/views/login.php');
define('LOGOUT_URL', AUTH_URL . '/controllers/LogoutController.php');
define('REGISTER_URL', AUTH_URL . '/views/register.php');
define('DASHBOARD_INDEX_URL', DASHBOARD_URL . '/views/index.php');
define('DOCUMENTS_INDEX_URL', DOCUMENTS_URL . '/views/index.php');

/**
 * Get a URL path relative to the base URL
 * @param string $path The relative path (e.g., 'modules/dashboard/views/index.php')
 * @return string The full URL
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

/**
 * Get an asset URL
 * @param string $path The asset path relative to public/assets (e.g., 'js/main.js')
 * @return string The full asset URL
 */
function asset($path) {
    $path = ltrim($path, '/');
    return ASSETS_URL . '/' . $path;
}

/**
 * Redirect to a URL
 * @param string $url The URL to redirect to
 * @param int $statusCode HTTP status code (default: 302)
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Redirect to login page
 */
function redirectToLogin() {
    redirect(LOGIN_URL);
}

/**
 * Redirect to dashboard
 */
function redirectToDashboard() {
    redirect(DASHBOARD_INDEX_URL);
}

/**
 * Check if the user's session is still valid (prevents concurrent logins)
 */
function isSessionValid() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['current_session_id'])) {
        return true; // Not logged in yet or no tracking set
    }

    try {
        require_once __DIR__ . '/database.php';
        $db = getDatabase();
        $stmt = $db->prepare("SELECT last_session_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $lastSessionId = $stmt->fetchColumn();

        return ($lastSessionId === $_SESSION['current_session_id']);
    } catch (Exception $e) {
        return true; // if DB fails, don't lock out
    }
}

/**
 * Global authentication check
 */
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        redirectToLogin();
    }

    if (!isSessionValid()) {
        logoutSuperseded();
    }
}

/**
 * Handle logout for superseded sessions
 */
function logoutSuperseded() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Redirect with a message
    $loginUrl = LOGIN_URL . (strpos(LOGIN_URL, '?') !== false ? '&' : '?') . 'error=session_superseded';
    header('Location: ' . $loginUrl);
    exit;
}

/**
 * Check if the user is already logged in and should be redirected away from login/landing
 */
function checkAlreadyLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user_id'])) {
        if (isSessionValid()) {
            redirectToDashboard();
        } else {
            // If they are here with an invalid session, clean it up
            $_SESSION = array();
            session_destroy();
        }
    }
}

/**
 * Get the current page URL
 * @return string Current page URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . '://' . $host . $uri;
}

/**
 * Check if current URL matches a pattern
 * @param string $pattern Pattern to match
 * @return bool True if matches
 */
function isCurrentUrl($pattern) {
    return strpos(currentUrl(), $pattern) !== false;
}

// Application settings
define('APP_NAME', 'Legislative Records Management System');
define('APP_SHORT_NAME', 'LRMS');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Load local configuration if it exists
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Database Configuration Defaults (can be overridden in config.local.php)
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'lrms_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// AI Configuration
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', ''); // Fallback to empty if not defined in local config
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
}

// Error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Manila');
