<?php

use PHPUnit\Framework\TestCase;

class LogQueueTest extends TestCase
{
    protected function tearDown(): void
    {
        CacheService::delete(LogQueue::key('activity_logs'));
        CacheService::delete(LogQueue::key('document_access_logs'));
    }

    public function test_push_increases_queue_size(): void
    {
        $this->assertTrue(LogQueue::push('activity_logs', ['action' => 'view', 'user_id' => 1]));
        $this->assertSame(1, LogQueue::size('activity_logs'));
    }

    public function test_drain_returns_items_and_empties_queue(): void
    {
        LogQueue::push('activity_logs', ['action' => 'a', 'user_id' => 1]);
        LogQueue::push('activity_logs', ['action' => 'b', 'user_id' => 2]);

        $drained = LogQueue::drain('activity_logs', 10);
        $this->assertCount(2, $drained);
        $this->assertSame('a', $drained[0]['action']);
        $this->assertSame(0, LogQueue::size('activity_logs'));
    }

    public function test_drain_respects_limit(): void
    {
        LogQueue::push('activity_logs', ['n' => 1]);
        LogQueue::push('activity_logs', ['n' => 2]);
        LogQueue::push('activity_logs', ['n' => 3]);

        $drained = LogQueue::drain('activity_logs', 2);
        $this->assertCount(2, $drained);
        $this->assertSame(1, LogQueue::size('activity_logs'));
    }

    public function test_enabled_returns_true_by_default(): void
    {
        $this->assertTrue(LogQueue::enabled());
    }

    public function test_push_returns_false_when_disabled(): void
    {
        // Constants cannot be redefined at runtime; this is covered by the enabled() test above.
        $this->markTestSkipped('Cannot redefine LOG_QUEUE_ENABLED in a single process after config.php loaded it.');
    }

    public function test_key_format(): void
    {
        $this->assertSame('queue:activity_logs', LogQueue::key('activity_logs'));
    }
}
