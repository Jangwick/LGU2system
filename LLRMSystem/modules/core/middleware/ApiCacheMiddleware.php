<?php

/**
 * API Cache Middleware
 *
 * Automatically caches GET API responses to reduce database and compute load.
 * Cached responses are stored by CacheService (Redis/File) and expire after
 * API_CACHE_TTL seconds. Non-GET and non-JSON responses are not cached.
 */

class ApiCacheMiddleware {
    private static bool $started = false;
    private static string $cacheKey = '';
    private static int $ttl = 60;
    private static bool $cacheable = false;

    public static function start(): void {
        if (self::$started) {
            return;
        }
        self::$started = true;

        $enabled = defined('API_CACHE_ENABLED') ? (bool) API_CACHE_ENABLED : (defined('CACHE_ENABLED') ? (bool) CACHE_ENABLED : true);
        if (!$enabled) {
            return;
        }

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        $uri = self::getRequestUri();
        if (!$uri || !self::isApiRequest($uri)) {
            return;
        }

        self::$ttl = defined('API_CACHE_TTL') ? (int) API_CACHE_TTL : 60;
        self::$cacheable = true;
        self::$cacheKey = self::buildKey($uri);

        $cached = CacheService::get(self::$cacheKey);
        if ($cached !== null) {
            self::sendCacheHeaders();
            echo $cached;
            exit;
        }

        ob_start();
        register_shutdown_function([__CLASS__, 'end']);
    }

    public static function end(): void {
        if (!self::$cacheable || self::$cacheKey === '') {
            return;
        }

        $content = (string) ob_get_contents();
        if ($content === '') {
            ob_end_flush();
            return;
        }

        if (self::isCacheableResponse($content)) {
            CacheService::set(self::$cacheKey, $content, self::$ttl);
        }

        ob_end_flush();
    }

    private static function getRequestUri(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?? $uri;
        $query = $_SERVER['QUERY_STRING'] ?? '';
        if ($query !== '') {
            $uri .= '?' . $query;
        }
        return $uri;
    }

    private static function isApiRequest(string $uri): bool {
        return strpos($uri, '/api/') !== false || strpos($uri, '\\api\\') !== false;
    }

    private static function buildKey(string $uri): string {
        $salt = '';
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $salt = ':u' . $_SESSION['user_id'];
        }
        return 'api:cache:' . $_SERVER['REQUEST_METHOD'] . ':' . md5($uri . $salt);
    }

    private static function isCacheableResponse(string $content): bool {
        $trimmed = ltrim($content);
        if ($trimmed === '') {
            return false;
        }
        // Only cache JSON responses
        if (strpos($trimmed, '{') !== 0 && strpos($trimmed, '[') !== 0) {
            return false;
        }
        json_decode($trimmed);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        // Do not cache error responses
        if (preg_match('/"success"\s*:\s*false/', $trimmed)) {
            return false;
        }
        return true;
    }

    private static function sendCacheHeaders(): void {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('X-Cache: HIT');
        }
    }
}
