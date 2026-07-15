<?php
/**
 * Performance benchmark
 * Runs repeated search requests and reports average/median/p95 response times
 */

require_once __DIR__ . '/../bootstrap.php';

$baseUrl = 'http://localhost:8000';
$queries = [
    '/modules/public-portal/views/search.php?q=budget',
    '/modules/public-portal/views/search.php?q=ordinance',
    '/modules/public-portal/views/search.php?q=resolution&status=approved',
    '/modules/public-portal/views/search.php?q=tax&page=1'
];

$iterations = 10;
$times = [];

foreach ($queries as $url) {
    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        @file_get_contents($baseUrl . $url);
        $times[] = (microtime(true) - $start) * 1000;
    }
}

sort($times);
$count = count($times);
$average = array_sum($times) / $count;
$median = $times[(int) floor($count / 2)];
$p95Index = (int) floor($count * 0.95);
$p95 = $times[max(0, $p95Index - 1)];
$min = min($times);
$max = max($times);

echo "Performance Benchmark Results ($count requests)\n";
echo "Average: " . round($average, 2) . " ms\n";
echo "Median: " . round($median, 2) . " ms\n";
echo "P95: " . round($p95, 2) . " ms\n";
echo "Min: " . round($min, 2) . " ms\n";
echo "Max: " . round($max, 2) . " ms\n";

$threshold = 2000;
$ok = $p95 < $threshold;

exit($ok ? 0 : 1);
