<?php
/**
 * Chatbot Controller
 * Handles logic for the AI Chatbot Assistant
 */

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class ChatbotController {
    private $db;
    private $logger;
    private $apiKey;

    public function __construct() {
        $this->db = getDatabase();
        $this->logger = new Logger($this->db);
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    }

    /**
     * Process a chat message
     */
    public function ask($message, $history = []) {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Chatbot is not configured. (Missing API Key)'];
        }

        $context = $this->getSystemContext();
        
        // Use v1beta and the specific model requested
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent";

        // Instruction for the system
        $systemInstruction = "You are the LRMS Assistant (Gemini 3 Flash). Guide users on how to use the Legislative Records Management System. Use the context below to answer questions professionaly.\n\nContext:\n" . substr($context, 0, 3000);

        // Construct the combined prompt
        $fullPrompt = $systemInstruction . "\n\n";
        
        if (!empty($history)) {
            $fullPrompt .= "Conversation History:\n";
            foreach ($history as $chat) {
                $role = (isset($chat['role']) && ($chat['role'] === 'user' || $chat['role'] === 'User')) ? 'User' : 'Assistant';
                $text = $chat['text'] ?? '';
                $fullPrompt .= "$role: $text\n";
            }
        }
        
        $fullPrompt .= "\nNew Question: " . (string)$message;

        // Ensure UTF-8 for json_encode
        $fullPrompt = mb_convert_encoding($fullPrompt, 'UTF-8', 'UTF-8');

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $fullPrompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 1024
            ]
        ];

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            return ['success' => false, 'error' => 'Failed to encode JSON payload: ' . json_last_error_msg()];
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . trim($this->apiKey)
        ]);
        
        // Critical for XAMPP SSL verification issues
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => "cURL Network Error: " . $curlError];
        }

        if ($httpCode !== 200) {
            $errDetails = json_decode($response, true);
            $msg = $errDetails['error']['message'] ?? 'Unknown API Error';
            return [
                'success' => false, 
                'error' => "API Error ($httpCode): $msg",
                'details' => $errDetails
            ];
        }

        $result = json_decode($response, true);
        $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'AI response was empty.';

        return [
            'success' => true,
            'answer' => $answer
        ];
    }

    /**
     * Get system context from documentation files
     */
    private function getSystemContext() {
        $base = __DIR__ . '/../../../docus/';
        $relevantFiles = [
            'IMPLEMENTATION_SUMMARY.md',
            'STRUCTURE.md',
            'CORE_FEATURES_STATUS.md',
            'USER_PROFILE_FEATURES_REPORT.md'
        ];

        $context = "";
        foreach ($relevantFiles as $file) {
            $path = $base . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $content = preg_replace('/\[Lines.*?\]/', '', $content);
                $context .= "### " . str_replace('.md', '', $file) . "\n" . substr($content, 0, 1500) . "\n\n";
            }
        }

        return $context;
    }
}
