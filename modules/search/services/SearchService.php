<?php

class SearchService {
    private $db;
    private $embeddingService;
    
    public function __construct($database, $embeddingService = null) {
        $this->db = $database;
        $this->embeddingService = $embeddingService;
    }
    
    /**
     * Perform hybrid search (Keyword + Semantic)
     */
    public function hybridSearch($query, $filters = []) {
        // 1. Get keyword results (existing)
        $keywordResults = $this->search($query, $filters);
        
        // 2. Get semantic results (new)
        $semanticResults = [];
        if (!empty($query) && $this->embeddingService) {
            $semanticResults = $this->semanticSearch($query, $filters);
        }
        
        // 3. Merge and re-rank using Reciprocal Rank Fusion (RRF)
        return $this->mergeResults($keywordResults, $semanticResults);
    }

    /**
     * Perform semantic search using vector embeddings
     */
    public function semanticSearch($query, $filters = []) {
        if (!$this->embeddingService) return [];

        // Generate query embedding
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);
        if (!$queryEmbedding) return [];

        // Fetch embeddings with potential pre-filtering
        $sql = "SELECT e.document_id, e.embedding 
                FROM document_embeddings e
                INNER JOIN legislative_documents d ON e.document_id = d.id
                WHERE d.deleted_at IS NULL";
        
        $params = [];
        if (!empty($filters['type'])) {
            $sql .= " AND d.document_type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND d.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND d.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $allEmbeddings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table doesn't exist or other SQL error
            error_log("SearchService: Semantic search failed (table missing?): " . $e->getMessage());
            return [];
        }

        $scores = [];
        foreach ($allEmbeddings as $row) {
            $docEmbedding = json_decode($row['embedding'], true);
            if (!$docEmbedding) continue;
            
            $similarity = $this->cosineSimilarity($queryEmbedding, $docEmbedding);
            
            if ($similarity > 0.6) { // Lowered threshold for more results
                $scores[$row['document_id']] = $similarity;
            }
        }

        if (empty($scores)) return [];

        // Sort by similarity
        arsort($scores);
        $topIds = array_keys(array_slice($scores, 0, 50, true)); // Get top 50, pagination will handle the rest

        // Fetch full document details
        $placeholders = implode(',', array_fill(0, count($topIds), '?'));
        $sql = "SELECT d.*, u.full_name as uploaded_by_name
                FROM legislative_documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.id IN ($placeholders) AND d.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($topIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add similarity scores
        foreach ($results as &$row) {
            $row['relevance_score'] = $scores[$row['id']];
        }

        // Re-sort results by score as SQL IN clause doesn't preserve order
        usort($results, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });

