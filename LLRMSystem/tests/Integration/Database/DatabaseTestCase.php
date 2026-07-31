<?php

use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        try {
            self::$db = getDatabase();
        } catch (Throwable $e) {
            self::markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function beginTransaction()
    {
        self::$db->beginTransaction();
    }

    protected function rollbackTransaction()
    {
        self::$db->rollBack();
    }

    protected function setUp(): void
    {
        $this->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
    }
}
