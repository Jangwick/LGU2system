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
}
