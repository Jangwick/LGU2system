<?php
/**
 * Bulk Indexing Script for Search Module
 * Usage: Run via terminal or browse to /modules/search/controllers/IndexWorker.php (if routed)
 */

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../services/EmbeddingService.php';

class IndexWorker {
    private $db;
    private $embeddingService;

    public function __construct() {
        $dbConfig = require __DIR__ . '/../../core/config/database.php';
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $this->db = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::ASSOC
        ]);
        $this->embeddingService = new EmbeddingService();
    }

    public function run() {
        echo "Starting Bulk Indexing...\n";

        // 1. Get all documents that don't have embeddings yet
        $sql = "SELECT d.* FROM legislative_documents d 
                LEFT JOIN document_embeddings e ON d.id = e.document_id 
                WHERE e.id IS NULL";
        
        $stmt = $this->db->query($sql);
        $docs = $stmt->fetchAll();

        $count = count($docs);
        echo "Found {$count} documents to index.\n";

        $success = 0;
        $failed = 0;

        foreach ($docs as $doc) {
            echo "Indexing Document ID: {$doc['id']} - {$doc['title']}... ";
            
            try {
                if ($this->embeddingService->embedDocument($this->db, $doc['id'])) {
                    echo "OK\n";
                    $success++;
                } else {
                    echo "FAILED (No Content)\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
                $failed++;
            }

            // Rate limiting for Gemini API (Free tier has limits)
            usleep(500000); // 0.5s delay
        }

        echo "\nIndexing Complete!\n";
        echo "Success: {$success}\n";
        echo "Failed: {$failed}\n";
    }
}

// If run from CLI
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    $worker = new IndexWorker();
    $worker->run();
} else {
    echo "To run indexing, visit this URL with ?run=1 or run via PHP CLI.";
}
