<?php
/**
 * Public Search API - No authentication required
 * Only returns approved/archived documents
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../search/services/SearchService.php';
require_once __DIR__ . '/../../search/services/EmbeddingService.php';

// Simple rate limiting by IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateLimitFile = sys_get_temp_dir() . '/public_portal_rate_' . md5($ip);
$now = time();
$maxRequests = 60; // per minute

if (file_exists($rateLimitFile)) {
    $data = json_decode(file_get_contents($rateLimitFile), true);
    if ($data && ($now - $data['start']) < 60) {
        if ($data['count'] >= $maxRequests) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            exit;
        }
        $data['count']++;
    } else {
        $data = ['start' => $now, 'count' => 1];
    }
} else {
    $data = ['start' => $now, 'count' => 1];
}
file_put_contents($rateLimitFile, json_encode($data));

try {
    $db = getDatabase();
    $embeddingService = new EmbeddingService();
    $searchService = new SearchService($db, $embeddingService);

    $query = Sanitizer::plainText($_GET['q'] ?? '');
    $mode = Sanitizer::enum($_GET['mode'] ?? 'hybrid', ['hybrid', 'semantic', 'keyword'], 'hybrid');
    $page = max(1, Sanitizer::int($_GET['page'] ?? 1, 1));
    $perPage = min(max(Sanitizer::int($_GET['per_page'] ?? 10, 10), 1), ApiPaginator::MAX_PER_PAGE);

    // Handle type filter: can be a single value or an array from type[] checkboxes
    $typeFilter = $_GET['type'] ?? '';
    if (is_array($typeFilter)) {
        $typeFilter = Sanitizer::array($typeFilter, 'plainText');
    } else {
        $typeFilter = Sanitizer::plainText($typeFilter);
    }

    $filters = [
        'type' => $typeFilter,
        'status' => '', // Will be forced below
        'date_from' => Sanitizer::date($_GET['date_from'] ?? ''),
        'date_to' => Sanitizer::date($_GET['date_to'] ?? ''),
        'tags' => Sanitizer::plainText($_GET['tags'] ?? ''),
        'limit' => $perPage,
        'offset' => ($page - 1) * $perPage
    ];

    // === SECURITY: Force status to only show approved ===
    // This is enforced server-side and cannot be overridden by query params

    if (!empty($query)) {
        $keywordTotal = 0;

        if ($mode === 'semantic') {
            $allResults = $searchService->semanticSearch($query, $filters);
            // Filter to only approved
            $allResults = array_filter($allResults, function($doc) {
                return in_array(strtolower($doc['status'] ?? ''), ['approved']);
            });
            $allResults = array_values($allResults);
            $total = count($allResults);
        } else {
            // Hybrid search
            $poolFilters = $filters;
            $poolFilters['limit'] = 100;
            $poolFilters['offset'] = 0;
            $allResults = $searchService->hybridSearch($query, $poolFilters);
            // Filter to only approved
            $allResults = array_filter($allResults, function($doc) {
                return in_array(strtolower($doc['status'] ?? ''), ['approved']);
            });
            $allResults = array_values($allResults);
            $total = count($allResults);
        }

        // Manual pagination
        $results = array_slice($allResults, ($page - 1) * $perPage, $perPage);
    } else {
        // Empty query - show all approved documents
        $filters['status'] = 'approved';
        $results = $searchService->search('', $filters);
        $total = $searchService->getCount('', $filters);
        // Sort by created_at descending
        usort($results, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Manual pagination for results
        $results = array_slice($results, ($page - 1) * $perPage, $perPage);
    }

    // Get facets (filtered for public - only count approved)
    $facets = $searchService->getFacets($query);

    // Strip sensitive fields from results
    foreach ($results as &$doc) {
        unset($doc['file_path']);
        unset($doc['deleted_at']);
    }
    unset($doc);

    $envelope = ApiPaginator::envelope($results, $page, $perPage, $total);
    // Keep legacy fields for existing consumers
    $envelope['results'] = $envelope['data'];
    $envelope['total'] = $total;
    $envelope['facets'] = $facets;
    $envelope['query'] = $query;
    $envelope['mode'] = $mode;
    unset($envelope['data']);
    echo json_encode($envelope);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your search.']);
}
