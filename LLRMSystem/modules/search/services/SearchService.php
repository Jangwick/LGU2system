<?php

class SearchService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
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
            $sql .= " AND (d.title LIKE :query OR d.description LIKE :query OR d.document_number LIKE :query)";
            $params[':query'] = '%' . $query . '%';
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
            $whereClause .= " AND (title LIKE :query OR description LIKE :query)";
            $params[':query'] = '%' . $query . '%';
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
                $row['document_number'] ?? '',
                $row['title'],
                $row['document_type'],
                $row['status'],
                $row['session_date'] ?? $row['created_at'],
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
            AND (title LIKE :query OR reference_number LIKE :query)
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
        
        if (!empty($query)) {
            $sql .= " AND MATCH(d.title, d.description, d.tags) AGAINST(:query IN BOOLEAN MODE)";
            $params[':query'] = $query;
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
        return $result['total'];
    }
}
