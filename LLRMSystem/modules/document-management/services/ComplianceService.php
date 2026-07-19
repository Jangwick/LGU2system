<?php

require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/ComplianceRule.php';
require_once __DIR__ . '/../models/DocumentComplianceResult.php';
require_once __DIR__ . '/../../search/services/EmbeddingService.php';
require_once __DIR__ . '/../../search/services/SearchService.php';

class ComplianceService {
    private $db;
    private $documentModel;
    private $ruleModel;
    private $resultModel;
    private $embeddingService;

    public function __construct($db = null) {
        $this->db = $db ?? getDatabase();
        $this->documentModel = new Document($this->db);
        $this->ruleModel = new ComplianceRule($this->db);
        $this->resultModel = new DocumentComplianceResult($this->db);
        $this->embeddingService = new EmbeddingService();
    }

    /**
     * Run a compliance check for a single document.
     * Returns the aggregate status and per-rule results.
     */
    public function checkDocument($documentId, $userId = null) {
        $document = $this->documentModel->getById($documentId);
        if (!$document) {
            throw new Exception("Document not found");
        }

        // Clear any previous results for this document
        $this->resultModel->deleteByDocumentId($documentId);

        $applicableRules = $this->ruleModel->getActiveApplicable($document['document_type'] ?? '');
        $complianceText = $this->buildComplianceText($document);
        $docVector = $this->getDocumentVector($documentId, $complianceText, $document['title'] ?? null);

        $hasApplicable = count($applicableRules) > 0;
        $hasForbidden = false;
        $allCompliant = true;
        $checkedAt = date('Y-m-d H:i:s');

        foreach ($applicableRules as $rule) {
            $scoreResult = $this->scoreRule($docVector, $complianceText, $rule, $document);
            $ruleStatus = ($scoreResult['score'] >= 70 && !$this->checkForbidden($complianceText, $rule)) ? 'compliant' : 'non_compliant';

            if ($this->checkForbidden($complianceText, $rule)) {
                $hasForbidden = true;
            }

            if ($ruleStatus !== 'compliant') {
                $allCompliant = false;
            }

            $this->resultModel->create([
                'document_id' => $documentId,
                'rule_id' => $rule['id'],
                'status' => $ruleStatus,
                'score' => $scoreResult['score'],
                'matched_keywords' => $scoreResult['matched'],
                'explanation' => $scoreResult['explanation'],
                'checked_by' => $userId,
                'checked_at' => $checkedAt
            ]);
        }

        if (!$hasApplicable) {
            $overall = 'compliant';
            $explanation = 'No applicable compliance standards were found for this document type.';
        } elseif ($hasForbidden || !$allCompliant) {
            $overall = 'non_compliant';
            $explanation = 'The document does not meet one or more applicable ordinance/regulation standards.';
        } else {
            $overall = 'compliant';
            $explanation = 'The document aligns with all applicable ordinance/regulation standards.';
        }

        $this->documentModel->update($documentId, [
            'compliance_status' => $overall,
            'compliance_checked_at' => $checkedAt
        ]);

        return [
            'success' => true,
            'compliance_status' => $overall,
            'explanation' => $explanation,
            'results' => $this->resultModel->getByDocumentId($documentId)
        ];
    }

    /**
     * Reject a non-compliant document and record the compliance comment.
     */
    public function rejectWithComment($documentId, $comment, $userId) {
        $document = $this->documentModel->getById($documentId);
        if (!$document) {
            throw new Exception("Document not found");
        }

        $oldStatus = $document['status'] ?? 'draft';
        $newStatus = 'rejected';
        $timestamp = date('Y-m-d H:i:s');

        $this->documentModel->update($documentId, [
            'status' => $newStatus,
            'status_changed_by' => $userId,
            'status_changed_at' => $timestamp,
            'approved_by' => null,
            'approved_at' => null
        ]);

        $this->documentModel->addStatusHistory($documentId, $oldStatus, $newStatus, $userId, $comment);

        return [
            'success' => true,
            'message' => 'Document rejected with compliance comment.',
            'status' => $newStatus
        ];
    }

    /**
     * Build a lowercased searchable corpus from the document fields.
     */
    private function buildCorpus($document) {
        $parts = [
            $document['title'] ?? '',
            $document['reference_number'] ?? '',
            $document['description'] ?? '',
            $document['tags'] ?? '',
            $document['extracted_text'] ?? ''
        ];
        return ' ' . strtolower(strip_tags(implode(' ', $parts))) . ' ';
    }

