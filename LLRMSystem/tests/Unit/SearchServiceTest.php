<?php

use PHPUnit\Framework\TestCase;

class SearchServiceTest extends TestCase
{
    public function test_cosine_similarity_for_identical_vectors(): void
    {
        $vec = [1.0, 2.0, 3.0];
        $this->assertEqualsWithDelta(1.0, SearchService::cosineSimilarity($vec, $vec), 0.0001);
    }

    public function test_cosine_similarity_for_orthogonal_vectors(): void
    {
        $a = [1.0, 0.0];
        $b = [0.0, 1.0];
        $this->assertEqualsWithDelta(0.0, SearchService::cosineSimilarity($a, $b), 0.0001);
    }

    public function test_cosine_similarity_for_zero_vector(): void
    {
        $this->assertEqualsWithDelta(0.0, SearchService::cosineSimilarity([0, 0], [1, 1]), 0.0001);
    }

    public function test_merge_results_combines_keyword_and_semantic(): void
    {
        $service = new SearchService(null);
        $method = new ReflectionMethod(SearchService::class, 'mergeResults');
        $method->setAccessible(true);

        $keyword = [
            ['id' => 1, 'title' => 'A', 'relevance_score' => 0.5],
            ['id' => 2, 'title' => 'B', 'relevance_score' => 0.3],
        ];

        $semantic = [
            ['id' => 2, 'title' => 'B', 'relevance_score' => 0.8],
            ['id' => 3, 'title' => 'C', 'relevance_score' => 0.9],
        ];

        $merged = $method->invoke($service, $keyword, $semantic);

        $this->assertCount(3, $merged);
        $ids = array_column($merged, 'id');
        $this->assertSame([2, 1, 3], $ids);
        $this->assertGreaterThan($merged[1]['relevance_score'], $merged[0]['relevance_score']);
    }

    public function test_apply_type_filter_with_single_type(): void
    {
        $service = new SearchService(null);
        $method = new ReflectionMethod(SearchService::class, 'applyTypeFilter');
        $method->setAccessible(true);

        $sql = 'SELECT 1';
        $params = [];
        $method->invokeArgs($service, [&$sql, &$params, 'ordinance']);

        $this->assertStringContainsString("document_type = :type", $sql);
        $this->assertSame('ordinance', $params[':type']);
    }

    public function test_apply_type_filter_with_array(): void
    {
        $service = new SearchService(null);
        $method = new ReflectionMethod(SearchService::class, 'applyTypeFilter');
        $method->setAccessible(true);

        $sql = 'SELECT 1';
        $params = [];
        $method->invokeArgs($service, [&$sql, &$params, ['ordinance', 'resolution']]);

        $this->assertStringContainsString("document_typeIN(:type0,:type1)", str_replace(' ', '', $sql));
        $this->assertSame('ordinance', $params[':type0']);
        $this->assertSame('resolution', $params[':type1']);
    }

    public function test_get_count_returns_total(): void
    {
        $stmt = new class {
            public function bindValue($key, $value, $type = null): void {}
            public function execute(): void {}
            public function fetch($mode) {
                return ['total' => 37];
            }
        };

        $db = new class($stmt) {
            private $stmt;
            public function __construct($stmt) { $this->stmt = $stmt; }
            public function prepare($sql) {
                $this->lastSql = $sql;
                return $this->stmt;
            }
            public $lastSql;
        };

        $service = new SearchService($db);
        $total = $service->getCount('budget', ['type' => 'ordinance']);

        $this->assertSame(37, $total);
        $this->assertStringContainsString('COUNT(*)', $db->lastSql);
    }

    public function test_get_facets_returns_counts_by_type_status_and_year(): void
    {
        $rows = [
            ['document_type' => 'ordinance', 'count' => 37],
            ['document_type' => 'resolution', 'count' => 12]
        ];

        $stmt = new class($rows) {
            private $rows;
            private $index = 0;
            public function __construct($rows) { $this->rows = $rows; }
            public function execute(): void {}
            public function fetchAll($mode) {
                $result = $this->rows;
                $this->rows = [];
                return $result;
            }
        };

        $db = new class($stmt) {
            private $stmt;
            public function __construct($stmt) { $this->stmt = $stmt; }
            public function prepare($sql) {
                return $this->stmt;
            }
        };

        $service = new SearchService($db);
        $facets = $service->getFacets();

        $this->assertArrayHasKey('by_type', $facets);
        $this->assertArrayHasKey('by_status', $facets);
        $this->assertArrayHasKey('by_year', $facets);
        $this->assertSame('ordinance', $facets['by_type'][0]['document_type']);
        $this->assertSame(37, (int) $facets['by_type'][0]['count']);
    }

    public function test_export_to_csv_returns_csv_string(): void
    {
        $service = new class(null) extends SearchService {
            public function search($query, $filters = []): array
            {
                return [
                    [
                        'reference_number' => '2025-01',
                        'title' => 'Budget Ordinance',
                        'document_type' => 'ordinance',
                        'status' => 'approved',
                        'document_date' => '2025-01-15',
                        'file_name' => 'budget.pdf',
                        'file_size' => 12345,
                        'uploaded_by_name' => 'Admin',
                        'created_at' => '2025-01-15 10:00:00'
                    ]
                ];
            }
        };

        $csv = $service->exportToCSV('budget', []);

        $this->assertStringContainsString('Reference Number', $csv);
        $this->assertStringContainsString('Budget Ordinance', $csv);
        $this->assertStringContainsString('ordinance', $csv);
    }

    public function test_get_suggestions_returns_distinct_documents(): void
    {
        $rows = [
            ['title' => 'Budget Ordinance', 'reference_number' => '2025-01', 'document_type' => 'ordinance'],
            ['title' => 'Resolution 2025', 'reference_number' => '2025-02', 'document_type' => 'resolution'],
        ];

        $stmt = new class($rows) {
            private $rows;
            public function __construct($rows) { $this->rows = $rows; }
            public function bindValue($key, $value, $type = null): void {}
            public function execute(): void {}
            public function fetchAll($mode) { return $this->rows; }
        };

        $db = new class($stmt) {
            private $stmt;
            public function __construct($stmt) { $this->stmt = $stmt; }
            public function prepare($sql) { return $this->stmt; }
        };

        $service = new SearchService($db);
        $suggestions = $service->getSuggestions('budget', 5);

        $this->assertCount(2, $suggestions);
        $this->assertSame('Budget Ordinance', $suggestions[0]['title']);
    }
}
