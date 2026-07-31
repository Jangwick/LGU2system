<?php

use PHPUnit\Framework\TestCase;

class CacheServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        CacheService::flush();
    }

    public function test_set_and_get_round_trip(): void
    {
        $this->assertTrue(CacheService::set('test-key', 'value', 60));
        $this->assertSame('value', CacheService::get('test-key'));
    }

    public function test_get_returns_null_for_missing_key(): void
    {
        $this->assertNull(CacheService::get('missing-key'));
    }

    public function test_delete_removes_key(): void
    {
        CacheService::set('delete-key', 'value', 60);
        $this->assertTrue(CacheService::delete('delete-key'));
        $this->assertNull(CacheService::get('delete-key'));
    }

    public function test_ttl_expires_value(): void
    {
        CacheService::set('ttl-key', 'value', 1);
        $this->assertSame('value', CacheService::get('ttl-key'));
        sleep(2);
        $this->assertNull(CacheService::get('ttl-key'));
    }

    public function test_remember_caches_callback_value(): void
    {
        $called = 0;
        $value = CacheService::remember('remember-key', 60, function () use (&$called) {
            $called++;
            return 'computed';
        });
        $this->assertSame('computed', $value);
        $this->assertSame(1, $called);

        $second = CacheService::remember('remember-key', 60, function () use (&$called) {
            $called++;
            return 'other';
        });
        $this->assertSame('computed', $second);
        $this->assertSame(1, $called);
    }

    public function test_flush_clears_all_values(): void
    {
        CacheService::set('a', 1, 60);
        CacheService::set('b', 2, 60);
        $this->assertTrue(CacheService::flush());
        $this->assertNull(CacheService::get('a'));
        $this->assertNull(CacheService::get('b'));
    }

    public function test_is_available_returns_true_when_file_driver_active(): void
    {
        $this->assertTrue(CacheService::isAvailable());
    }

    public function test_is_redis_false_without_redis(): void
    {
        $this->assertFalse(CacheService::isRedis());
    }

    public function test_handles_serialized_arrays_and_objects(): void
    {
        $payload = ['foo' => 'bar', 'list' => [1, 2, 3]];
        CacheService::set('array-key', $payload, 60);
        $this->assertSame($payload, CacheService::get('array-key'));
    }
}
