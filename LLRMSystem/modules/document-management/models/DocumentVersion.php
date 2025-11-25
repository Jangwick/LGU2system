<?php

class DocumentVersion {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all versions for a document
     */
    public function getByDocumentId($documentId) {
        $stmt = $this->db->prepare("
            SELECT v.*, u.name as created_by_name 
            FROM document_versions v
            LEFT JOIN users u ON v.created_by = u.id
            WHERE v.document_id = :document_id
            ORDER BY v.version_number DESC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get specific version
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT v.*, u.name as created_by_name 
            FROM document_versions v
            LEFT JOIN users u ON v.created_by = u.id
            WHERE v.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get latest version number for document
     */
    public function getLatestVersionNumber($documentId) {
        $stmt = $this->db->prepare("
            SELECT MAX(version_number) as latest 
            FROM document_versions 
            WHERE document_id = :document_id
        ");
        $stmt->execute([':document_id' => $documentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['latest'] ?? 0;
    }
    
    /**
     * Create new version
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO document_versions (
                document_id, version_number, file_path, file_name,
                file_size, change_description, created_by, created_at
            ) VALUES (
                :document_id, :version_number, :file_path, :file_name,
                :file_size, :change_description, :created_by, NOW()
            )
        ");
        
        $stmt->execute([
            ':document_id' => $data['document_id'],
            ':version_number' => $data['version_number'],
            ':file_path' => $data['file_path'],
            ':file_name' => $data['file_name'],
            ':file_size' => $data['file_size'],
            ':change_description' => $data['change_description'] ?? null,
            ':created_by' => $data['created_by']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get version by document and version number
     */
    public function getByVersion($documentId, $versionNumber) {
        $stmt = $this->db->prepare("
            SELECT v.*, u.name as created_by_name 
            FROM document_versions v
            LEFT JOIN users u ON v.created_by = u.id
            WHERE v.document_id = :document_id 
            AND v.version_number = :version_number
        ");
        $stmt->execute([
            ':document_id' => $documentId,
            ':version_number' => $versionNumber
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count versions for document
     */
    public function countByDocument($documentId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM document_versions 
            WHERE document_id = :document_id
        ");
        $stmt->execute([':document_id' => $documentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
