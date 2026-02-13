<?php
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';

class ResearchController {
    private $db;
    private $permissions;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->permissions = new PermissionMiddleware($this->db);
        
        // Require at least officer access for research tools
        $this->permissions->requireLogin();
        $this->permissions->requireMinimumRole('officer');
    }
    
    /**
     * Get Legislative Trends Data
     */
    public function getTrendsData() {
        // Topic Trends (based on tags)
        $trends = [];
        $stmt = $this->db->query("
            SELECT name as topic, count(tr.document_id) as count
            FROM document_tags t
            JOIN document_tag_relationships tr ON t.id = tr.tag_id
            GROUP BY topic
            ORDER BY count DESC
            LIMIT 10
        ");
        $trends['top_topics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Document Volume Trend
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as count
            FROM legislative_documents
            WHERE deleted_at IS NULL
            GROUP BY month
            ORDER BY month ASC
            LIMIT 12
        ");
        $trends['volume_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $trends;
    }

    /**
     * Get documents for comparison
     */
    public function getDocumentsForComparison($ids) {
        if (empty($ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            SELECT d.*, u.full_name as author
            FROM legislative_documents d
            LEFT JOIN users u ON d.uploaded_by = u.id
            WHERE d.id IN ($placeholders) AND d.deleted_at IS NULL
        ");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Cross-Reference Map Data
     */
    public function getCrossReferenceData() {
        // Find documents that mention other documents in their description or title
        // For a real implementation, we'd use a regex for Ref Numbers
        $stmt = $this->db->query("
            SELECT id, title, reference_number, document_type, description
            FROM legislative_documents
            WHERE deleted_at IS NULL
            ORDER BY created_at DESC
        ");
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $links = [];
        $nodes = [];
        
        // Simple heuristic: look for reference numbers in descriptions
        foreach ($docs as $doc) {
            $nodes[] = [
                'id' => $doc['id'],
                'label' => $doc['reference_number'],
                'title' => $doc['title'],
                'type' => $doc['document_type']
            ];
            
            foreach ($docs as $target) {
                if ($doc['id'] === $target['id']) continue;
                
                // If ref number of target appears in doc's description
                if (strpos($doc['description'], $target['reference_number']) !== false) {
                    $links[] = ['source' => $doc['id'], 'target' => $target['id']];
                }
            }
        }
        
        return ['nodes' => $nodes, 'links' => $links];
    }
}
