<?php

class ComplianceRule {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM compliance_rules WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM compliance_rules WHERE 1=1";
        $params = [];

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params[':is_active'] = (int)$filters['is_active'];
        }

        if (!empty($filters['document_type_scope'])) {
            $sql .= " AND (document_type_scope = :scope OR document_type_scope = 'all')";
            $params[':scope'] = $filters['document_type_scope'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (code LIKE :search OR title LIKE :search OR keywords LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY weight DESC, title ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveApplicable($documentType) {
        $stmt = $this->db->prepare("
            SELECT * FROM compliance_rules
            WHERE is_active = 1
            AND (document_type_scope = 'all' OR document_type_scope = :document_type)
            ORDER BY weight DESC, title ASC
        ");
        $stmt->execute([':document_type' => $documentType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO compliance_rules
            (code, title, summary, example_excerpt, embedding_json, document_type_scope, keywords, required_tags, forbidden_keywords, reference_pattern, weight, is_mandatory, is_active, effective_date)
            VALUES
            (:code, :title, :summary, :example_excerpt, :embedding_json, :document_type_scope, :keywords, :required_tags, :forbidden_keywords, :reference_pattern, :weight, :is_mandatory, :is_active, :effective_date)
        ");
        $stmt->execute([
            ':code' => $data['code'],
            ':title' => $data['title'],
            ':summary' => $data['summary'] ?? null,
            ':example_excerpt' => $data['example_excerpt'] ?? null,
            ':embedding_json' => $data['embedding_json'] ?? null,
            ':document_type_scope' => $data['document_type_scope'] ?? 'all',
            ':keywords' => $data['keywords'] ?? null,
            ':required_tags' => $data['required_tags'] ?? null,
            ':forbidden_keywords' => $data['forbidden_keywords'] ?? null,
            ':reference_pattern' => $data['reference_pattern'] ?? null,
            ':weight' => $data['weight'] ?? 1,
            ':is_mandatory' => isset($data['is_mandatory']) ? (int)$data['is_mandatory'] : 0,
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            ':effective_date' => $data['effective_date'] ?? null,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $allowed = ['code','title','summary','example_excerpt','embedding_json','document_type_scope','keywords','required_tags','forbidden_keywords','reference_pattern','weight','is_mandatory','is_active','effective_date'];
        $fields = [];
        $params = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":" . $field] = $data[$field] ?? null;
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE compliance_rules SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM compliance_rules WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
