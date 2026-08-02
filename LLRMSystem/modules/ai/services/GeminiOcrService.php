<?php
/**
 * Gemini OCR Service
 *
 * Extracts text from images and document pages using a Google Gemini 2
 * generative vision model (e.g., gemini-2.0-flash-001). Designed to be
 * called before the local Tesseract/Groq pipeline so Tesseract and Groq
 * can act as enhancement/fallback layers.
 */

class GeminiOcrService {
    private $apiKey;
    private $model;
    private $prompt;
    private $lastError = null;
    private $lastHttpCode = null;
    private $lastRequestTime = 0;

    public function __construct() {
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        $this->model = defined('OCR_GEMINI_MODEL') ? OCR_GEMINI_MODEL : 'gemini-2.0-flash-001';
        $this->prompt = defined('OCR_GEMINI_PROMPT') ? OCR_GEMINI_PROMPT
            : 'Extract all readable text and any visible signatures from this document. Return only plain text, with no descriptions of images or other visual content.';
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function getLastHttpCode() {
        return $this->lastHttpCode;
    }

    /**
     * Enforce a minimum gap between Gemini API calls to avoid rate limits.
     */
    private function throttle() {
        $gapMs = defined('OCR_GEMINI_DELAY_MS') ? (int)OCR_GEMINI_DELAY_MS : 1000;
        if ($gapMs <= 0 || $this->lastRequestTime <= 0) {
            return;
        }
        $elapsedUs = (microtime(true) - $this->lastRequestTime) * 1000000;
        $gapUs = $gapMs * 1000;
        if ($elapsedUs < $gapUs) {
            usleep((int)($gapUs - $elapsedUs));
        }
    }

    /**
     * Extract text from an image or PDF page using Gemini 2 vision.
     *
     * @param string $filePath Path to the image or PDF page.
     * @return string|null Extracted text, or null on failure.
     */
    public function extractText($filePath) {
        $this->lastError = null;
        $this->lastHttpCode = null;

        if (empty($this->apiKey)) {
            $this->lastError = 'GEMINI_API_KEY is missing';
            return null;
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->lastError = 'File not found or not readable: ' . $filePath;
            return null;
        }

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
        if (empty($base64)) {
            $this->lastError = 'Failed to read file for Gemini OCR';
            return null;
        }

        $this->throttle();
        $this->lastRequestTime = microtime(true);

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data' => $base64
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 2048,
                'topP' => 0.9
            ]
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->lastHttpCode = $httpCode;

        if ($err) {
            $this->lastError = 'Curl: ' . $err;
            error_log('GeminiOcrService Curl Error: ' . $err);
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->lastError = 'HTTP ' . $httpCode . ': ' . $response;
            error_log('GeminiOcrService HTTP Error: ' . $httpCode . ' ' . $response);
            return null;
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($text)) {
            $this->lastError = 'No text returned. Response: ' . json_encode($result);
            error_log('GeminiOcrService Error: ' . json_encode($result));
            return null;
        }

        return trim($text);
    }
}
