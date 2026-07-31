<?php

/**
 * Log Queue
 *
 * Stores log payloads in CacheService (Redis or file) for asynchronous insertion
 * by the process-logs worker. This keeps the web request path from blocking on
 * database writes.
 */

class LogQueue {
    private static int $maxSize = 1000;

    public static function enabled(): bool {
        return defined('LOG_QUEUE_ENABLED') ? (bool) LOG_QUEUE_ENABLED : (defined('CACHE_ENABLED') ? (bool) CACHE_ENABLED : true);
    }

    public static function key(string $queue): string {
        return 'queue:' . $queue;
    }

    public static function push(string $queue, array $payload): bool {
        if (!self::enabled()) {
            return false;
        }

        $key = self::key($queue);
        $items = CacheService::get($key);
        if (!is_array($items)) {
            $items = [];
        }

        $items[] = $payload;

        // Prevent unbounded growth if the worker is down
        if (count($items) > self::$maxSize) {
            array_shift($items);
            error_log('LogQueue: ' . $queue . ' exceeded max size, dropped oldest entry');
        }

        return CacheService::set($key, $items, 86400);
    }

    public static function drain(string $queue, int $limit = 100): array {
        $key = self::key($queue);
        $items = CacheService::get($key);
        if (!is_array($items) || empty($items)) {
            return [];
        }

        $drained = array_splice($items, 0, $limit);

        if (empty($items)) {
            CacheService::delete($key);
        } else {
            CacheService::set($key, $items, 86400);
        }

        return $drained;
    }

    public static function size(string $queue): int {
        $items = CacheService::get(self::key($queue));
        return is_array($items) ? count($items) : 0;
    }
}
