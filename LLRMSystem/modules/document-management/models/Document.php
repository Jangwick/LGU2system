<?php

class Document {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all documents with filters
     */
    public function getAll($filters = []) {
        $sql = "SELECT d.*, u.name as uploaded_by_name 
                FROM legislative_documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.deleted_at IS NULL";
        
        $params = [];
        
        // Role-based filtering: Viewers can see approved, archived, and rejected documents
        if (!empty($filters['user_role']) && $filters['user_role'] === 'viewer') {
            $sql .= " AND d.status IN ('approved', 'archived', 'rejected')";
        }
        
        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= " AND (d.title LIKE :search1 OR d.reference_number LIKE :search2 OR d.description LIKE :search3)";
            $searchValue = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchValue;
            $params[':search2'] = $searchValue;
            $params[':search3'] = $searchValue;
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
            $sql .= " AND d.document_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND d.document_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['tags'])) {
            $sql .= " AND d.tags LIKE :tags";
            $params[':tags'] = '%' . $filters['tags'] . '%';
        }
        
        if (!empty($filters['reference'])) {
            $sql .= " AND d.reference_number LIKE :reference";
            $params[':reference'] = '%' . $filters['reference'] . '%';
        }
        
        if (!empty($filters['file_size'])) {
            if ($filters['file_size'] === 'small') {
                $sql .= " AND d.file_size < 1048576";
            } elseif ($filters['file_size'] === 'medium') {
                $sql .= " AND d.file_size BETWEEN 1048576 AND 10485760";
            } elseif ($filters['file_size'] === 'large') {
                $sql .= " AND d.file_size > 10485760";
            }
        }
        
        // Sorting
        $orderBy = $filters['sort_by'] ?? 'created_at';
        $orderDir = $filters['sort_dir'] ?? 'DESC';
        $sql .= " ORDER BY d.$orderBy $orderDir";
        
        // Pagination
        $limit = $filters['limit'] ?? 10;
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
     * Get document by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.name as uploaded_by_name 
            FROM legislative_documents d
            LEFT JOIN users u ON d.uploaded_by = u.id
            WHERE d.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new document
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO legislative_documents (
                reference_number, title, document_type, document_date,
                status, file_path, file_name, file_size, file_type,
                description, tags, source_module, source_id,
                uploaded_by, created_at
            ) VALUES (
                :reference_number, :title, :document_type, :document_date,
                :status, :file_path, :file_name, :file_size, :file_type,
                :description, :tags, :source_module, :source_id,
                :uploaded_by, NOW()
            )
        ");
        
