<?php
/**
 * Legislative Analysis Service
 * Handles AI-powered summarization and vector-based similarity
 */

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../search/services/EmbeddingService.php';

class AnalysisService {
    private $db;
    private $embeddingService;
    private $apiKey;

    public function __construct() {
        $this->db = getDatabase();
        $this->embeddingService = new EmbeddingService();
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    }

    /**
     * Generate an AI summary using Gemini
     */
    public function summarizeDocument($documentId) {
        $stmt = $this->db->prepare("SELECT title, description FROM legislative_documents WHERE id = :id");
        $stmt->execute([':id' => $documentId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) return "Document not found.";
        if (empty($this->apiKey)) return "AI API Key not configured.";

        $prompt = "Summarize this legislative document professionally in 3-5 bullet points. Focus on its legal intent and impact.\n\n";
        $prompt .= "Title: " . $doc['title'] . "\n";
        $prompt .= "Description: " . $doc['description'];

        return $this->callGemini($prompt);
    }

    /**
     * Find similar documents using vector embeddings
     */
    public function findSimilarDocuments($documentId, $limit = 5) {
        // 1. Get embedding for the source document
        $stmt = $this->db->prepare("SELECT embedding FROM document_embeddings WHERE document_id = :id");
        $stmt->execute([':id' => $documentId]);
        $source = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$source) {
            // Document hasn't been embedded yet, try to generate it now
            $this->embeddingService->embedDocument($this->db, $documentId);
            $stmt->execute([':id' => $documentId]);
            $source = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$source) return [];

        $sourceVector = json_decode($source['embedding'], true);

        // 2. Fetch all other embeddings
        $stmt = $this->db->prepare("
            SELECT de.document_id, de.embedding, d.title, d.reference_number, d.document_type
            FROM document_embeddings de
            JOIN legislative_documents d ON de.document_id = d.id
            WHERE de.document_id != :id AND d.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $documentId]);
        $others = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($others as $other) {
            $otherVector = json_decode($other['embedding'], true);
            $similarity = $this->cosineSimilarity($sourceVector, $otherVector);
            
            if ($similarity > 0.5) { // Threshold
                $results[] = [
                    'id' => $other['document_id'],
                    'title' => $other['title'],
                    'reference_number' => $other['reference_number'],
                    'type' => $other['document_type'],
                    'score' => round($similarity * 100, 1)
                ];
            }
        }

        // Sort by score
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, $limit);
    }

    private function cosineSimilarity($vec1, $vec2) {
        $dot = 0;
        $mag1 = 0;
        $mag2 = 0;
        foreach ($vec1 as $idx => $val) {
            $v1 = (float)$val;
            $v2 = (float)($vec2[$idx] ?? 0);
            $dot += $v1 * $v2;
            $mag1 += $v1 * $v1;
            $mag2 += $v2 * $v2;
        }
        $mag = sqrt($mag1) * sqrt($mag2);
        return ($mag == 0) ? 0 : $dot / $mag;
    }

    private function callGemini($prompt) {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'contents' => [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . trim($this->apiKey)
        ]);

        // Disable SSL verification for XAMPP compatibility
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) return "Connection Error: " . $err;

        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($result['error']['message'])) {
            return "AI Error: " . $result['error']['message'];
        }

        return "Summary could not be generated at this time. (Check your internet connection or API Key)";
    }

    /**
     * Compare multiple documents using algorithmic logic (No AI/LLM required)
     */
    public function compareDocumentsAlgorithmic($ids) {
        if (count($ids) < 2) return null;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            SELECT d.id, d.title, d.description, d.document_date, d.document_type,
                   GROUP_CONCAT(t.name) as tags
            FROM legislative_documents d
            LEFT JOIN document_tag_relationships tr ON d.id = tr.document_id
            LEFT JOIN document_tags t ON tr.tag_id = t.id
            WHERE d.id IN ($placeholders)
            GROUP BY d.id
        ");
        $stmt->execute($ids);
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $analysis = [
            'shared_tags' => [],
            'timeline_span' => '',
            'type_distribution' => [],
            'shared_keywords' => [],
            'document_stats' => []
        ];

        // 1. Basic Stats & Type Distribution
        foreach ($docs as $doc) {
            $type = $doc['document_type'];
            $analysis['type_distribution'][$type] = ($analysis['type_distribution'][$type] ?? 0) + 1;
            
            $analysis['document_stats'][] = [
                'title' => $doc['title'],
                'word_count' => str_word_count(strip_tags($doc['description'])),
                'type' => $type
            ];
        }

        // 2. Tag Analysis
        $tagMap = [];
        foreach ($docs as $doc) {
            $tags = $doc['tags'] ? explode(',', $doc['tags']) : [];
            foreach ($tags as $tag) {
                $tagMap[$tag] = ($tagMap[$tag] ?? 0) + 1;
            }
        }
        foreach ($tagMap as $tag => $count) {
            if ($count >= 2) $analysis['shared_tags'][] = $tag; // Show tags shared by at least 2 docs
        }

        // 3. Timeline Analysis
        $dates = array_column($docs, 'document_date');
        $dateObjs = array_map(function($d) { return new DateTime($d); }, $dates);
        sort($dateObjs);
        $start = reset($dateObjs);
        $end = end($dateObjs);
        $interval = $start->diff($end);
        
        if ($interval->y > 0) $span = $interval->format('%y years, %m months');
        elseif ($interval->m > 0) $span = $interval->format('%m months, %d days');
        else $span = $interval->format('%d days');
        
        $analysis['timeline_span'] = $span;

        // 4. Keyword/Description Analysis (Simple Intersection)
        $descriptions = array_map(function($d) {
            $text = ($d['description'] ?: '') . ' ' . $d['title']; // Fallback to title if description empty
            $cleaned = strtolower(preg_replace('/[^a-z0-9 ]/', '', $text));
            $words = explode(' ', $cleaned);
            return array_unique(array_filter($words, function($w) { return strlen($w) > 4; }));
        }, $docs);
        
        if (!empty($descriptions)) {
            $intersection = $descriptions[0];
            for ($i = 1; $i < count($descriptions); $i++) {
                $intersection = array_intersect($intersection, $descriptions[$i]);
            }
            
            // Filter out common stop words
            $stopWords = ['the', 'and', 'for', 'this', 'that', 'with', 'from', 'shall', 'been', 'which', 'upon', 'under', 'their', 'every', 'each', 'through', 'within', 'between'];
            $analysis['shared_keywords'] = array_slice(array_values(array_diff($intersection, $stopWords)), 0, 15);
        }

        return $analysis;
    }

    /**
     * Compare multiple documents using AI
     */
    public function compareDocumentsAI($ids) {
        if (count($ids) < 2) return "Please select at least two documents for comparison.";
        if (empty($this->apiKey)) return "AI API Key not configured.";

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT title, description, reference_number FROM legislative_documents WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $prompt = "Compare the following legislative documents. Identify key differences, commonalities, and legal implications if they were to coexist or if one replaces the other.\n\n";
        
        foreach ($docs as $i => $doc) {
            $prompt .= "Document " . ($i + 1) . " [" . $doc['reference_number'] . "]: " . $doc['title'] . "\n";
            $prompt .= "Content Summary: " . $doc['description'] . "\n\n";
        }

        $prompt .= "Format your analysis into: 1. Executive Comparison, 2. Key Differences, 3. Conflict Analysis (if any). Use bullet points.";

        return $this->callGemini($prompt);
    }
}
