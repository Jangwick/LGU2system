<?php

require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/ComplianceRule.php';
require_once __DIR__ . '/../models/DocumentComplianceResult.php';

class ComplianceService {
    private $db;
    private $documentModel;
    private $ruleModel;
    private $resultModel;

    public function __construct($db = null) {
        $this->db = $db ?? getDatabase();
        $this->documentModel = new Document($this->db);
        $this->ruleModel = new ComplianceRule($this->db);
        $this->resultModel = new DocumentComplianceResult($this->db);
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
        $corpus = $this->buildCorpus($document);

        $hasApplicable = count($applicableRules) > 0;
        $hasCompliantMatch = false;
        $hasForbidden = false;
        $checkedAt = date('Y-m-d H:i:s');

        foreach ($applicableRules as $rule) {
            $scoreResult = $this->scoreRule($corpus, $rule, $document);
            $ruleStatus = $scoreResult['score'] > 0 ? 'compliant' : 'non_compliant';

            if ($this->checkForbidden($corpus, $rule)) {
                $ruleStatus = 'non_compliant';
                $hasForbidden = true;
            } elseif ($scoreResult['score'] > 0) {
                $hasCompliantMatch = true;
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
        } elseif ($hasForbidden) {
            $overall = 'non_compliant';
            $explanation = 'The document contains terms that conflict with an active compliance standard.';
        } elseif ($hasCompliantMatch) {
            $overall = 'compliant';
            $explanation = 'The document aligns with one or more active ordinance/regulation standards.';
        } else {
            $overall = 'non_compliant';
            $explanation = 'The document does not align with any applicable ordinance/regulation standard.';
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
     * Score a document against a single rule.
     */
    private function scoreRule($corpus, $rule, $document) {
        $keywords = $this->parseKeywords($rule['keywords'] ?? '');
        $matched = [];
        $matchCount = 0;

        foreach ($keywords as $keyword) {
            if ($keyword === '') continue;
            // Count each keyword only once, allow partial word boundaries
            if (stripos($corpus, ' ' . strtolower($keyword) . ' ') !== false ||
                stripos($corpus, strtolower($keyword)) !== false) {
                $matchCount++;
                $matched[] = $keyword;
            }
        }

        $total = count($keywords);
        $score = ($total > 0) ? round(($matchCount / $total) * 100) : 0;

        // Bonus when the document reference number matches the rule's pattern
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