        $stmt->execute([
            ':reference_number' => $data['reference_number'],
            ':title' => $data['title'],
            ':document_type' => $data['document_type'],
            ':document_date' => $data['document_date'],
            ':status' => $data['status'] ?? 'draft',
            ':file_path' => $data['file_path'],
            ':file_name' => $data['file_name'],
            ':file_size' => $data['file_size'],
            ':file_type' => $data['file_type'],
            ':description' => $data['description'] ?? null,
            ':tags' => $data['tags'] ?? null,
            ':source_module' => $data['source_module'] ?? 'manual',
            ':source_id' => $data['source_id'] ?? null,
            ':uploaded_by' => $data['uploaded_by']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update document
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE legislative_documents SET
                title = :title,
                document_type = :document_type,
                document_date = :document_date,
                status = :status,
                description = :description,
                tags = :tags,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':document_type' => $data['document_type'],
            ':document_date' => $data['document_date'],
            ':status' => $data['status'],
            ':description' => $data['description'] ?? null,
            ':tags' => $data['tags'] ?? null
        ]);
    }
    
    /**
     * Delete document (soft delete)
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE legislative_documents SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Permanently delete document (Super Admin only)
     */
    public function forceDelete($id, $userId = null) {
        // Check if user is Super Admin
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }

        if (!$userId) {
            return ['success' => false, 'error' => 'User not authenticated'];
        }

        // Get user role
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || strtolower($user['role']) !== 'super_admin') {
            return ['success' => false, 'error' => 'Only Super Admin can permanently delete documents'];
        }

        $stmt = $this->db->prepare("DELETE FROM legislative_documents WHERE id = :id");
        $result = $stmt->execute([':id' => $id]);

        if ($result) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to permanently delete document'];
    }
    
    /**
     * Restore soft deleted document
     */
    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE legislative_documents SET deleted_at = NULL WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Get trashed documents
     */
    public function getTrashed($filters = []) {
        $sql = "SELECT d.*, u.name as uploaded_by_name 
                FROM legislative_documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.deleted_at IS NOT NULL";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (d.title LIKE :search1 OR d.reference_number LIKE :search2)";
            $searchValue = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchValue;
            $params[':search2'] = $searchValue;
        }
        
        $sql .= " ORDER BY d.deleted_at DESC";
        
        $limit = $filters['limit'] ?? 10;
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
     * Get total count with filters
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM legislative_documents WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE :search1 OR reference_number LIKE :search2 OR description LIKE :search3)";
            $searchValue = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchValue;
            $params[':search2'] = $searchValue;
            $params[':search3'] = $searchValue;
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND document_type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND document_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND document_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['tags'])) {
            $sql .= " AND tags LIKE :tags";
            $params[':tags'] = '%' . $filters['tags'] . '%';
        }
        
        if (!empty($filters['reference'])) {
            $sql .= " AND reference_number LIKE :reference";
            $params[':reference'] = '%' . $filters['reference'] . '%';
        }
        
        if (!empty($filters['file_size'])) {
            if ($filters['file_size'] === 'small') {
                $sql .= " AND file_size < 1048576";
            } elseif ($filters['file_size'] === 'medium') {
                $sql .= " AND file_size BETWEEN 1048576 AND 10485760";
            } elseif ($filters['file_size'] === 'large') {
                $sql .= " AND file_size > 10485760";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Generate reference number
     */
    /**
     * Generate reference number
     */
    public function generateReferenceNumber($type, $year = null) {
        $prefix = $this->getTypePrefix($type);
        $year = $year ?: date('Y');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM legislative_documents 
            WHERE document_type = :type 
            AND YEAR(document_date) = :year
            AND deleted_at IS NULL
        ");
        $stmt->execute([':type' => $type, ':year' => $year]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $number = str_pad($result['count'] + 1, 3, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$number}";
    }
    
    /**
     * Get type prefix for reference number
     */
    private function getTypePrefix($type) {
        $prefixes = [
            'ordinance' => 'ORD',
            'resolution' => 'RES',
            'session' => 'SES',
            'agenda' => 'AGD',
            'committee' => 'COM',
            'voting' => 'VOT',
            'hearing' => 'HRG',
            'archive' => 'ARC',
            'consultation' => 'CON',
            'research' => 'RSC'
        ];
        
        return $prefixes[$type] ?? 'DOC';
    }
    /**
     * Get document versions
     */
    public function getVersions($documentId) {
        $stmt = $this->db->prepare("
            SELECT v.*, u.name as created_by_name 
            FROM document_versions v
            LEFT JOIN users u ON v.created_by = u.id
            WHERE v.document_id = :id
            ORDER BY v.version_number DESC
        ");
        $stmt->execute([':id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get related documents
     */
    public function getRelated($documentId) {
        $stmt = $this->db->prepare("
            SELECT d.id, d.title, d.reference_number, d.document_type, l.link_type
            FROM document_links l
            JOIN legislative_documents d ON l.linked_document_id = d.id
            WHERE l.document_id = ? AND d.deleted_at IS NULL
            UNION
            SELECT d.id, d.title, d.reference_number, d.document_type, l.link_type
            FROM document_links l
            JOIN legislative_documents d ON l.document_id = d.id
            WHERE l.linked_document_id = ? AND d.deleted_at IS NULL
        ");
        $stmt->execute([$documentId, $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get document activity logs
     */
    public function getActivity($documentId) {
        $stmt = $this->db->prepare("
            (SELECT 'access' as type, a.access_type as action, a.accessed_at as created_at, u.name as user_name, '' as description
             FROM document_access_logs a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.document_id = ?)
            UNION
            (SELECT 'activity' as type, l.action, l.created_at, u.name as user_name, l.description
             FROM activity_logs l
             LEFT JOIN users u ON l.user_id = u.id
             WHERE l.table_name = 'documents' AND l.record_id = ?)
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$documentId, $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
