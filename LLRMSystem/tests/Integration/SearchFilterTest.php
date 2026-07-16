<?php

use PHPUnit\Framework\TestCase;

class SearchFilterTest extends TestCase
{
    private $mockSearchService;
    private $mockEmbeddingService;
    private $mockLogger;
    private $controller;

    protected function setUp(): void
    {
        $_GET = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'administrator'];

        // Mock SearchService to avoid DB dependency
        $this->mockSearchService = $this->getMockBuilder('SearchService')
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getCount',
                'hybridSearch',
                'semanticSearch',
                'search',
                'getFacets',
                'exportToCSV'
            ])
            ->getMock();

        $this->mockSearchService->method('getCount')->willReturn(0);
        $this->mockSearchService->method('search')->willReturn([]);
        $this->mockSearchService->method('hybridSearch')->willReturn([]);
        $this->mockSearchService->method('semanticSearch')->willReturn([]);
        $this->mockSearchService->method('getFacets')->willReturn([]);
        $this->mockSearchService->method('exportToCSV')->willReturn('');

        // Mock EmbeddingService
        $this->mockEmbeddingService = $this->getMockBuilder('EmbeddingService')
            ->disableOriginalConstructor()
            ->getMock();

        // Mock Logger
        $this->mockLogger = $this->getMockBuilder('Logger')
            ->disableOriginalConstructor()
            ->onlyMethods(['logActivity'])
            ->getMock();

        // Use real SearchController without running constructor (no DB dependency)
        $reflection = new ReflectionClass('SearchController');
        $this->controller = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('searchService')->setValue($this->controller, $this->mockSearchService);
        $reflection->getProperty('embeddingService')->setValue($this->controller, $this->mockEmbeddingService);
        $reflection->getProperty('logger')->setValue($this->controller, $this->mockLogger);
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_SESSION = [];
    }

    public function testTypeArrayFromCheckboxesIsSanitizedCorrectly()
    {
        $_GET['type'] = ['ordinance', 'resolution', '<script>alert(1)</script>'];
        $_GET['q'] = 'test';
        $_GET['mode'] = 'hybrid';
        $_GET['page'] = '1';

        $result = $this->controller->index();

        $this->assertIsArray($result['filters']['type']);
        $this->assertSame(['ordinance', 'resolution', 'alert(1)'], $result['filters']['type']);
    }

    public function testSingleTypeValueIsSanitizedAsString()
    {
        $_GET['type'] = '<script>ordinance</script>';
        $_GET['q'] = 'test';
        $_GET['mode'] = 'hybrid';
        $_GET['page'] = '1';

        $result = $this->controller->index();

        $this->assertSame('ordinance', $result['filters']['type']);
    }

    public function testExportHandlesTypeArray()
    {
        $_GET['type'] = ['resolution', 'memo'];
        $_GET['q'] = 'budget';

        $result = $this->controller->export();

        $this->assertIsArray($result['filters']['type']);
        $this->assertSame(['resolution', 'memo'], $result['filters']['type']);
    }
}
