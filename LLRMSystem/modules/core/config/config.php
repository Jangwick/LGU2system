<?php
/**
 * Application Configuration
 * Dynamic path detection that works regardless of folder nesting or location
 */

// Prevent direct access
if (!defined('APP_CONFIG_LOADED')) {
    define('APP_CONFIG_LOADED', true);
}

// Load local configuration if it exists
if (file_exists(__DIR__ . '/config.local.php')) {
    ob_start();
    require_once __DIR__ . '/config.local.php';
    ob_end_clean();
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
        // Always prefer https. On localhost/development fall back to the
        // actual scheme so local development still works without TLS.
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $isLocalhost = in_array($host, ['localhost', '127.0.0.1'], true)
            || str_starts_with($host, 'localhost:')
            || str_starts_with($host, '127.0.0.1:');
        $protocol = (!$isLocalhost || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'))
            ? 'https'
            : 'http';
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
// For Gmail: Use an App Password (not your main password). Set credentials in config.local.php
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', 'your_email@gmail.com'); // Override in config.local.php
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'your_app_password');    // Override in config.local.php
if (!defined('SMTP_FROM')) define('SMTP_FROM', 'your_email@gmail.com'); // Override in config.local.php
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'LLRM System Security');

// OTP Settings
define('OTP_EXPIRY_MINUTES', 1); // Reduced from 10 to 1 minute for security
define('OTP_RESEND_COOLDOWN', 60); // Seconds

// Session Timeout Settings
define('SESSION_TIMEOUT_MINUTES', 5); // Auto-logout after 5 minutes of inactivity
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
define('CORE_URL', BASE_URL . '/modules/core');
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
define('PUBLIC_PORTAL_URL', BASE_URL . '/modules/public-portal/views/search.php');

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
    $url = ASSETS_URL . '/' . $path;
    $file = __DIR__ . '/../../../public/assets/' . $path;
    $mtime = @filemtime($file);
    if ($mtime) {
        $url .= '?v=' . $mtime;
    }
    return $url;
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
    // Mirror the HTTPS-preference logic in detectBaseUrl().
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isLocalhost = in_array($host, ['localhost', '127.0.0.1'], true)
        || str_starts_with($host, 'localhost:');
    $protocol = (!$isLocalhost || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'))
        ? 'https'
        : 'http';
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

// Database Configuration
// Priority: Environment variables > config.local.php > defaults
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'lrms_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}

// AI Configuration
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', ''); // Fallback to empty if not defined in local config
}
if (!defined('GEMINI_EMBEDDING_MODEL')) {
    define('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-2'); // Gemini Embedding 2
}
if (!defined('GEMINI_EMBEDDING_DIMENSIONALITY')) {
    define('GEMINI_EMBEDDING_DIMENSIONALITY', 768); // Lower-cost 768-dim vectors
}
if (!defined('GROQ_API_KEY')) {
    define('GROQ_API_KEY', ''); // Set in config.local.php
}
if (!defined('GROQ_MODEL')) {
    define('GROQ_MODEL', 'llama-3.3-70b-versatile');
}

// Encryption Configuration
// Master encryption key for file encryption
// Priority: Environment variable > config.local.php > fallback
if (!defined('ENCRYPTION_KEY')) {
    $envKey = getenv('ENCRYPTION_KEY');
    if ($envKey !== false && $envKey !== '') {
        define('ENCRYPTION_KEY', $envKey);
    } else {
        // Fallback - should be overridden in config.local.php or environment
        define('ENCRYPTION_KEY', hash('sha256', 'default-encryption-key-change-in-production', true));
    }
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Only set Secure flag when not on localhost.
    // On localhost (HTTP), setting cookie_secure=1 prevents the browser
    // from sending session cookies, which breaks OTP verification flow.
    $sessionHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isLocalDev = in_array($sessionHost, ['localhost', '127.0.0.1'], true)
        || str_starts_with($sessionHost, 'localhost:');
    if (!$isLocalDev) {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_samesite', 'Lax');
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

// Load input sanitization utilities (available to all endpoints that include config)
require_once __DIR__ . '/../utils/Sanitizer.php';
require_once __DIR__ . '/../utils/Request.php';

// OCR Configuration (can be overridden in config.local.php)
if (!defined('OCR_ENABLED')) {
    define('OCR_ENABLED', true);
}
if (!defined('OCR_TESSERACT_PATH')) {
    // Auto-detect based on OS — OcrService handles detection
    define('OCR_TESSERACT_PATH', '');
}
if (!defined('OCR_GHOSTSCRIPT_PATH')) {
    define('OCR_GHOSTSCRIPT_PATH', '');
}
if (!defined('OCR_LANGUAGE')) {
    define('OCR_LANGUAGE', 'eng');
}
if (!defined('OCR_TIMEOUT')) {
    define('OCR_TIMEOUT', 120);
}
if (!defined('OCR_ASYNC_THRESHOLD')) {
    define('OCR_ASYNC_THRESHOLD', 5242880); // 5MB — files larger than this run async
}

// OCR AI vision fallback (uses Groq API when Tesseract cannot read a page/image)
if (!defined('OCR_GROQ_FALLBACK')) {
    define('OCR_GROQ_FALLBACK', true);
}
if (!defined('OCR_GROQ_ENHANCE')) {
    define('OCR_GROQ_ENHANCE', true);
}
if (!defined('OCR_GROQ_MODEL')) {
    define('OCR_GROQ_MODEL', 'qwen/qwen3.6-27b');
}
if (!defined('OCR_GROQ_MAX_PAGES')) {
    define('OCR_GROQ_MAX_PAGES', 0); // 0 = no page limit
}
if (!defined('OCR_GROQ_PROMPT')) {
    define('OCR_GROQ_PROMPT', 'Extract all readable text from this image. Also briefly describe any images, seals, signatures, stamps, diagrams, or other visible content. Return only plain text.');
}
