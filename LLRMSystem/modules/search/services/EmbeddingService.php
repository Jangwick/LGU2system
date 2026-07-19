<?php
/**
 * Embedding Service for Semantic Search
 * Handles generation of vector embeddings via Google Gemini API
 */

class EmbeddingService {
    private $apiKey;
    private $model;

    public function __construct() {
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        $this->model = (defined('GEMINI_EMBEDDING_MODEL') && GEMINI_EMBEDDING_MODEL)
            ? GEMINI_EMBEDDING_MODEL
            : 'models/gemini-embedding-001';
    }

    /**
     * Generate embedding vector for a given text
     * @param string $text
     * @return array|null Vector values
     */
    public function generateEmbedding($text) {
        if (empty($this->apiKey)) {
            error_log("EmbeddingService: GEMINI_API_KEY is missing");
            return null;
        }

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:embedContent";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $this->model,
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            error_log("EmbeddingService Curl Error: " . $err);
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("EmbeddingService HTTP Error: " . $httpCode . " Response: " . $response);
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['embedding']['values'])) {
            return $result['embedding']['values'];
        }

        error_log("EmbeddingService Gemini Error: " . json_encode($result));
        return null;
    }

    /**
     * Prepare text for a search query embedding.
     */
    public static function prepareQuery($query) {
        return "task: search result | query: " . $query;
    }

    /**
     * Prepare text for a document embedding.
     */
    public static function prepareDocument($content, $title = null) {
        $title = $title ?? 'none';
        return "title: " . $title . " | text: " . $content;
    }

    /**
     * Prepare text for a classification embedding.
     */
    public static function prepareClassificationInput($content) {
        return "task: classification | query: " . $content;
    }

    public function generateQueryEmbedding($query) {
        return $this->generateEmbedding(self::prepareQuery($query));
    }

    public function generateDocumentEmbedding($content, $title = null) {
        return $this->generateEmbedding(self::prepareDocument($content, $title));
    }

    public function generateClassificationEmbedding($content) {
        return $this->generateEmbedding(self::prepareClassificationInput($content));
    }

    /**
     * Embed a document by ID
     * Combines metadata and generates vector
     */
    public function embedDocument($db, $documentId) {
        // Fetch document
        $stmt = $db->prepare("SELECT title, description, tags FROM legislative_documents WHERE id = :id");
        $stmt->execute([':id' => $documentId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) return false;

        // Combine content for better context
        $content = "Title: " . $doc['title'] . "\n";
        $content .= "Description: " . $doc['description'] . "\n";
        $content .= "Tags: " . $doc['tags'];

        $embedding = $this->generateEmbedding($content);
        
        if ($embedding) {
            return $this->storeInDB($db, $documentId, $embedding);
        }

        return false;
    }

    /**
     * Store embedding in database
     * Note: In production, use a vector database like Pinecone or Qdrant
     * For this implementation, we use a dedicated table for embeddings
     */
    private function storeInDB($db, $documentId, $embedding) {
        $jsonEmbedding = json_encode($embedding);
        
        // Ensure table exists (simplified for this task)
        $db->exec("CREATE TABLE IF NOT EXISTS document_embeddings (
            document_id INT PRIMARY KEY,
            embedding LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE
        )");

        $stmt = $db->prepare("INSERT INTO document_embeddings (document_id, embedding) 
                            VALUES (:id, :emb) 
                            ON DUPLICATE KEY UPDATE embedding = :emb");
        
        return $stmt->execute([
            ':id' => $documentId,
            ':emb' => $jsonEmbedding
        ]);
    }
}
