<?php

use PHPUnit\Framework\TestCase;

class ApiPaginatorTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function test_resolve_params_uses_defaults(): void
    {
        $_GET = [];
        [$page, $perPage] = ApiPaginator::resolveParams();
        $this->assertSame(1, $page);
        $this->assertSame(ApiPaginator::DEFAULT_PER_PAGE, $perPage);
    }

    public function test_resolve_params_reads_query_string(): void
    {
        $_GET = ['page' => '3', 'per_page' => '15'];
        [$page, $perPage] = ApiPaginator::resolveParams();
        $this->assertSame(3, $page);
        $this->assertSame(15, $perPage);
    }

    public function test_resolve_params_enforces_minimums_and_maximums(): void
    {
        $_GET = ['page' => '0', 'per_page' => '500'];
        [$page, $perPage] = ApiPaginator::resolveParams();
        $this->assertSame(1, $page);
        $this->assertSame(ApiPaginator::MAX_PER_PAGE, $perPage);
    }

    public function test_paginate_array_returns_correct_page(): void
    {
        $items = range(1, 45);
        $result = ApiPaginator::paginateArray($items, 2, 10);

        $this->assertCount(10, $result['data']);
        $this->assertSame(11, $result['data'][0]);
        $this->assertSame(2, $result['pagination']['page']);
        $this->assertSame(10, $result['pagination']['per_page']);
        $this->assertSame(45, $result['pagination']['total']);
        $this->assertSame(5, $result['pagination']['total_pages']);
    }

    public function test_paginate_array_last_page_is_partial(): void
    {
        $items = range(1, 23);
        $result = ApiPaginator::paginateArray($items, 3, 10);

        $this->assertCount(3, $result['data']);
        $this->assertSame(21, $result['data'][0]);
        $this->assertSame(3, $result['pagination']['total_pages']);
    }

    public function test_paginate_array_clamps_per_page_to_max(): void
    {
        $items = range(1, 50);
        $result = ApiPaginator::paginateArray($items, 1, 500);

        $this->assertLessThanOrEqual(ApiPaginator::MAX_PER_PAGE, count($result['data']));
        $this->assertSame(ApiPaginator::MAX_PER_PAGE, $result['pagination']['per_page']);
    }

    public function test_envelope_contains_success_and_pagination_keys(): void
    {
        $envelope = ApiPaginator::envelope(['a', 'b'], 1, 2, 5, true, 'ok');

        $this->assertTrue($envelope['success']);
        $this->assertSame('ok', $envelope['message']);
        $this->assertSame(['a', 'b'], $envelope['data']);
        $this->assertArrayHasKey('pagination', $envelope);
        $this->assertSame(3, $envelope['pagination']['total_pages']);
    }
}
