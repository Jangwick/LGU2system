<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../modules/search/controllers/SearchController.php';

class SearchControllerTest extends TestCase
{
    private function createControllerWithoutConstructor(): SearchController
    {
        $reflection = new ReflectionClass(SearchController::class);
        return $reflection->newInstanceWithoutConstructor();
    }

    public function test_index_builds_search_result_envelope(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public $calls = [];

            public function search($query, $filters): array
            {
                $this->calls[] = ['search', $query, $filters];
                return [
                    ['id' => 1, 'title' => 'Doc 1'],
                    ['id' => 2, 'title' => 'Doc 2'],
                ];
            }

            public function getCount($query, $filters): int
            {
                $this->calls[] = ['getCount', $query, $filters];
                return 50;
            }

            public function getFacets($query): array
            {
                $this->calls[] = ['getFacets', $query];
                return [
                    'by_type' => [['document_type' => 'ordinance', 'count' => 37]],
                    'by_status' => [],
                    'by_year' => [],
                    'by_month' => []
                ];
            }
        };

        $reflection = new ReflectionClass($controller);
        $searchService = $reflection->getProperty('searchService');
        $searchService->setAccessible(true);
        $searchService->setValue($controller, $service);

        $_GET = [];
        $_SESSION = ['user_id' => 1, 'user_role' => 'staff'];

        $result = $controller->index();

        $this->assertSame('', $result['query']);
        $this->assertSame('hybrid', $result['mode']);
        $this->assertCount(2, $result['results']);
        $this->assertSame(50, $result['total']);
        $this->assertSame(5, (int) $result['total_pages']);
        $this->assertSame(1, $result['page']);
        $this->assertArrayHasKey('facets', $result);
        $this->assertSame(37, $result['facets']['by_type'][0]['count']);
    }

    public function test_index_uses_keyword_mode_for_empty_query(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public function hybridSearch($query, $filters): array
            {
                return [['id' => 1, 'title' => 'Keyword result']];
            }

            public function getCount($query, $filters): int
            {
                return 10;
            }

            public function getFacets($query): array
            {
                return ['by_type' => [], 'by_status' => [], 'by_year' => [], 'by_month' => []];
            }
        };

        $logger = new class {
            public function logActivity($action, $module, $recordId, $description, $metadata = null): void
            {
                // no-op
            }
        };

        $reflection = new ReflectionClass($controller);
        $searchService = $reflection->getProperty('searchService');
        $searchService->setAccessible(true);
        $searchService->setValue($controller, $service);

        $loggerProp = $reflection->getProperty('logger');
        $loggerProp->setAccessible(true);
        $loggerProp->setValue($controller, $logger);

        $_GET = ['q' => 'budget', 'mode' => 'keyword'];
        $_SESSION = ['user_id' => 1, 'user_role' => 'staff'];

        $result = $controller->index();

        $this->assertSame('budget', $result['query']);
        $this->assertSame('keyword', $result['mode']);
        $this->assertCount(1, $result['results']);
    }

    public function test_export_returns_csv_and_filename(): void
    {
        $controller = $this->createControllerWithoutConstructor();

        $service = new class {
            public function exportToCSV($query, $filters): string
            {
                return "id,title\n1,Test";
            }
        };

        $logger = new class {
            public function logActivity($action, $module, $recordId, $description, $metadata = null): void
            {
                // no-op
            }
        };

        $reflection = new ReflectionClass($controller);
        $searchService = $reflection->getProperty('searchService');
        $searchService->setAccessible(true);
        $searchService->setValue($controller, $service);

        $loggerProp = $reflection->getProperty('logger');
        $loggerProp->setAccessible(true);
        $loggerProp->setValue($controller, $logger);

        $_GET = ['q' => ' ordinance '];
        $_SESSION = ['user_id' => 1];

        $result = $controller->export();

        $this->assertStringContainsString('id,title', $result['csv']);
        $this->assertSame('', $result['filters']['type']);
        $this->assertStringStartsWith('search_results_', $result['filename']);
    }
}
