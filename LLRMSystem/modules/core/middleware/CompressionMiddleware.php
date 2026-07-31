<?php

/**
 * Compression Middleware
 *
 * PHP-level gzip fallback for environments where Nginx/Apache compression is not
 * available. Uses PHP's built-in ob_gzhandler to compress output when the client
 * accepts gzip encoding.
 */

class CompressionMiddleware {
    private static bool $started = false;

    public static function start(): void {
        if (self::$started) {
            return;
        }
        self::$started = true;

        // Skip if headers were already sent (e.g., streamed download)
        if (headers_sent()) {
            return;
        }

        $enabled = defined('COMPRESSION_ENABLED') ? (bool) COMPRESSION_ENABLED : (defined('CACHE_ENABLED') ? (bool) CACHE_ENABLED : true);
        if (!$enabled) {
            return;
        }

        // Only start a new buffer at the top of the output stack
        if (ob_get_level() > 0) {
            return;
        }

        // ob_gzhandler will return the buffer unmodified if gzip is not accepted
        ob_start('ob_gzhandler');
        register_shutdown_function([__CLASS__, 'end']);
    }

    public static function end(): void {
        if (!self::$started) {
            return;
        }

        // Flush any remaining buffered output
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
}