    /**
     * Split a comma separated keyword string into an array of trimmed non-empty terms.
     */
    private function parseKeywords($string) {
        if (empty($string)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $string))));
    }

    /**
     * Score a document against a single rule using vector similarity
     * (with a legacy keyword fallback if embedding generation fails).
     */
    private function scoreRule($docVector, $complianceText, $rule, $document) {
        if ($docVector) {
            $ruleVector = $this->getRuleVector($rule);
            if ($ruleVector) {
                $similarity = SearchService::cosineSimilarity($docVector, $ruleVector);
                $score = min(100, max(0, round($similarity * 100)));
                $explanation = 'Semantic similarity to rule example: ' . $score . '%';
                return [
                    'score' => $score,
                    'matched' => 'Similarity: ' . $score . '%',
                    'explanation' => $explanation
                ];
            }
        }

        return $this->keywordScoreFallback($rule, $document);
    }

    /**
     * Build a focused compliance text from document fields for embedding.
     */
    private function buildComplianceText($document) {
        $parts = [
            $document['title'] ?? '',
            $document['reference_number'] ?? '',
            $document['description'] ?? '',
            $document['tags'] ?? '',
            substr($document['extracted_text'] ?? '', 0, 2000)
        ];
        return implode(' ', array_filter($parts));
    }

    /**
     * Get or generate a document embedding vector.
     */
    private function getDocumentVector($documentId, $text, $title = null) {
        $cached = $this->getCachedDocumentVector($documentId);
        if ($cached) return $cached;

        $vector = $this->embeddingService->generateDocumentEmbedding($text, $title);
        if ($vector) {
            $this->cacheDocumentVector($documentId, $vector);
        }
        return $vector;
    }

    /**
     * Retrieve a cached document compliance embedding vector.
     */
    private function getCachedDocumentVector($documentId) {
        $stmt = $this->db->prepare("SELECT embedding FROM document_compliance_embeddings WHERE document_id = :id");
        $stmt->execute([':id' => $documentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['embedding'])) {
            $vector = json_decode($row['embedding'], true);
            if (is_array($vector) && !empty($vector)) {
                return $vector;
            }
        }
        return null;
    }

    /**
     * Cache a document compliance embedding vector.
     */
    private function cacheDocumentVector($documentId, $vector) {
        $json = json_encode($vector);
        $stmt = $this->db->prepare("
            INSERT INTO document_compliance_embeddings (document_id, embedding)
            VALUES (:id, :emb)
            ON DUPLICATE KEY UPDATE embedding = :emb
        ");
        return $stmt->execute([':id' => $documentId, ':emb' => $json]);
    }

    /**
     * Get or generate a rule example embedding vector.
     */
    private function getRuleVector($rule) {
        if (!empty($rule['embedding_json'])) {
            $cached = json_decode($rule['embedding_json'], true);
            if (is_array($cached) && !empty($cached)) {
                return $cached;
            }
        }

        $text = $rule['example_excerpt'] ?? ($rule['summary'] ?? ($rule['keywords'] ?? ''));
        if (empty($text)) {
            return null;
        }

        $vector = $this->embeddingService->generateDocumentEmbedding($text, $rule['code'] ?? $rule['title']);
        if ($vector) {
            $this->ruleModel->update($rule['id'], [
                'embedding_json' => json_encode($vector)
            ]);
        }
        return $vector;
    }

    /**
     * Legacy keyword-based scoring used only when embeddings are unavailable.
     */
    private function keywordScoreFallback($rule, $document) {
        $corpus = $this->buildCorpus($document);
        $keywords = $this->parseKeywords($rule['keywords'] ?? '');
        $matched = [];
        $matchCount = 0;

        foreach ($keywords as $keyword) {
            if ($keyword === '') continue;
            if (stripos($corpus, ' ' . strtolower($keyword) . ' ') !== false ||
                stripos($corpus, strtolower($keyword)) !== false) {
                $matchCount++;
                $matched[] = $keyword;
            }
        }

        $total = count($keywords);
        $score = ($total > 0) ? round(($matchCount / $total) * 100) : 0;

        if (!empty($rule['reference_pattern']) && !empty($document['reference_number'])) {
            $pattern = $rule['reference_pattern'];
            $delim = substr($pattern, 0, 1);
            if (!in_array($delim, ['/', '#', '~', '@'])) {
                $pattern = '/' . $pattern . '/';
            }
            if (preg_match($pattern, $document['reference_number'])) {
                $score = min(100, $score + 25);
            }
        }

        $matchedString = implode(', ', array_unique($matched));
        $explanation = $score > 0
            ? 'Matched keywords: ' . $matchedString
            : 'No matching keywords found for this standard.';

        return [
            'score' => $score,
            'matched' => $matchedString,
            'explanation' => $explanation
        ];
    }

    /**
     * Check whether the document contains any forbidden keywords for a rule.
     */
    private function checkForbidden($corpus, $rule) {
        $forbidden = $this->parseKeywords($rule['forbidden_keywords'] ?? '');
        foreach ($forbidden as $term) {
            if ($term !== '' && stripos($corpus, strtolower($term)) !== false) {
                return true;
            }
        }
        return false;
    }
}
