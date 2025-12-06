<?php
/**
 * VDMsystem - Application Configuration
 * Dynamic path detection that works regardless of folder nesting or location
 */

// Prevent direct access
if (!defined('APP_CONFIG_LOADED')) {
    define('APP_CONFIG_LOADED', true);
}

// Application Info
define('APP_NAME', 'VDM System');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Voting and Decision-Making System');
define('APP_MODULE_CODE', 'LGU-MOD-05');

/**
 * Detect the base directory of the application
 */
function detectBasePath() {
    static $basePath = null;
    
    if ($basePath === null) {
        $currentDir = __DIR__;
        
        // Walk up the directory tree until we find the root (containing 'modules' folder)
        while ($currentDir !== dirname($currentDir)) {
            if (is_dir($currentDir . DIRECTORY_SEPARATOR . 'modules')) {
                $basePath = $currentDir;
                break;
            }
            $currentDir = dirname($currentDir);
        }
        
        if ($basePath === null) {
            die('Error: Could not detect application base path.');
        }
    }
    
    return $basePath;
}

/**
 * Detect the base URL of the application
 */
function detectBaseUrl() {
    static $baseUrl = null;
    
    if ($baseUrl === null) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $basePath = str_replace('\\', '/', detectBasePath());
        
        $relativePath = str_replace($documentRoot, '', $basePath);
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
define('DASHBOARD_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'dashboard');
define('DOCUMENTS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'documents');
define('VOTING_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'voting');
define('WORKFLOWS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'workflows');
define('REPORTS_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'reports');
define('AUDIT_PATH', MODULES_PATH . DIRECTORY_SEPARATOR . 'audit');

// URL paths
define('ASSETS_URL', BASE_URL . '/public/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');

// Module URLs
define('AUTH_URL', BASE_URL . '/modules/authentication');
define('DASHBOARD_URL', BASE_URL . '/modules/dashboard');
define('DOCUMENTS_URL', BASE_URL . '/modules/documents');
define('VOTING_URL', BASE_URL . '/modules/voting');
define('WORKFLOWS_URL', BASE_URL . '/modules/workflows');
define('REPORTS_URL', BASE_URL . '/modules/reports');
define('AUDIT_URL', BASE_URL . '/modules/audit');

// Common page URLs
define('LOGIN_URL', AUTH_URL . '/views/login.php');
define('LOGOUT_URL', AUTH_URL . '/controllers/LogoutController.php');
define('REGISTER_URL', AUTH_URL . '/views/register.php');
define('DASHBOARD_INDEX_URL', DASHBOARD_URL . '/views/index.php');
define('DOCUMENTS_INDEX_URL', DOCUMENTS_URL . '/views/index.php');
define('VOTING_INDEX_URL', VOTING_URL . '/views/index.php');

/**
 * Get a URL path relative to the base URL
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

/**
 * Get an asset URL
 */
function asset($path) {
    $path = ltrim($path, '/');
    return ASSETS_URL . '/' . $path;
}

/**
 * Redirect to a URL
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
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isAuthenticated()) return false;
    $userRole = strtolower(trim($_SESSION['user_role'] ?? ''));
    if (is_array($role)) {
        return in_array($userRole, array_map('strtolower', $role));
    }
    return $userRole === strtolower($role);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole(['admin', 'administrator']);
}

/**
 * Sanitize output for HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'draft' => 'bg-gray-100 text-gray-800',
        'under_review' => 'bg-yellow-100 text-yellow-800',
        'committee_review' => 'bg-blue-100 text-blue-800',
        'pending_vote' => 'bg-purple-100 text-purple-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'archived' => 'bg-gray-100 text-gray-600',
        'scheduled' => 'bg-indigo-100 text-indigo-800',
        'in_progress' => 'bg-yellow-100 text-yellow-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    return $classes[strtolower($status)] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Get vote badge class
 */
function getVoteBadgeClass($vote) {
    $classes = [
        'approve' => 'bg-green-100 text-green-800',
        'reject' => 'bg-red-100 text-red-800',
        'abstain' => 'bg-gray-100 text-gray-800',
    ];
    return $classes[strtolower($vote)] ?? 'bg-gray-100 text-gray-800';
}
