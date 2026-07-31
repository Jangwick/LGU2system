<?php

/**
 * Cache Service
 * 
 * Unified caching layer that prefers Redis (phpredis or Predis) and falls back
 * to a file-based cache when Redis is unavailable. Used by API endpoints to
 * reduce database load and improve response times.
 */

interface CacheDriver {
    public function get(string $key);
    public function set(string $key, $value, int $ttl = 300): bool;
    public function delete(string $key): bool;
    public function flush(): bool;
}

class RedisCacheDriver implements CacheDriver {
    private $redis;

    public function __construct(Redis $redis) {
        $this->redis = $redis;
    }

    public function get(string $key) {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return $this->unserialize($data);
    }

    public function set(string $key, $value, int $ttl = 300): bool {
        $serialized = serialize($value);
        return $this->redis->setex($key, $ttl, $serialized);
    }

    public function delete(string $key): bool {
        return (bool) $this->redis->del($key);
    }

    public function flush(): bool {
        return $this->redis->flushDB();
    }

    private function unserialize(string $data) {
        $value = unserialize($data);
        if ($value === false && $data !== serialize(false)) {
            return null;
        }
        return $value;
    }
}

class PredisCacheDriver implements CacheDriver {
    private $client;

    public function __construct(Predis\Client $client) {
        $this->client = $client;
    }

    public function get(string $key) {
        $data = $this->client->get($key);
        if ($data === null) {
            return null;
        }
        $value = unserialize($data);
        if ($value === false && $data !== serialize(false)) {
            return null;
        }
        return $value;
    }

    public function set(string $key, $value, int $ttl = 300): bool {
        $serialized = serialize($value);
        $this->client->setex($key, $ttl, $serialized);
        return true;
    }

    public function delete(string $key): bool {
        return (bool) $this->client->del($key);
    }

    public function flush(): bool {
        $this->client->flushdb();
        return true;
    }
}

class FileCacheDriver implements CacheDriver {
    private string $cacheDir;

    public function __construct(string $cacheDir) {
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR);
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
    }

    public function get(string $key) {
        $file = $this->getFile($key);
        if (!file_exists($file)) {
            return null;
        }
        $expiresAt = (int) @file_get_contents($file . '.expires');
        if ($expiresAt > 0 && $expiresAt < time()) {
            @unlink($file);
            @unlink($file . '.expires');
            return null;
        }
        $data = @file_get_contents($file);
        if ($data === false) {
            return null;
        }
        $value = unserialize($data);
        if ($value === false && $data !== serialize(false)) {
            return null;
        }
        return $value;
    }

    public function set(string $key, $value, int $ttl = 300): bool {
        if (!is_dir($this->cacheDir) && !@mkdir($this->cacheDir, 0775, true) && !is_dir($this->cacheDir)) {
            error_log('FileCacheDriver: Cannot create cache directory ' . $this->cacheDir);
            return false;
        }
        $file = $this->getFile($key);
        $serialized = serialize($value);
        $written = @file_put_contents($file, $serialized, LOCK_EX) !== false;
        if ($written) {
            @file_put_contents($file . '.expires', (string) (time() + $ttl), LOCK_EX);
        }
        return $written;
    }

    public function delete(string $key): bool {
        $file = $this->getFile($key);
        $deleted = true;
        if (file_exists($file)) {
            $deleted = @unlink($file) && $deleted;
        }
        if (file_exists($file . '.expires')) {
            $deleted = @unlink($file . '.expires') && $deleted;
        }
        return $deleted;
    }

    public function flush(): bool {
        if (!is_dir($this->cacheDir)) {
            return true;
        }
        $success = true;
        foreach (glob($this->cacheDir . DIRECTORY_SEPARATOR . '*') as $file) {
            if (is_file($file)) {
                $success = @unlink($file) && $success;
            }
        }
        return $success;
    }

    private function getFile(string $key): string {
        return $this->cacheDir . DIRECTORY_SEPARATOR . sha1($key) . '.cache';
    }
}

class NullCacheDriver implements CacheDriver {
    public function get(string $key) { return null; }
    public function set(string $key, $value, int $ttl = 300): bool { return false; }
    public function delete(string $key): bool { return true; }
    public function flush(): bool { return true; }
}

class CacheService {
    private static ?CacheService $instance = null;
    private ?CacheDriver $driver = null;
    private bool $enabled;

    private function __construct() {
        $this->enabled = $this->isEnabled();
        if ($this->enabled) {
            $this->driver = $this->detectDriver();
        }
        if ($this->driver === null) {
            $this->driver = new NullCacheDriver();
        }
    }

    public static function getInstance(): CacheService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get(string $key) {
        return self::getInstance()->driver->get($key);
    }

    public static function set(string $key, $value, int $ttl = 300): bool {
        return self::getInstance()->driver->set($key, $value, $ttl);
    }

    public static function delete(string $key): bool {
        return self::getInstance()->driver->delete($key);
    }

    public static function flush(): bool {
        return self::getInstance()->driver->flush();
    }

    public static function remember(string $key, int $ttl, callable $callback) {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function isAvailable(): bool {
        return self::getInstance()->driver instanceof RedisCacheDriver
            || self::getInstance()->driver instanceof PredisCacheDriver
            || self::getInstance()->driver instanceof FileCacheDriver;
    }

    public static function isRedis(): bool {
        $driver = self::getInstance()->driver;
        return $driver instanceof RedisCacheDriver || $driver instanceof PredisCacheDriver;
    }

    private function isEnabled(): bool {
        if (defined('CACHE_ENABLED')) {
            return (bool) CACHE_ENABLED;
        }
        if (defined('REDIS_ENABLED')) {
            return (bool) REDIS_ENABLED;
        }
        return true;
    }

    private function detectDriver(): ?CacheDriver {
        $host = defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1';
        $port = defined('REDIS_PORT') ? (int) REDIS_PORT : 6379;
        $db = defined('REDIS_DB') ? (int) REDIS_DB : 0;
        $password = (defined('REDIS_PASSWORD') && REDIS_PASSWORD !== '') ? REDIS_PASSWORD : null;
        $timeout = defined('REDIS_TIMEOUT') ? (float) REDIS_TIMEOUT : 0.1;

        try {
            if (class_exists('Redis')) {
                $redis = new Redis();
                $redis->connect($host, $port, $timeout);
                if ($password !== null) {
                    $redis->auth($password);
                }
                if ($db !== 0) {
                    $redis->select($db);
                }
                $redis->ping();
                return new RedisCacheDriver($redis);
            }

            if (class_exists('Predis\Client')) {
                $client = new Predis\Client([
                    'scheme' => 'tcp',
                    'host' => $host,
                    'port' => $port,
                    'database' => $db,
                    'password' => $password,
                    'timeout' => $timeout,
                ]);
                $client->ping();
                return new PredisCacheDriver($client);
            }
        } catch (Throwable $e) {
            error_log('CacheService: Redis connection failed: ' . $e->getMessage());
        }

        try {
            $cacheDir = defined('FILE_CACHE_PATH') ? FILE_CACHE_PATH : STORAGE_PATH . DIRECTORY_SEPARATOR . 'cache';
            if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
                error_log('CacheService: Cannot create file cache directory ' . $cacheDir);
                return null;
            }
            return new FileCacheDriver($cacheDir);
        } catch (Throwable $e) {
            error_log('CacheService: File cache setup failed: ' . $e->getMessage());
        }

        return null;
    }
}
