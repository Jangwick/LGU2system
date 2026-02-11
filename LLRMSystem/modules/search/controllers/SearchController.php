<?php

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../services/SearchService.php';
require_once __DIR__ . '/../services/EmbeddingService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class SearchController {
    private $searchService;
    private $embeddingService;
    private $logger;
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->embeddingService = new EmbeddingService();
        $this->searchService = new SearchService($this->db, $this->embeddingService);
        $this->logger = new Logger($this->db);
    }
    
    /**
     * Main search page
     */
    public function index() {
        $query = $_GET['q'] ?? '';
        $mode = $_GET['mode'] ?? 'hybrid'; // Default search mode
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10;
        
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'tags' => $_GET['tags'] ?? '',
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage
        ];
        
        // Handle different search modes
        if (!empty($query)) {
            $keywordTotal = $this->searchService->getCount($query, $filters);
            
            if ($mode === 'semantic') {
                $allResults = $this->searchService->semanticSearch($query, $filters);
                $total = count($allResults);
            } else {
                // Hybrid (Keyword + Semantic)
                // Use a larger pool for better reranking, then paginate the result
                $poolFilters = $filters;
                $poolFilters['limit'] = 100; 
                $poolFilters['offset'] = 0;
                $allResults = $this->searchService->hybridSearch($query, $poolFilters);
                $total = max($keywordTotal, count($allResults));
            }
            
            // Manual pagination for AI/Hybrid results
            $results = array_slice($allResults, ($page - 1) * $perPage, $perPage);
        } else {
            $results = $this->searchService->search($query, $filters);
            $total = $this->searchService->getCount($query, $filters);
        }
        
        $facets = $this->searchService->getFacets($query);
        
        // Log search if user is logged in
        if (isset($_SESSION['user_id']) && !empty($query)) {
            $this->logger->logActivity(Logger::ACTION_SEARCH, 'search', null,
                "Search query: {$query} (Mode: {$mode})", [
                    'query' => $query,
                    'mode' => $mode,
                    'results_count' => $total,
                    'filters' => $filters
                ]);
        }
        
        return [
            'query' => $query,
            'mode' => $mode,
            'results' => $results,
            'total' => $total,
            'facets' => $facets,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'filters' => $filters
        ];
    }
    
    /**
     * Export search results
     */
    public function export() {
        $query = $_GET['q'] ?? '';
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $csv = $this->searchService->exportToCSV($query, $filters);
        
        // Log search export
        if (isset($_SESSION['user_id'])) {
            $this->logger->logActivity(Logger::ACTION_REPORT_EXPORT, 'search', null,
                "Exported search results for: {$query}", [
                    'query' => $query,
                    'filters' => $filters,
                    'format' => 'csv'
                ]);
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="search_results_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }
    
    /**
     * Get search suggestions (AJAX)
     */
    public function suggestions() {
        $query = $_GET['q'] ?? '';
        $suggestions = $this->searchService->getSuggestions($query);
        
        header('Content-Type: application/json');
        echo json_encode($suggestions);
        exit;
    }
}
