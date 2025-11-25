<?php

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../services/SearchService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class SearchController {
    private $searchService;
    private $logger;
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->searchService = new SearchService($this->db);
        $this->logger = new Logger($this->db);
    }
    
    /**
     * Main search page
     */
    public function index() {
        $query = $_GET['q'] ?? '';
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'tags' => $_GET['tags'] ?? '',
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage
        ];
        
        $results = $this->searchService->search($query, $filters);
        $total = $this->searchService->getCount($query, $filters);
        $facets = $this->searchService->getFacets($query);
        
        // Log search if user is logged in
        if (isset($_SESSION['user_id']) && !empty($query)) {
            $this->logger->log(
                $_SESSION['user_id'],
                'search_performed',
                null,
                "Search query: {$query}"
            );
        }
        
        return [
            'query' => $query,
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
