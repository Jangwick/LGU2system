<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/core/utils/CacheService.php';

class FileCacheDriverTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/file_cache_driver_test_' . uniqid();
        @mkdir($this->cacheDir, 0775, true);
    }

    protected function tearDown(): void
    {
        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($this->cacheDir);
    }

    public function test_set_and_get_round_trip(): void
    {
        $driver = new FileCacheDriver($this->cacheDir);

        $this->assertTrue($driver->set('key1', ['name' => 'value'], 300));
        $this->assertSame(['name' => 'value'], $driver->get('key1'));
    }

    public function test_get_returns_null_when_missing(): void
    {
        $driver = new FileCacheDriver($this->cacheDir);

        $this->assertNull($driver->get('missing-key'));
    }

    public function test_expired_value_returns_null(): void
    {
        $driver = new FileCacheDriver($this->cacheDir);

        $driver->set('short-lived', 'data', -1);
        $this->assertNull($driver->get('short-lived'));
    }

    public function test_delete_removes_cached_value(): void
    {
        $driver = new FileCacheDriver($this->cacheDir);

        $driver->set('to-delete', 'value', 300);
        $this->assertTrue($driver->delete('to-delete'));
        $this->assertNull($driver->get('to-delete'));
    }

    public function test_flush_removes_all_cache_files(): void
    {
        $driver = new FileCacheDriver($this->cacheDir);

        $driver->set('a', 1, 300);
        $driver->set('b', 2, 300);

        $this->assertTrue($driver->flush());
        $this->assertNull($driver->get('a'));
        $this->assertNull($driver->get('b'));
    }

    public function test_get_returns_null_for_corrupt_data(): void
    {
        $driver = new FileCacheDriver($this->cacheDir);

        $file = $this->cacheDir . DIRECTORY_SEPARATOR . sha1('corrupt') . '.cache';
        file_put_contents($file, 'not a valid serialized string');

        $this->assertNull($driver->get('corrupt'));
    }

}
