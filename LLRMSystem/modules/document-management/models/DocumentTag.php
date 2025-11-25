<?php

class DocumentTag {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all tags
     */
    public function getAll() {
        $stmt = $this->db->query("
            SELECT t.*, COUNT(dtr.document_id) as usage_count
            FROM document_tags t
            LEFT JOIN document_tag_relationships dtr ON t.id = dtr.tag_id
            GROUP BY t.id
            ORDER BY t.name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get tag by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM document_tags WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get tag by slug
     */
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM document_tags WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get tags for a document
     */
    public function getByDocumentId($documentId) {
        $stmt = $this->db->prepare("
            SELECT t.*
            FROM document_tags t
            INNER JOIN document_tag_relationships dtr ON t.id = dtr.tag_id
            WHERE dtr.document_id = :document_id
            ORDER BY t.name ASC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create tag
     */
    public function create($name) {
        $slug = $this->generateSlug($name);
        
        // Check if tag already exists
        $existing = $this->getBySlug($slug);
        if ($existing) {
            return $existing['id'];
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO document_tags (name, slug, created_at)
            VALUES (:name, :slug, NOW())
        ");
        
        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update tag
     */
    public function update($id, $name) {
        $slug = $this->generateSlug($name);
        
        $stmt = $this->db->prepare("
            UPDATE document_tags 
            SET name = :name, slug = :slug
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':slug' => $slug
        ]);
    }
    
    /**
     * Delete tag
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM document_tags WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Assign tag to document
     */
    public function assignToDocument($tagId, $documentId) {
        // Check if already assigned
        $stmt = $this->db->prepare("
            SELECT * FROM document_tag_relationships 
            WHERE document_id = :document_id AND tag_id = :tag_id
        ");
        $stmt->execute([
            ':document_id' => $documentId,
            ':tag_id' => $tagId
        ]);
        
        if ($stmt->fetch()) {
            return true; // Already assigned
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO document_tag_relationships (document_id, tag_id)
            VALUES (:document_id, :tag_id)
        ");
        
        return $stmt->execute([
            ':document_id' => $documentId,
            ':tag_id' => $tagId
        ]);
    }
    
    /**
     * Remove tag from document
     */
    public function removeFromDocument($tagId, $documentId) {
        $stmt = $this->db->prepare("
            DELETE FROM document_tag_relationships 
            WHERE document_id = :document_id AND tag_id = :tag_id
        ");
        
        return $stmt->execute([
            ':document_id' => $documentId,
            ':tag_id' => $tagId
        ]);
    }
    
    /**
     * Sync tags for document (replace all)
     */
    public function syncDocumentTags($documentId, $tagIds) {
        // Delete existing tags
        $stmt = $this->db->prepare("
            DELETE FROM document_tag_relationships 
            WHERE document_id = :document_id
        ");
        $stmt->execute([':document_id' => $documentId]);
        
        // Insert new tags
        if (empty($tagIds)) {
            return true;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO document_tag_relationships (document_id, tag_id)
            VALUES (:document_id, :tag_id)
        ");
        
        foreach ($tagIds as $tagId) {
            $stmt->execute([
                ':document_id' => $documentId,
                ':tag_id' => $tagId
            ]);
        }
        
        return true;
    }
    
    /**
     * Get tag cloud data (most used tags)
     */
    public function getTagCloud($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT t.*, COUNT(dtr.document_id) as usage_count
            FROM document_tags t
            LEFT JOIN document_tag_relationships dtr ON t.id = dtr.tag_id
            GROUP BY t.id
            HAVING usage_count > 0
            ORDER BY usage_count DESC, t.name ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search tags
     */
    public function search($query) {
        $stmt = $this->db->prepare("
            SELECT * FROM document_tags 
            WHERE name LIKE :query 
            ORDER BY name ASC
            LIMIT 20
        ");
        $stmt->execute([':query' => '%' . $query . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate slug from name
     */
    private function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Get tag usage statistics
     */
    public function getStatistics() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT t.id) as total_tags,
                COUNT(dtr.document_id) as total_assignments,
                AVG(tag_usage.usage_count) as avg_usage
            FROM document_tags t
            LEFT JOIN document_tag_relationships dtr ON t.id = dtr.tag_id
            LEFT JOIN (
                SELECT tag_id, COUNT(*) as usage_count
                FROM document_tag_relationships
                GROUP BY tag_id
            ) tag_usage ON t.id = tag_usage.tag_id
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
