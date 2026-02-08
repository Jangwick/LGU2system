<?php
/**
 * Chatbot Controller
 * Handles logic for the AI Chatbot Assistant
 */

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
        
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $contents = [];
        
        // Add System Context as a "model instruction" or part of the first message
        $contents[] = [
            "role" => "user",
            "parts" => [["text" => "System Instructions: Use the following information about the Legislative Records Management System (LRMS) to assist the user. If you are asked about something you don't know, suggest they contact the IT administrator.\n\nContext:\n" . $context]]
        ];
        $contents[] = [
            "role" => "model",
            "parts" => [["text" => "I understand. I am the LRMS Assistant, ready to help you navigate and use the system. How can I assist you today?"]]
        ];

        // Add history
        foreach ($history as $chat) {
            $contents[] = [
                "role" => $chat['role'] === 'user' ? 'user' : 'model',
                "parts" => [["text" => $chat['text']]]
            ];
        }

        // Add current message
        $contents[] = [
            "role" => "user",
            "parts" => [["text" => $message]]
        ];

        $payload = [
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.7,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 1024,
            ]
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'API Error (' . $httpCode . ')', 'details' => json_decode($response, true)];
        }

        $result = json_decode($response, true);
        $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'I could not generate a response.';

        return [
            'success' => true,
            'answer' => $answer
        ];
    }

    /**
     * Get system context from documentation files
     */
    private function getSystemContext() {
        $docusDir = __DIR__ . '/../../../docus/';
        $relevantFiles = [
            'IMPLEMENTATION_SUMMARY.md',
            'STRUCTURE.md',
            'CORE_FEATURES_STATUS.md',
            'USER_PROFILE_FEATURES_REPORT.md'
        ];

        $context = "";
        foreach ($relevantFiles as $file) {
            $path = $docusDir . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                // Basic cleanup to save tokens
                $content = preg_replace('/\[Lines.*?\]/', '', $content);
                $context .= "### " . str_replace('.md', '', $file) . "\n" . substr($content, 0, 1500) . "\n\n";
            }
        }

        return $context;
    }
}
