<?php

class ConnectionPoolTest extends DatabaseTestCase
{
    public function test_get_database_returns_same_pdo_with_persistence(): void
    {
        if (!defined('DB_PERSISTENT') || !DB_PERSISTENT) {
            $this->markTestSkipped('DB_PERSISTENT is not enabled.');
        }

        $db1 = getDatabase();
        $db2 = getDatabase();

        $this->assertSame($db1, $db2);
    }

    public function test_database_connection_is_alive(): void
    {
        $db = getDatabase();
        $stmt = $db->query('SELECT 1 as alive');
        $result = $stmt->fetch();

        $this->assertSame(1, (int) $result['alive']);
    }
}
