<?php

class PaginatorDatabaseTest extends DatabaseTestCase
{
    public function test_paginate_query_returns_envelope(): void
    {
        $baseSql = 'SELECT id, title FROM legislative_documents';
        $countSql = 'SELECT COUNT(*) FROM legislative_documents';

        $result = ApiPaginator::paginateQuery(self::$db, $baseSql, $countSql, [], 1, 10);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['data']);
        $this->assertLessThanOrEqual(10, count($result['data']));
        $this->assertGreaterThanOrEqual(0, $result['pagination']['total']);
        $this->assertSame(1, $result['pagination']['page']);
    }

    public function test_paginate_query_pages_accurately_when_data_exists(): void
    {
        $countSql = 'SELECT COUNT(*) FROM legislative_documents';
        $totalStmt = self::$db->query($countSql);
        $total = (int) $totalStmt->fetchColumn();

        if ($total === 0) {
            $this->markTestSkipped('No documents in database to paginate.');
        }

        $baseSql = 'SELECT id, title FROM legislative_documents ORDER BY id ASC';
        $page2 = ApiPaginator::paginateQuery(self::$db, $baseSql, $countSql, [], 2, 10);

        $this->assertCount(min(10, max(0, $total - 10)), $page2['data']);
        $this->assertSame($total, $page2['pagination']['total']);
        $this->assertSame(2, $page2['pagination']['page']);
    }
}
