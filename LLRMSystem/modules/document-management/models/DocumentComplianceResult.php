<?php

class DocumentComplianceResult {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getByDocumentId($documentId) {
        $stmt = $this->db->prepare("
            SELECT r.*, cr.code, cr.title, cr.summary, cr.document_type_scope, cr.weight, cr.is_mandatory
            FROM document_compliance_results r
            LEFT JOIN compliance_rules cr ON r.rule_id = cr.id
            WHERE r.document_id = :document_id
            ORDER BY r.score DESC, cr.weight DESC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestByDocumentId($documentId) {
        $stmt = $this->db->prepare("
            SELECT r.*, cr.code, cr.title, cr.summary
            FROM document_compliance_results r
            LEFT JOIN compliance_rules cr ON r.rule_id = cr.id
            WHERE r.document_id = :document_id
            ORDER BY r.checked_at DESC, r.id DESC
            LIMIT 1
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO document_compliance_results
            (document_id, rule_id, status, score, matched_keywords, explanation, ai_analysis, reviewer_comment, checked_by, checked_at)
            VALUES
            (:document_id, :rule_id, :status, :score, :matched_keywords, :explanation, :ai_analysis, :reviewer_comment, :checked_by, :checked_at)
        ");
        $stmt->execute([
            ':document_id' => $data['document_id'],
            ':rule_id' => $data['rule_id'] ?? null,
            ':status' => $data['status'],
            ':score' => $data['score'] ?? 0,
            ':matched_keywords' => $data['matched_keywords'] ?? null,
            ':explanation' => $data['explanation'] ?? null,
            ':ai_analysis' => $data['ai_analysis'] ?? null,
            ':reviewer_comment' => $data['reviewer_comment'] ?? null,
            ':checked_by' => $data['checked_by'] ?? null,
            ':checked_at' => $data['checked_at'] ?? null,
        ]);
        return $this->db->lastInsertId();
    }

    public function deleteByDocumentId($documentId) {
        $stmt = $this->db->prepare("DELETE FROM document_compliance_results WHERE document_id = :document_id");
        return $stmt->execute([':document_id' => $documentId]);
    }
}
