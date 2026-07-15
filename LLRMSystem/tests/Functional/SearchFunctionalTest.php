<?php

use PHPUnit\Framework\TestCase;

class SearchFunctionalTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $_GET = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'administrator'];

        $mockService = $this->getMockBuilder('SearchService')
            ->disableOriginalConstructor()
            ->onlyMethods(['search', 'getCount', 'getFacets'])
            ->getMock();

        $mockService->method('search')->willReturn([]);
        $mockService->method('getCount')->willReturn(0);
        $mockService->method('getFacets')->willReturn([]);

        $mockLogger = $this->getMockBuilder('Logger')
            ->disableOriginalConstructor()
            ->onlyMethods(['logActivity'])
            ->getMock();

        $reflection = new ReflectionClass('SearchController');
        $this->controller = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('searchService')->setValue($this->controller, $mockService);
        $reflection->getProperty('embeddingService')->setValue($this->controller, null);
        $reflection->getProperty('logger')->setValue($this->controller, $mockLogger);
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_SESSION = [];
    }

    public function testSearchControllerBuildsFiltersForBusinessLogic()
    {
        $_GET = [
            'q' => 'budget',
            'type' => ['ordinance', 'resolution'],
            'status' => 'approved',
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
            'page' => '1'
        ];

        $result = $this->controller->index();

        $this->assertSame('budget', $result['query']);
        $this->assertIsArray($result['filters']['type']);
        $this->assertSame('approved', $result['filters']['status']);
    }

    public function testSearchControllerCalculatesPagination()
    {
        $_GET = ['q' => 'tax', 'page' => '2'];

        $result = $this->controller->index();

        $this->assertSame(10, $result['per_page']);
        $this->assertSame(2, $result['page']);
    }
}
