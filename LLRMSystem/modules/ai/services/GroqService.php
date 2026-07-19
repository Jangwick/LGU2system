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

    private function systemPrompt() {
        return 'You are a Philippine local-government legal compliance reviewer for Valenzuela City. ' .
            'Analyze proposed ordinances against legal standards and return only a JSON object. ' .
            'Be strict but fair. Mark a rule compliant only when the document clearly satisfies it. ' .
            'Use the vector similarity score and the legal reference text as guidance. ' .
            'Return only JSON, no commentary.';
    }

    private function buildPrompt($documentText, array $rules) {
        $documentText = substr($documentText, 0, 4000);

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

        return "Analyze the proposed Valenzuela City ordinance below for compliance with each listed legal standard. " .
            "Return only a JSON object where every key is a rule code and the value is an object with three fields: " .
            "{\"status\": \"compliant\" | \"non_compliant\" | \"needs_review\", \"confidence\": 0-100, \"analysis\": \"2-4 sentence legal reasoning\"}. " .
            "If a rule is not applicable to the document subject, mark it compliant with low confidence and explain why. " .
            "Consider the vector similarity score as a hint, but use the legal reference text and the ordinance content to decide.\n\n" .
            "Ordinance Text:\n{$documentText}\n\n" .
            "Legal Standards:\n{$rulesText}\n\n" .
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
