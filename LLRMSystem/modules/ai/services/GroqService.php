<?php
/**
 * GroqService - OpenAI-compatible chat completions for legal compliance analysis
 */
class GroqService {
    private $apiKey;
    private $model;
    private $fallbackModels;
    public $lastError = null;
    public $lastHttpCode = null;
    private $retryWaitBudget = 20;
    private $retryWaitUsed = 0;

    public function __construct() {
        $this->apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
        $this->retryWaitBudget = defined('GROQ_MAX_RETRY_WAIT') ? (int) GROQ_MAX_RETRY_WAIT : 20;
        $this->model = (defined('GROQ_MODEL') && GROQ_MODEL) ? GROQ_MODEL : 'llama-3.3-70b-versatile';
        $this->fallbackModels = $this->loadFallbackModels();
    }

    /**
     * Build the ordered list of models to try on rate-limit or failure.
     */
    private function loadFallbackModels() {
        $models = [];
        if (defined('GROQ_FALLBACK_MODELS') && GROQ_FALLBACK_MODELS) {
            $parts = array_map('trim', explode(',', GROQ_FALLBACK_MODELS));
            $models = array_values(array_filter($parts));
        }
        return $models;
    }

    /**
     * Extract a retry wait time (seconds) from a Groq 429 error body.
     */
    private function parseRetryAfter($response) {
        if (preg_match('/try again in ([\d.]+)s/i', $response, $m)) {
            return (int) ceil((float) $m[1]);
        }
        if (preg_match('/retry[_-]after[\s:]*(\d+)/i', $response, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    /**
     * POST to the Groq chat endpoint, rotating through fallback models and
     * honoring the retry-after hint on 429 rate-limit responses.
     * Returns [response, httpCode] or null if all models fail.
     */
    private function callWithFallback($apiUrl, $payload, $isVision = false) {
        $maxAttemptsPerModel = 3;
        $primaryModel = $payload['model'] ?? $this->model;
        $models = array_values(array_unique(array_merge([$primaryModel], $this->fallbackModels)));
        foreach ($models as $model) {
            for ($attempt = 0; $attempt < $maxAttemptsPerModel; $attempt++) {
                $attemptPayload = $payload;
                $attemptPayload['model'] = $model;

                $httpResult = $this->httpPost($apiUrl, $attemptPayload);
                if ($httpResult === null) {
                    continue;
                }

                $httpCode = $httpResult['httpCode'];
                $this->lastHttpCode = $httpCode;

                if ($httpCode >= 200 && $httpCode < 300) {
                    return $httpResult;
                }

                if ($httpCode == 429) {
                    $wait = $this->parseRetryAfter($httpResult['response']);
                    if ($wait <= 0) {
                        $wait = min(30, 5 * ($attempt + 1)); // 5, 10, 15 ... up to 30s
                    }
                    // Respect a global sleep budget so a rate-limited run degrades
                    // quickly instead of blocking the request for minutes.
                    $remaining = $this->retryWaitBudget - $this->retryWaitUsed;
                    if ($remaining <= 0) {
                        error_log('GroqService: retry wait budget exhausted, moving to next model');
                        break;
                    }
                    $wait = min($wait, $remaining);
                    $this->retryWaitUsed += $wait;
                    error_log('GroqService: model ' . $model . ' rate limited, waiting ' . $wait . 's');
                    sleep($wait);
                    continue;
                }

                if ($httpCode >= 400 && $httpCode < 500) {
                    error_log('GroqService: model ' . $model . ' returned HTTP ' . $httpCode . ', trying next model');
                    break; // non-retryable for this model (e.g., vision not supported)
                }

                // 5xx / other transient: retry same model
            }
        }

        return null;
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * POST JSON to Groq API — uses curl if available, falls back to file_get_contents
     * Returns ['response' => string, 'httpCode' => int] or null on failure
     */
    private function httpPost($url, $payload) {
        $jsonBody = json_encode($payload);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
                error_log('GroqService Curl Error: ' . $err);
                return null;
            }
            return ['response' => $response, 'httpCode' => $httpCode];
        }

        // Fallback: file_get_contents with stream context
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $jsonBody,
                'timeout' => 120,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            $this->lastError = 'HTTP request failed (no curl, file_get_contents failed)';
            error_log('GroqService: file_get_contents failed');
            return null;
        }
        $httpCode = 200;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $header, $m)) {
                    $httpCode = (int)$m[1];
                }
            }
        }
        return ['response' => $response, 'httpCode' => $httpCode];
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

        $this->retryWaitUsed = 0;

        $docBudget = defined('GROQ_MAX_DOCUMENT_CHARS') ? (int) GROQ_MAX_DOCUMENT_CHARS : 6000;
        $documentText = substr($documentText, 0, $docBudget);

        $merged = [];
        $errors = [];

        foreach ($this->chunkRules($rules) as $batch) {
            $batchResult = $this->analyzeBatch($documentText, $batch, $docBudget);
            if (is_array($batchResult)) {
                $merged = array_merge($merged, $batchResult);
            } else {
                $codes = array_map(function ($r) { return $r['code']; }, $batch);
                $errors[] = implode(',', $codes) . ': ' . $this->lastError;
            }
        }

        if (empty($merged)) {
            $this->lastError = 'All Groq batches failed. ' . implode(' | ', $errors);
            return null;
        }

        $this->lastError = empty($errors) ? null : 'Partial analysis. ' . implode(' | ', $errors);
        return $merged;
    }

    /**
     * Split rules into batches small enough to stay under the Groq request size limit.
     */
    private function chunkRules(array $rules) {
        $budget = defined('GROQ_MAX_RULES_CHARS') ? (int) GROQ_MAX_RULES_CHARS : 3500;

        $batches = [];
        $current = [];
        $currentSize = 0;

        foreach ($rules as $rule) {
            $size = $this->estimateRuleSize($rule);
            if (!empty($current) && ($currentSize + $size) > $budget) {
                $batches[] = $current;
                $current = [];
                $currentSize = 0;
            }
            $current[] = $rule;
            $currentSize += $size;
        }

        if (!empty($current)) {
            $batches[] = $current;
        }

        return $batches;
    }

    /**
     * Approximate the prompt cost of a single rule.
     */
    private function estimateRuleSize($rule) {
        return strlen($rule['code'] ?? '')
            + strlen($rule['title'] ?? '')
            + strlen($rule['summary'] ?? '')
            + strlen(substr($rule['example_excerpt'] ?? '', 0, 1000))
            + strlen(substr($rule['reference_text'] ?? '', 0, 1200))
            + 120; // labels and separators
    }

    /**
     * Analyze one batch of rules, shrinking the payload and splitting the batch
     * when Groq rejects the request as too large.
     */
    private function analyzeBatch($documentText, array $rules, $docBudget) {
        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->buildPrompt($documentText, $rules)]
            ],
            'temperature' => 0.2,
            'max_tokens' => 10000,
            'top_p' => 0.9,
            'response_format' => ['type' => 'json_object']
        ];

        $httpResult = $this->callWithFallback($apiUrl, $payload);

        if ($httpResult === null || $httpResult['httpCode'] == 413) {
            // Too large: first try a shorter document excerpt, then split the batch.
            if ($this->lastHttpCode == 413 && $docBudget > 1500) {
                return $this->analyzeBatch(substr($documentText, 0, (int) ($docBudget / 2)), $rules, (int) ($docBudget / 2));
            }

            if (count($rules) > 1) {
                $half = (int) ceil(count($rules) / 2);
                $left = $this->analyzeBatch($documentText, array_slice($rules, 0, $half), $docBudget);
                $right = $this->analyzeBatch($documentText, array_slice($rules, $half), $docBudget);
                $combined = array_merge(is_array($left) ? $left : [], is_array($right) ? $right : []);
                return empty($combined) ? null : $combined;
            }

            if ($this->lastError === null) {
                $this->lastError = 'All Groq models failed for compliance analysis';
            }
            return null;
        }

        $response = $httpResult['response'];
        $httpCode = $httpResult['httpCode'];
        $this->lastHttpCode = $httpCode;

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->lastError = 'HTTP ' . $httpCode . ': ' . substr($response, 0, 300);
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

        $httpResult = $this->callWithFallback($apiUrl, $payload, true);
        if ($httpResult === null) {
            $this->lastError = 'All Groq models failed for image text extraction';
            return null;
        }

        $response = $httpResult['response'];
        $httpCode = $httpResult['httpCode'];
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
            $cleaned = $this->cleanAIResponse($content);
            return $hasExisting
                ? ['text' => '', 'additional_text' => trim($cleaned), 'visual_elements' => '']
                : ['text' => trim($cleaned), 'additional_text' => '', 'visual_elements' => ''];
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
            $score = isset($r['vector_score']) ? (float) $r['vector_score'] : 0;
            $rulesText .= "Rule: {$r['code']} - {$r['title']}\n";
            $rulesText .= "Summary: " . ($r['summary'] ?? '') . "\n";
            $rulesText .= "Vector similarity score: {$score}%\n";
            $rulesText .= "---\n";
        }

        return "Analyze the submitted document below as a proposed Valenzuela City ordinance. " .
            "For each listed legal standard, focus only on Valenzuela City ordinances and local regulations. " .
            "Compare the document content against the Valenzuela-specific rule and the provided legal reference text. " .
            "If the document does not align with the Valenzuela rule, mark it non_compliant and explain why. " .
            "Return only a valid JSON object, no markdown, where every key is a rule code and the value is an object with these fields: " .
            "{\"status\": \"compliant\" | \"non_compliant\" | \"needs_review\", \"confidence\": 0-100, \"reason\": \"3-4 sentences of legal/procedural analysis. State the specific rule requirement, what the document actually contains (or omits), and why that means compliance or non-compliance. For non-compliant, also note what content would be needed. Max 520 characters\", \"evidence\": \"Direct quote from the document that supports the verdict, or 'No relevant content found' if the required content is absent. Max 220 characters\"}. " .
            "The reason must not be empty, generic, or just restate the rule. It must give a real analysis. Keep the JSON valid. " .
            "If a rule is not applicable to the document subject, mark it compliant with low confidence and explain why. " .
            "The document text below may contain [Visual elements] sections describing seals, signatures, stamps, or diagrams. " .
            "Treat those descriptions as evidence of the document's formal validity and completeness. " .
            "Consider the vector similarity score as a hint, but base your verdict on the Valenzuela rule and the document content.\n\n" .
            "Document Text:\n{$documentText}\n\n" .
            "Valenzuela City Legal Standards:\n{$rulesText}\n\n" .
            "JSON:";
    }

    private function extractJson($content) {
        $content = $this->cleanAIResponse($content);
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

    /**
     * Strip AI reasoning tokens, think tags, and markdown artifacts from response
     */
    private function cleanAIResponse($content) {
        $content = trim($content);

        // Remove <think>...</think> blocks (including unclosed ones)
        $content = preg_replace('/<think>.*?<\/think>/is', '', $content);
        $content = preg_replace('/<think>.*$/is', '', $content);

        // Remove <reasoning>...</reasoning> blocks
        $content = preg_replace('/<reasoning>.*?<\/reasoning>/is', '', $content);
        $content = preg_replace('/<reasoning>.*$/is', '', $content);

        // Remove lines that look like AI reasoning steps (e.g. "1. Analyze the image:", "**1. Transcribe:")
        $content = preg_replace('/^\s*\d+\.\s*\*{0,2}(Analyze|Transcribe|Extract|Identify|Describe|Note|Check|Look|Read|Scan|Review|Parse|Process|Step)\b.*$/im', '', $content);

        // Remove markdown bold header artifacts like **Header:**, **Body Text:**, etc.
        $content = preg_replace('/^\s*\*{0,2}(Header|Excerpt Note|Ordinance Title|Author Credits|Body Text|Top section|Visual elements?|Additional text)\*{0,2}:?\s*$/im', '', $content);

        return trim($content);
    }
}