        return $results;
    }

    /**
     * Simple Cosine Similarity calculation
     */
    private function cosineSimilarity($vec1, $vec2) {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        foreach ($vec1 as $i => $val) {
            $dotProduct += $val * $vec2[$i];
            $normA += $val * $val;
            $normB += $vec2[$i] * $vec2[$i];
        }
        
        $divisor = sqrt($normA) * sqrt($normB);
        return $divisor == 0 ? 0 : $dotProduct / $divisor;
    }

    /**
     * Merge results using Reciprocal Rank Fusion (RRF)
     */
    private function mergeResults($keywordResults, $semanticResults) {
        $ranks = [];
        $k = 60; // Constant for RRF

        // Score keyword results
        foreach ($keywordResults as $index => $doc) {
            $id = $doc['id'];
            $ranks[$id] = [
                'doc' => $doc,
                'score' => 1 / ($k + $index + 1)
            ];
        }

        // Add/update semantic results
        foreach ($semanticResults as $index => $doc) {
            $id = $doc['id'];
            $score = 1 / ($k + $index + 1);
            if (isset($ranks[$id])) {
                $ranks[$id]['score'] += $score;
                $ranks[$id]['doc']['relevance_score'] = ($ranks[$id]['doc']['relevance_score'] ?? 0) + ($doc['relevance_score'] ?? 0);
            } else {
                $ranks[$id] = [
                    'doc' => $doc,
                    'score' => $score
                ];
            }
        }

        // Sort by RRF score
        uasort($ranks, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_map(function($item) {
            return $item['doc'];
        }, array_values($ranks));
    }
    
    /**
     * Perform fulltext search with advanced filters
     */
    public function search($query, $filters = []) {
        $sql = "SELECT d.*, u.full_name as uploaded_by_name
                FROM legislative_documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.deleted_at IS NULL";
        
        $params = [];
        
        // Text search using LIKE (more compatible than FULLTEXT)
        if (!empty($query)) {
            $sql .= " AND (d.title LIKE :q1 OR d.description LIKE :q2 OR d.reference_number LIKE :q3 OR d.tags LIKE :q4 OR d.document_type LIKE :q5)";
            $params[':q1'] = '%' . $query . '%';
            $params[':q2'] = '%' . $query . '%';
            $params[':q3'] = '%' . $query . '%';
            $params[':q4'] = '%' . $query . '%';
            $params[':q5'] = '%' . $query . '%';
        }
        
        // Apply filters
        if (!empty($filters['type'])) {
            $sql .= " AND d.document_type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND d.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND d.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['uploaded_by'])) {
            $sql .= " AND d.uploaded_by = :uploaded_by";
            $params[':uploaded_by'] = $filters['uploaded_by'];
        }
        
        if (!empty($filters['tags'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM document_tag_relationships dtr
                INNER JOIN document_tags dt ON dtr.tag_id = dt.id
                WHERE dtr.document_id = d.id
                AND dt.id IN (" . implode(',', array_map('intval', explode(',', $filters['tags']))) . ")
            )";
        }
        
        // Order by date
        $sql .= " ORDER BY d.created_at DESC";
        
        // Pagination
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get faceted search results (counts by category)
     */
    public function getFacets($query = '') {
        $facets = [
            'by_type' => [],
            'by_status' => [],
            'by_year' => [],
            'by_month' => []
        ];
        
        $whereClause = "WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($query)) {
            $whereClause .= " AND (title LIKE :q1 OR description LIKE :q2 OR reference_number LIKE :q3)";
            $params[':q1'] = '%' . $query . '%';
            $params[':q2'] = '%' . $query . '%';
            $params[':q3'] = '%' . $query . '%';
        }
        
        // Count by document type
        $stmt = $this->db->prepare("
            SELECT document_type, COUNT(*) as count
            FROM legislative_documents
            {$whereClause}
            GROUP BY document_type
            ORDER BY count DESC
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $facets['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count by status
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count
            FROM legislative_documents
            {$whereClause}
            GROUP BY status
            ORDER BY count DESC
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $facets['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count by year
        $stmt = $this->db->prepare("
            SELECT YEAR(created_at) as year, COUNT(*) as count
            FROM legislative_documents
            {$whereClause}
            GROUP BY YEAR(created_at)
            ORDER BY year DESC
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $facets['by_year'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $facets;
    }
    
    /**
     * Export search results to CSV
     */
    public function exportToCSV($query, $filters = []) {
        $results = $this->search($query, array_merge($filters, ['limit' => 10000, 'offset' => 0]));
        
        $output = fopen('php://temp', 'w');
        
        // Headers
        fputcsv($output, [
            'Reference Number', 'Title', 'Type', 'Status', 'Date', 
            'File Name', 'File Size', 'Uploaded By', 'Created At'
        ]);
        
        // Data rows
        foreach ($results as $row) {
            fputcsv($output, [
                $row['reference_number'] ?? '',
                $row['title'],
                $row['document_type'],
                $row['status'],
                $row['document_date'] ?? $row['created_at'],
                $row['file_name'] ?? '',
                $row['file_size'] ?? '',
                $row['uploaded_by_name'] ?? '',
                $row['created_at']
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Get search suggestions
     */
    public function getSuggestions($query, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT title, reference_number, document_type
            FROM legislative_documents
            WHERE deleted_at IS NULL
            AND (title LIKE :query OR reference_number LIKE :query OR tags LIKE :query OR document_type LIKE :query)
            LIMIT :limit
        ");
        
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get search count
     */
    public function getCount($query, $filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM legislative_documents d
                WHERE d.deleted_at IS NULL";
        
        $params = [];
        
        // Use the same LIKE logic as search() for consistency
        if (!empty($query)) {
            $sql .= " AND (d.title LIKE :q1 OR d.description LIKE :q2 OR d.reference_number LIKE :q3 OR d.tags LIKE :q4 OR d.document_type LIKE :q5)";
            $params[':q1'] = '%' . $query . '%';
            $params[':q2'] = '%' . $query . '%';
            $params[':q3'] = '%' . $query . '%';
            $params[':q4'] = '%' . $query . '%';
            $params[':q5'] = '%' . $query . '%';
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND d.document_type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND d.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND d.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
}
