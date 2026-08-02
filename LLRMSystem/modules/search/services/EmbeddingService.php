<?php
/**
 * Embedding Service for Semantic Search
 * Handles generation of vector embeddings via Google Gemini API
 */

class EmbeddingService {
    private $apiKey;
    private $model;
    public $lastError = null;
    public $lastHttpCode = null;

    public function getLastError() {
        return $this->lastError;
    }

    public function getLastHttpCode() {
        return $this->lastHttpCode;
    }

    public function __construct() {
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        $configuredModel = (defined('GEMINI_EMBEDDING_MODEL') && GEMINI_EMBEDDING_MODEL)
            ? GEMINI_EMBEDDING_MODEL
            : 'gemini-embedding-001';
        $this->model = preg_replace('/^models\//', '', $configuredModel);
    }

    /**
     * Generate embedding vector for a given text and/or file
     * @param string $text
     * @param string|null $filePath Optional image or PDF to include as inline data
     * @return array|null Vector values
     */
    public function generateEmbedding($text, $filePath = null) {
        $this->lastError = null;
        $this->lastHttpCode = null;

        if (empty($this->apiKey)) {
            $this->lastError = 'GEMINI_API_KEY is missing';
            error_log("EmbeddingService: GEMINI_API_KEY is missing");
            return null;
        }

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:embedContent";

        $parts = [
            ['text' => $text]
        ];

        if (!empty($filePath) && file_exists($filePath) && defined('GEMINI_EMBEDDING_USE_FILE') && GEMINI_EMBEDDING_USE_FILE) {
            $mime = @mime_content_type($filePath);
            if (empty($mime)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeMap = [
                    'png'  => 'image/png',
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif'  => 'image/gif',
                    'bmp'  => 'image/bmp',
                    'tiff' => 'image/tiff',
                    'tif'  => 'image/tiff',
                    'webp' => 'image/webp',
                    'pdf'  => 'application/pdf',
                ];
                $mime = $mimeMap[$ext] ?? 'application/octet-stream';
            }

            $base64 = base64_encode(file_get_contents($filePath));
            if (!empty($base64)) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mime,
                        'data' => $base64
                    ]
                ];
            }
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $dimensionality = defined('GEMINI_EMBEDDING_DIMENSIONALITY') ? GEMINI_EMBEDDING_DIMENSIONALITY : 768;
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'models/' . $this->model,
            'content' => [
                'parts' => $parts
            ],
            'output_dimensionality' => $dimensionality
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
            $this->lastError = 'Curl: ' . $err;
            $this->lastHttpCode = $httpCode;
            error_log("EmbeddingService Curl Error: " . $err);
            return null;
        }

        $this->lastHttpCode = $httpCode;

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->lastError = 'HTTP ' . $httpCode . ': ' . $response;
            error_log("EmbeddingService HTTP Error: " . $httpCode . " Response: " . $response);
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['embedding']['values'])) {
            return $result['embedding']['values'];
        }

        $this->lastError = 'Gemini response: ' . json_encode($result);
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
     * Combines metadata and generates vector, optionally using the document file
     */
    public function embedDocument($db, $documentId, $filePath = null) {
        // Fetch document
        $stmt = $db->prepare("SELECT title, description, tags FROM legislative_documents WHERE id = :id");
        $stmt->execute([':id' => $documentId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) return false;

        // Combine content for better context
        $content = "Title: " . $doc['title'] . "\n";
        $content .= "Description: " . $doc['description'] . "\n";
        $content .= "Tags: " . $doc['tags'];

        $embedding = $this->generateEmbedding($content, $filePath);

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
                            ON DUPLICATE KEY UPDATE embedding = VALUES(embedding)");

        return $stmt->execute([
            ':id' => $documentId,
            ':emb' => $jsonEmbedding
        ]);
    }
}
