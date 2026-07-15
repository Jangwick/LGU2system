<?php

use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        try {
            self::$db = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
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
