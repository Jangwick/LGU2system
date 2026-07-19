<?php
/**
 * Authentication helper middleware.
 * This file starts the session and provides a reusable checkAuth() helper.
 * API endpoints include this file for session availability, then return their own
 * JSON responses, so this file does NOT redirect or terminate on its own.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('checkAuth')) {
    /**
     * Check whether a user is currently authenticated.
     *
     * @return bool
     */
    function checkAuth(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}
