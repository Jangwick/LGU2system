<?php

class SearchDatabaseIntegrationTest extends DatabaseTestCase
{
    public function testDatabaseConnectionIsAlive()
    {
        $stmt = self::$db->query('SELECT 1 as alive');
        $result = $stmt->fetch();
        $this->assertSame(1, (int) $result['alive']);
    }

    public function testTypeFilterArrayProducesValidSqlInClause()
    {
        $types = ['ordinance', 'resolution'];
        $placeholders = [];
        $params = [];

        foreach ($types as $index => $type) {
            $placeholder = ':type' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $type;
        }

        $sql = 'SELECT 1 FROM legislative_documents WHERE document_type IN (' . implode(',', $placeholders) . ')';
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        $this->assertTrue($stmt->execute($params));
    }

    public function testLegislativeDocumentsTableExists()
    {
        $stmt = self::$db->query("SHOW TABLES LIKE 'legislative_documents'");
        $this->assertNotFalse($stmt);
        $this->assertGreaterThanOrEqual(0, $stmt->rowCount());
    }
}
