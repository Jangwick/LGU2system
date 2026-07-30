<?php

require_once __DIR__ . '/OcrService.php';
require_once __DIR__ . '/../../search/services/EmbeddingService.php';
require_once __DIR__ . '/../../search/services/SearchService.php';

/**
 * Detect duplicate documents on upload/receive so OCR/Groq work can be reused.
 */
class DeduplicationService {
    private $db;
    private $ocrService;
    private $embeddingService;
    private $threshold;

    public function __construct($db, $ocrService = null, $embeddingService = null, $threshold = 0.95) {
        $this->db = $db;
        $this->ocrService = $ocrService ?? new OcrService();
        $this->embeddingService = $embeddingService ?? new EmbeddingService();
        $this->threshold = $threshold;
        $this->ensureTables();
    }

    private function ensureTables() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS document_hashes (
            document_id INT NOT NULL,
            file_hash VARCHAR(64) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (document_id),
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE
        )");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_document_hashes_file_hash ON document_hashes(file_hash)");

        $this->db->exec("CREATE TABLE IF NOT EXISTS document_ocr_embeddings (
            document_id INT NOT NULL,
            embedding LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (document_id),
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE
        )");
    }

    /**
     * Look for an existing document with the same file or semantic OCR content.
     * Returns the matching existing document row or null.
     */
    public function findDuplicate($filePath, $mimeType = null, $title = '') {
        // 1. Exact hash match (still kept for final result, but does not short-circuit)
        $hashMatch = null;
        $hash = $this->hashFile($filePath);
        if ($hash) {
            $stmt = $this->db->prepare("
                SELECT h.document_id, d.extracted_text, d.key_points, d.ocr_status
                FROM document_hashes h
                INNER JOIN legislative_documents d ON h.document_id = d.id
                WHERE h.file_hash = ? AND d.ocr_status = 'completed' AND d.extracted_text IS NOT NULL AND d.extracted_text != ''
                ORDER BY h.document_id DESC
                LIMIT 1
            ");
            $stmt->execute([$hash]);
            $hashMatch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // 2. Always run a quick Tesseract-only OCR pre-scan for the embedding check (no Groq)
        $quick = $this->ocrService->extractText($filePath, $mimeType, ['enhance' => false]);
        $quickText = (($quick['status'] ?? '') === 'completed') ? trim($quick['text'] ?? '') : '';

        $embeddingMatch = null;
        $embedding = null;
        if (!empty($quickText)) {
            $embedding = $this->embeddingService->generateDocumentEmbedding($quickText, $title);
            if (!empty($embedding)) {
                $stmt = $this->db->query("
                    SELECT e.document_id, e.embedding, d.extracted_text, d.key_points, d.ocr_status
                    FROM document_ocr_embeddings e
                    INNER JOIN legislative_documents d ON e.document_id = d.id
                    WHERE d.ocr_status = 'completed' AND d.extracted_text IS NOT NULL AND d.extracted_text != ''
                ");
                $best = null;
                $bestScore = 0;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $stored = json_decode($row['embedding'] ?? '', true);
                    if (empty($stored) || !is_array($stored)) continue;
                    $score = SearchService::cosineSimilarity($embedding, $stored);
                    if ($score >= $this->threshold && $score > $bestScore) {
                        $best = $row;
                        $bestScore = $score;
                    }
                }
                if ($best) {
                    $embeddingMatch = $best;
                }
            }
        }

        // Exact hash match is the strongest signal; otherwise fall back to semantic match
        if ($hashMatch) {
            return ['type' => 'hash', 'document' => $hashMatch, 'embedding' => $embedding];
        }
        if ($embeddingMatch) {
            return ['type' => 'embedding', 'document' => $embeddingMatch, 'embedding' => $embedding];
        }
        return null;
    }

    /**
     * Persist hash and OCR-text embedding for a document so future uploads can match it.
     */
    public function store($documentId, $filePath, $text, $title = '') {
        $hash = $this->hashFile($filePath);
        if ($hash) {
            $stmt = $this->db->prepare("
                INSERT INTO document_hashes (document_id, file_hash)
                VALUES (:id, :hash)
                ON DUPLICATE KEY UPDATE file_hash = VALUES(file_hash)
            ");
            $stmt->execute([':id' => $documentId, ':hash' => $hash]);
        }

        $embedding = $this->embeddingService->generateDocumentEmbedding($text, $title);
        if ($embedding) {
            $json = json_encode($embedding);
            $stmt = $this->db->prepare("
                INSERT INTO document_ocr_embeddings (document_id, embedding)
                VALUES (:id, :emb)
                ON DUPLICATE KEY UPDATE embedding = VALUES(embedding)
            ");
            $stmt->execute([':id' => $documentId, ':emb' => $json]);
        }
    }

    private function hashFile($path) {
        if (!file_exists($path) || !is_readable($path)) return null;
        return hash_file('sha256', $path);
    }
}
