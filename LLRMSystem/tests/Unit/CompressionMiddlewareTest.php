<?php

use PHPUnit\Framework\TestCase;

class CompressionMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        $reflection = new ReflectionClass(CompressionMiddleware::class);
        $started = $reflection->getProperty('started');
        $started->setAccessible(true);
        $started->setValue(null, false);
    }

    protected function tearDown(): void
    {
        // Only close buffers we explicitly opened in the current test
        // to avoid interfering with the framework-level output buffers.
    }

    public function test_start_does_not_open_buffer_when_one_exists(): void
    {
        ob_start();
        $levelBefore = ob_get_level();

        CompressionMiddleware::start();

        $this->assertSame($levelBefore, ob_get_level());
        @ob_end_clean();
    }

    public function test_start_opens_buffer_at_top_level(): void
    {
        // PHPUnit/bootstrap already runs middleware, so a true top-level buffer
        // is not available in this environment. Skip this scenario.
        if (ob_get_level() > 0) {
            $this->markTestSkipped('Top-level output buffer not available in test bootstrap.');
        }

        CompressionMiddleware::start();

        $this->assertSame(1, ob_get_level());
        @ob_end_clean();
    }

    public function test_disabled_compression_skips_buffer(): void
    {
        if (!defined('COMPRESSION_ENABLED')) {
            define('COMPRESSION_ENABLED', false);
        }

        $levelBefore = ob_get_level();
        CompressionMiddleware::start();
        $this->assertSame($levelBefore, ob_get_level());
    }

    public function test_second_start_call_is_ignored(): void
    {
        CompressionMiddleware::start();
        $level = ob_get_level();
        CompressionMiddleware::start();
        $this->assertSame($level, ob_get_level());
    }

    public function test_end_flushes_output_when_started(): void
    {
        $reflection = new ReflectionClass(CompressionMiddleware::class);
        $started = $reflection->getProperty('started');
        $started->setAccessible(true);
        $started->setValue(null, true);

        ob_start();
        echo 'test output';
        $level = ob_get_level();

        CompressionMiddleware::end();

        $this->assertLessThan($level, ob_get_level());
    }
}
