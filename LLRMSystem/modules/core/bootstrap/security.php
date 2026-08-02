<?php
/**
 * Security Bootstrap
 * Centralized security initialization for all entry points
 * 
 * This file should be included at the very top of every entry point
 * before any HTML output or session operations.
 */

// Prevent direct access
if (!defined('APP_CONFIG_LOADED')) {
    require_once __DIR__ . '/../config/config.php';
}

// Load input sanitization utilities (Sanitizer + Request)
require_once __DIR__ . '/../utils/Sanitizer.php';
require_once __DIR__ . '/../utils/Request.php';

/**
 * Configure secure session settings
 */
function configureSecureSession() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        // Secure cookie settings
        $secure = (!in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1'], true) 
            && !str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:'));
        
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => '', // Current domain
            'secure' => $secure, // Only send over HTTPS
            'httponly' => true, // Not accessible via JavaScript
            'samesite' => 'Lax' // CSRF protection, allows navigation from external sites
        ]);
        
        session_start();
    }
}

/**
 * Send security response headers
 * Must be called before any HTML output
 */
function sendSecurityHeaders() {
    // Prevent caching of dynamic pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS protection (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy (restrict browser features)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    
    // Content Security Policy
    // Note: 'unsafe-inline' is currently required due to inline scripts and Tailwind
    // Future: Move to nonce-based CSP to eliminate 'unsafe-inline'
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://static.cloudflareinsights.com; " .
        "script-src-elem 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://static.cloudflareinsights.com; " .
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://fonts.googleapis.com; " .
        "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
        "img-src 'self' data: blob: https://lacs.spvalenzuela.com; " .
        "connect-src 'self' https://cdnjs.cloudflare.com; " .
        "frame-ancestors 'self'; " .
        "base-uri 'self'; " .
        "form-action 'self';"
    );
}

/**
 * Initialize CSRF protection
 */
function initializeCSRF() {
    require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
    // Generate token if not exists
    CsrfMiddleware::generateToken();
}

/**
 * Initialize security for authenticated requests
 * Includes session timeout check for logged-in users
 */
function initializeAuthenticatedSecurity() {
    configureSecureSession();
    sendSecurityHeaders();
    initializeCSRF();
    
    // Session timeout check (currently disabled for debugging, enable in production)
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../middleware/SessionTimeoutMiddleware.php';
        require_once __DIR__ . '/../config/database.php';
        $sessionMiddleware = new SessionTimeoutMiddleware(getDatabase());
        $sessionMiddleware->checkSessionTimeout();
    }
}

/**
 * Initialize security for public/guest requests
 * Does not include session timeout check
 */
function initializePublicSecurity() {
    configureSecureSession();
    sendSecurityHeaders();
    initializeCSRF();
}

/**
 * Require authentication for API endpoints
 * Sends 401 response if not authenticated
 */
function requireAuthentication() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Authentication required',
            'code' => 'UNAUTHORIZED'
        ]);
        exit;
    }
}

/**
 * Check if current user has a specific role
 */
function hasRole($role) {
    $userRole = strtolower(trim($_SESSION['user_role'] ?? ''));
    $requiredRole = strtolower(trim($role));
    
    // Role hierarchy
    $roleHierarchy = [
        'viewer' => 1,
        'staff' => 2,
        'officer' => 3,
        'administrator' => 4,
        'superadmin' => 5,
        'super_admin' => 5
    ];
    
    // Normalize roles
    $normalizedUserRole = str_replace('super_admin', 'superadmin', $userRole);
    $normalizedRequiredRole = str_replace('super_admin', 'superadmin', $requiredRole);
    
    if (!isset($roleHierarchy[$normalizedUserRole]) || !isset($roleHierarchy[$normalizedRequiredRole])) {
        return false;
    }
    
    return $roleHierarchy[$normalizedUserRole] >= $roleHierarchy[$normalizedRequiredRole];
}

/**
 * Require minimum role level
 * Sends 403 response if user doesn't have required role
 */
function requireRole($role) {
    if (!hasRole($role)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Insufficient permissions',
            'code' => 'FORBIDDEN'
        ]);
        exit;
    }
}
