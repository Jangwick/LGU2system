<?php

class DocumentLink {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all links for a document
     */
    public function getByDocumentId($documentId) {
        $stmt = $this->db->prepare("
            SELECT dl.*, 
                   ld.reference_number as linked_reference,
                   ld.title as linked_title,
                   ld.document_type as linked_type,
                   ld.status as linked_status
            FROM document_links dl
            LEFT JOIN legislative_documents ld ON dl.linked_document_id = ld.id
            WHERE dl.document_id = :document_id
            AND ld.deleted_at IS NULL
            ORDER BY dl.created_at DESC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get incoming links (documents that link to this one)
     */
    public function getIncomingLinks($documentId) {
        $stmt = $this->db->prepare("
            SELECT dl.*, 
                   ld.reference_number,
                   ld.title,
                   ld.document_type,
                   ld.status
            FROM document_links dl
            LEFT JOIN legislative_documents ld ON dl.document_id = ld.id
            WHERE dl.linked_document_id = :document_id
            AND ld.deleted_at IS NULL
            ORDER BY dl.created_at DESC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create link between documents
     */
    public function create($documentId, $linkedDocumentId, $linkType = 'related') {
        // Check if link already exists
        $stmt = $this->db->prepare("
            SELECT id FROM document_links 
            WHERE document_id = :document_id 
            AND linked_document_id = :linked_document_id
        ");
        $stmt->execute([
            ':document_id' => $documentId,
            ':linked_document_id' => $linkedDocumentId
        ]);
        
        if ($stmt->fetch()) {
            return false; // Link already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO document_links (document_id, linked_document_id, link_type, created_at)
            VALUES (:document_id, :linked_document_id, :link_type, NOW())
        ");
        
        $result = $stmt->execute([
            ':document_id' => $documentId,
            ':linked_document_id' => $linkedDocumentId,
            ':link_type' => $linkType
        ]);
        
        // Create bi-directional link for certain types
        if ($result && in_array($linkType, ['supersedes', 'amends'])) {
            $reverseType = $linkType === 'supersedes' ? 'superseded_by' : 'amended_by';
            $stmt = $this->db->prepare("
                INSERT INTO document_links (document_id, linked_document_id, link_type, created_at)
                VALUES (:linked_document_id, :document_id, :reverse_type, NOW())
            ");
            $stmt->execute([
                ':linked_document_id' => $linkedDocumentId,
                ':document_id' => $documentId,
                ':reverse_type' => $reverseType
            ]);
        }
        
        return $result;
    }
    
    /**
     * Delete link
     */
    public function delete($linkId) {
        $stmt = $this->db->prepare("DELETE FROM document_links WHERE id = :id");
        return $stmt->execute([':id' => $linkId]);
    }
    
    /**
     * Delete all links for a document
     */
    public function deleteByDocument($documentId) {
        $stmt = $this->db->prepare("
            DELETE FROM document_links 
            WHERE document_id = :document_id 
            OR linked_document_id = :document_id
        ");
        return $stmt->execute([':document_id' => $documentId]);
    }
    
    /**
     * Get document lineage (trace relationships)
     */
    public function getLineage($documentId, $depth = 3) {
        $lineage = [
            'supersedes' => [],
            'superseded_by' => [],
            'amends' => [],
            'amended_by' => [],
            'related' => []
        ];
        
        $this->buildLineage($documentId, $lineage, 0, $depth);
        
        return $lineage;
    }
    
    /**
     * Recursive function to build lineage
     */
    private function buildLineage($documentId, &$lineage, $currentDepth, $maxDepth) {
        if ($currentDepth >= $maxDepth) {
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT dl.*, 
                   ld.reference_number,
                   ld.title,
                   ld.document_type,
                   ld.status
            FROM document_links dl
            LEFT JOIN legislative_documents ld ON dl.linked_document_id = ld.id
            WHERE dl.document_id = :document_id
            AND ld.deleted_at IS NULL
        ");
        $stmt->execute([':document_id' => $documentId]);
        $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($links as $link) {
            $linkType = $link['link_type'];
            if (isset($lineage[$linkType])) {
                $lineage[$linkType][] = $link;
                // Recursively get links for this document
                $this->buildLineage($link['linked_document_id'], $lineage, $currentDepth + 1, $maxDepth);
            }
        }
    }
    
    /**
     * Count links for a document
     */
    public function countByDocument($documentId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM document_links 
            WHERE document_id = :document_id
        ");
        $stmt->execute([':document_id' => $documentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
