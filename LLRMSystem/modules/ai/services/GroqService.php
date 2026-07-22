<?php
/**
 * GroqService - OpenAI-compatible chat completions for legal compliance analysis
 */
class GroqService {
    private $apiKey;
    private $model;
    public $lastError = null;
    public $lastHttpCode = null;

    public function __construct() {
        $this->apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
        $this->model = (defined('GROQ_MODEL') && GROQ_MODEL) ? GROQ_MODEL : 'llama-3.3-70b-versatile';
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function getLastHttpCode() {
        return $this->lastHttpCode;
    }

    /**
     * Analyze a Valenzuela City ordinance against provided compliance rules.
     *
     * @param string $documentText
     * @param array $rules List of rule arrays with keys: code, title, summary, example_excerpt, reference_url, reference_text, vector_score
     * @return array|null Keyed by rule code with status, confidence, analysis
     */
    public function analyzeCompliance($documentText, array $rules) {
        $this->lastError = null;
        $this->lastHttpCode = null;

        if (empty($this->apiKey)) {
            $this->lastError = 'GROQ_API_KEY is missing';
            return null;
        }

        if (empty($rules)) {
            return [];
        }

        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->buildPrompt($documentText, $rules)]
            ],
            'temperature' => 0.2,
            'max_tokens' => 4096,
            'top_p' => 0.9
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $this->lastError = 'Curl: ' . $err;
            $this->lastHttpCode = $httpCode;
            error_log('GroqService Curl Error: ' . $err);
            return null;
        }

        $this->lastHttpCode = $httpCode;

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->lastError = 'HTTP ' . $httpCode . ': ' . $response;
            error_log('GroqService HTTP Error: ' . $httpCode . ' ' . $response);
            return null;
        }

        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? null;

        if (empty($content)) {
            $this->lastError = 'No completion content returned';
            error_log('GroqService Error: ' . json_encode($result));
            return null;
        }

        $jsonString = $this->extractJson($content);
        $decoded = json_decode($jsonString, true);

        if (!is_array($decoded)) {
            $this->lastError = 'Invalid JSON response';
            error_log('GroqService JSON Error: ' . $jsonString);
            return null;
        }

        return $decoded;
    }

    /**
     * Extract text from an image using a Groq vision model.
     *
     * @param string $imagePath Path to an image file
     * @param string|null $prompt Optional prompt for the vision model
     * @param string|null $model Optional vision model override
     * @param int $maxTokens Maximum tokens in the response
     * @return string|null Extracted text/description, or null on failure
     */
    public function extractTextFromImage($imagePath, $tesseractText = '', $model = null, $maxTokens = 2048) {
        $this->lastError = null;
        $this->lastHttpCode = null;

        if (empty($this->apiKey)) {
            $this->lastError = 'GROQ_API_KEY is missing';
            return null;
        }

        if (!file_exists($imagePath)) {
            $this->lastError = 'Image not found: ' . $imagePath;
            return null;
        }

        $mime = @mime_content_type($imagePath);
        if (empty($mime)) {
            $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $mimeMap = [
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'bmp'  => 'image/bmp',
                'tiff' => 'image/tiff',
                'tif'  => 'image/tiff',
                'webp' => 'image/webp',
            ];
            $mime = $mimeMap[$ext] ?? 'image/png';
        }

        $base64 = base64_encode(file_get_contents($imagePath));
        if (empty($base64)) {
            $this->lastError = 'Failed to read image file';
            return null;
        }
        $dataUrl = 'data:' . $mime . ';base64,' . $base64;

        $hasExisting = !empty(trim($tesseractText));
        $tesseractContext = '';
        if ($hasExisting) {
            $escapedExisting = substr($tesseractText, 0, 1500);
            $tesseractContext = " Tesseract OCR already extracted this text from the image:\n---\n{$escapedExisting}\n---\n";
        }

        $jsonShape = $hasExisting
            ? '{"additional_text": "...", "visual_elements": "..."}'
            : '{"text": "...", "visual_elements": "..."}';
        $textInstruction = $hasExisting
            ? 'Return only additional readable text that Tesseract missed in the "additional_text" field.'
            : 'Return all readable text from the image in the "text" field.';

        $prompt = "Analyze the provided document image. {$textInstruction} " .
            "Also describe any images, seals, signatures, stamps, diagrams, or other non-text visual content in the \"visual_elements\" field. " .
            "Use empty strings if none are found." .
            $tesseractContext .
            "\n\nReturn only this JSON object and no commentary: {$jsonShape}";

        if (empty($model)) {
            $model = defined('OCR_GROQ_MODEL') ? OCR_GROQ_MODEL : 'qwen/qwen3.6-27b';
        }

        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]]
                    ]
                ]
            ],
            'temperature' => 0.2,
            'max_tokens' => $maxTokens,
            'top_p' => 0.9
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $this->lastError = 'Curl: ' . $err;
            $this->lastHttpCode = $httpCode;
            error_log('GroqService Curl Error: ' . $err);
            return null;
        }

        $this->lastHttpCode = $httpCode;

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->lastError = 'HTTP ' . $httpCode . ': ' . $response;
            error_log('GroqService HTTP Error: ' . $httpCode . ' ' . $response);
            return null;
        }

        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? null;

        if (empty($content)) {
            $this->lastError = 'No completion content returned';
            error_log('GroqService Error: ' . json_encode($result));
            return null;
        }

        $content = $this->extractJson($content);
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            // Non-JSON response: treat the whole reply as text
            return $hasExisting
                ? ['text' => '', 'additional_text' => trim($content), 'visual_elements' => '']
                : ['text' => trim($content), 'additional_text' => '', 'visual_elements' => ''];
        }

        return [
            'text' => trim($decoded['text'] ?? ''),
            'additional_text' => trim($decoded['additional_text'] ?? ''),
            'visual_elements' => trim($decoded['visual_elements'] ?? '')
        ];
    }

    private function systemPrompt() {
        return 'You are a specialized legal compliance reviewer for Valenzuela City ordinances and local regulations. ' .
            'Focus exclusively on Valenzuela City legal standards, ordinances, and the provided reference text. ' .
            'Compare the submitted document content against the Valenzuela-specific rule and the legal reference. ' .
            'If the document does not align with the Valenzuela rule, mark it non_compliant and explain why. ' .
            'Be strict but fair. Mark a rule compliant only when the document clearly satisfies it. ' .
            'Return only a valid JSON object, no commentary.';
    }

    private function buildPrompt($documentText, array $rules) {
        $documentText = substr($documentText, 0, 12000);

        $rulesText = '';
        foreach ($rules as $r) {
            $excerpt = substr($r['example_excerpt'] ?? '', 0, 1000);
            $ref = substr($r['reference_text'] ?? '', 0, 1200);
            $score = isset($r['vector_score']) ? (float) $r['vector_score'] : 0;
            $rulesText .= "Rule: {$r['code']} - {$r['title']}\n";
            $rulesText .= "Summary: " . ($r['summary'] ?? '') . "\n";
            $rulesText .= "Example: {$excerpt}\n";
            $rulesText .= "Reference URL: " . ($r['reference_url'] ?? '') . "\n";
            $rulesText .= "Reference Text: {$ref}\n";
            $rulesText .= "Vector similarity score: {$score}%\n";
            $rulesText .= "---\n";
        }

        return "Analyze the submitted document below as a proposed Valenzuela City ordinance. " .
            "For each listed legal standard, focus only on Valenzuela City ordinances and local regulations. " .
            "Compare the document content against the Valenzuela-specific rule and the provided legal reference text. " .
            "If the document does not align with the Valenzuela rule, mark it non_compliant and explain why. " .
            "Return only a JSON object where every key is a rule code and the value is an object with these fields: " .
            "{\"status\": \"compliant\" | \"non_compliant\" | \"needs_review\", \"confidence\": 0-100, \"reason\": \"2-4 sentence legal reasoning\", \"evidence\": \"specific text or details from the document or reference that support the verdict\"}. " .
            "If a rule is not applicable to the document subject, mark it compliant with low confidence and explain why. " .
            "The document text below may contain [Visual elements] sections describing seals, signatures, stamps, or diagrams. " .
            "Treat those descriptions as evidence of the document's formal validity and completeness. " .
            "Consider the vector similarity score as a hint, but base your verdict on the Valenzuela rule and the document content.\n\n" .
            "Document Text:\n{$documentText}\n\n" .
            "Valenzuela City Legal Standards:\n{$rulesText}\n\n" .
            "JSON:";
    }

    private function extractJson($content) {
        $content = trim($content);
        if (strpos($content, '```json') === 0) {
            $content = substr($content, 7);
        } elseif (strpos($content, '```') === 0) {
            $content = substr($content, 3);
        }
        $content = trim($content);
        if (substr($content, -3) === '```') {
            $content = substr($content, 0, -3);
        }
        return trim($content);
    }
}
