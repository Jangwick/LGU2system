<?php
/**
 * CSRF Middleware
 * Generates and validates synchroniser tokens to protect all
 * state-changing POST endpoints against Cross-Site Request Forgery.
 *
 * Token delivery: The token is embedded in every authenticated page via
 * a <meta name="csrf-token"> tag (see modules/core/layouts/header.php).
 * All JS fetch() calls automatically inject it as the X-CSRF-Token request
 * header (see public/assets/js/csrf.js).
 *
 * Fallback: The token is also accepted from $_POST['csrf_token'] for any
 * traditional (non-AJAX) form that includes the hidden field.
 */
class CsrfMiddleware {

    /**
     * Return the current session CSRF token, generating one if needed.
     * Safe to call multiple times — the same token is reused for the session lifetime.
     */
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Return an HTML <meta> tag carrying the current CSRF token.
     * Include this inside <head> on every authenticated page.
     */
    public static function metaTag(): string {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::generateToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate the incoming CSRF token.
     *
     * Accepts the token from (in order of preference):
     *  1. X-CSRF-Token request header  (used by the JS fetch interceptor)
     *  2. $_POST['csrf_token']          (used by traditional HTML form submissions)
     */
    public static function validate(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        $sessionToken = $_SESSION['csrf_token'];

        // 1. Header (preferred — works for both JSON and FormData fetch calls)
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if ($headerToken !== '' && hash_equals($sessionToken, $headerToken)) {
            return true;
        }

        // 2. POST body field (fallback for non-JS form submissions)
        $postToken = $_POST['csrf_token'] ?? '';
        if ($postToken !== '' && hash_equals($sessionToken, $postToken)) {
            return true;
        }

        return false;
    }

    /**
     * Require a valid CSRF token for state-changing requests.
     * Exits with HTTP 403 and a JSON error body on failure.
     * GET / HEAD / OPTIONS requests are always allowed through.
     */
    public static function requireValidToken(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Safe (read-only) methods do not mutate state — no token needed.
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        if (!self::validate()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error'   => 'Invalid or missing CSRF token. Please refresh the page and try again.',
                'code'    => 'CSRF_FAILURE',
            ]);
            exit;
        }
    }
}
