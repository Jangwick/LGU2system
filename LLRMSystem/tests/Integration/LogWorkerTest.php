<?php

use PHPUnit\Framework\TestCase;

class LogWorkerTest extends TestCase
{
    protected function setUp(): void
    {
        CacheService::delete(LogQueue::key('activity_logs'));
    }

    protected function tearDown(): void
    {
        CacheService::delete(LogQueue::key('activity_logs'));
    }

    public function test_worker_drains_activity_logs_into_database(): void
    {
        try {
            $db = getDatabase();
        } catch (Throwable $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }

        $uniqueAction = 'worker-test-' . uniqid();

        LogQueue::push('activity_logs', [
            'user_id' => 1,
            'action' => $uniqueAction,
            'table_name' => 'test',
            'record_id' => 1,
            'description' => 'Worker test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertSame(1, LogQueue::size('activity_logs'));

        $output = [];
        $return = 0;
        exec('php ' . BASE_PATH . '/modules/core/jobs/process-logs.php', $output, $return);

        $this->assertSame(0, $return);
        $this->assertStringContainsString('activity log', implode(' ', $output));

        $stmt = $db->prepare("SELECT * FROM activity_logs WHERE action = :action");
        $stmt->execute([':action' => $uniqueAction]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertSame('Worker test', $row['description']);
    }
}
