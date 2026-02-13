<?php

namespace App\Services;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Enhanced medical triage system prompt for sequential questioning
     */
    private static function getSystemPrompt(string $language = 'en'): string
    {
        $basePrompt = $language === 'tl' ? 
            "Ikaw ay isang AI Triage Assistant para sa BESTLINK COLLEGE CLINIC. " .
            "Ang inyong tungkulin ay mag-assess ng mga sintomas ng mga estudyante at magbigay ng medical guidance. " .
            "Gumamit ng mga sumusunod na triage levels: Emergency, Urgent, Non-Urgent. " .
            "IMPORTANTE: Magtanong ng ISANG follow-up question lang sa bawat response hanggang sa makakuha kayo ng sapat na impormasyon para sa final assessment. " .
            "Kapag satisfied na kayo sa impormasyon, magbigay ng final triage assessment at recommendations. " .
            "Magbigay ng JSON response na may mga sumusunod na keys: reply, triage, emergency, language, next_question, assessment_complete, recommendations."
            :
            "You are an AI Triage Assistant for BESTLINK COLLEGE CLINIC. " .
            "Your role is to assess student symptoms and provide medical guidance. " .
            "Use these triage levels: Emergency, Urgent, Non-Urgent. " .
            "IMPORTANT: Ask only ONE follow-up question per response until you have sufficient information for final assessment. " .
            "When satisfied with information, provide final triage assessment and recommendations. " .
            "Provide JSON response with these keys: reply, triage, emergency, language, next_question, assessment_complete, recommendations.";

        return $basePrompt . "\n\n" . self::getDetailedInstructions($language);
    }

    private static function getDetailedInstructions(string $language): string
    {
        if ($language === 'tl') {
            return "GABAY SA PAG-ASSESS:\n" .
                "1. EMERGENCY: Chest pain, difficulty breathing, severe bleeding, loss of consciousness, severe allergic reactions\n" .
                "2. URGENT: High fever (>39°C), severe pain, persistent vomiting, signs of infection\n" .
                "3. NON-URGENT: Mild symptoms, general wellness questions, follow-up care\n\n" .
                "FOLLOW-UP QUESTION STRATEGY:\n" .
                "- Una, alamin ang basic symptoms at duration\n" .
                "- Pangalawa, tanungin ang severity/intensity\n" .
                "- Pangatlo, alamin ang associated symptoms\n" .
                "- Pang-apat, tanungin ang medical history kung kailangan\n" .
                "- Kapag may emergency symptoms, agad na i-assess bilang Emergency\n" .
                "- Sa bawat response, magbigay ng ISANG tanong lang sa 'next_question' field\n" .
                "- Kapag kompleto na ang assessment, i-set ang 'assessment_complete' to true at magbigay ng recommendations\n\n" .
                "JSON FORMAT EXAMPLE (ONGOING ASSESSMENT):\n" .
                "{\n" .
                "  \"reply\": \"Naiintindihan ko na may sakit kayo sa ulo. Kailangan ko ng mas detalyadong impormasyon.\",\n" .
                "  \"triage\": {\"level\": \"Assessing\", \"confidence\": 0.5, \"reason\": \"Gathering information\"},\n" .
                "  \"emergency\": false,\n" .
                "  \"language\": \"tl\",\n" .
                "  \"next_question\": \"Gaano katagal na ang sakit sa ulo ninyo?\",\n" .
                "  \"assessment_complete\": false,\n" .
                "  \"recommendations\": []\n" .
                "}\n\n" .
                "JSON FORMAT EXAMPLE (FINAL ASSESSMENT):\n" .
                "{\n" .
                "  \"reply\": \"Base sa inyong mga sintomas, ito ay Non-Urgent na kondisyon.\",\n" .
                "  \"triage\": {\"level\": \"Non-Urgent\", \"confidence\": 0.8, \"reason\": \"Mild headache, no red flags\"},\n" .
                "  \"emergency\": false,\n" .
                "  \"language\": \"tl\",\n" .
                "  \"next_question\": null,\n" .
                "  \"assessment_complete\": true,\n" .
                "  \"recommendations\": [\n" .
                "    \"Uminom ng maraming tubig at magpahinga\",\n" .
                "    \"Pwedeng uminom ng over-the-counter pain reliever tulad ng paracetamol\",\n" .
                "    \"Kung tumatagal pa ang sakit ng 2-3 araw, bisitahin ang clinic\"\n" .
                "  ]\n" .
                "}";
        }

        return "ASSESSMENT GUIDELINES:\n" .
            "1. EMERGENCY: Chest pain, difficulty breathing, severe bleeding, loss of consciousness, severe allergic reactions\n" .
            "2. URGENT: High fever (>39°C), severe pain, persistent vomiting, signs of infection\n" .
            "3. NON-URGENT: Mild symptoms, general wellness questions, follow-up care\n\n" .
            "FOLLOW-UP QUESTION STRATEGY:\n" .
            "- First, determine basic symptoms and duration\n" .
            "- Second, assess severity/intensity\n" .
            "- Third, check for associated symptoms\n" .
            "- Fourth, inquire about medical history if needed\n" .
            "- If emergency symptoms present, immediately assess as Emergency\n" .
            "- Each response should have only ONE question in 'next_question' field\n" .
            "- When assessment is complete, set 'assessment_complete' to true and provide recommendations\n\n" .
            "JSON FORMAT EXAMPLE (ONGOING ASSESSMENT):\n" .
            "{\n" .
            "  \"reply\": \"I understand you have a headache. I need more detailed information to help you properly.\",\n" .
            "  \"triage\": {\"level\": \"Assessing\", \"confidence\": 0.5, \"reason\": \"Gathering information\"},\n" .
            "  \"emergency\": false,\n" .
            "  \"language\": \"en\",\n" .
            "  \"next_question\": \"How long have you been experiencing this headache?\",\n" .
            "  \"assessment_complete\": false,\n" .
            "  \"recommendations\": []\n" .
            "}\n\n" .
            "JSON FORMAT EXAMPLE (FINAL ASSESSMENT):\n" .
            "{\n" .
            "  \"reply\": \"Based on your symptoms, this appears to be a Non-Urgent condition.\",\n" .
            "  \"triage\": {\"level\": \"Non-Urgent\", \"confidence\": 0.8, \"reason\": \"Mild headache with no red flags\"},\n" .
            "  \"emergency\": false,\n" .
            "  \"language\": \"en\",\n" .
            "  \"next_question\": null,\n" .
            "  \"assessment_complete\": true,\n" .
            "  \"recommendations\": [\n" .
            "    \"Stay hydrated and get adequate rest\",\n" .
            "    \"Consider taking over-the-counter pain relievers like acetaminophen\",\n" .
            "    \"If headache persists for more than 2-3 days, visit the clinic\"\n" .
            "  ]\n" .
            "}";
    }

    /**
     * Build conversation context for sequential assessment
     */
    private static function buildMedicalContext(array $context, string $language = 'en'): string
    {
        $systemPrompt = self::getSystemPrompt($language);
        $conversation = [$systemPrompt];

        // Add conversation history
        foreach (array_slice($context, -10) as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';
            
            if ($role === 'user') {
                $conversation[] = "PATIENT: " . $content;
            } else {
                $conversation[] = "ASSISTANT: " . $content;
            }
        }

        // Add assessment strategy
        $strategyPrompt = $language === 'tl' ?
            "\nSTRATEGY SA KASALUKUYANG ASSESSMENT:\n" .
            "- MANDATORY: JSON response lang, walang ibang text\n" .
            "- Kung kulang pa ang info, magtanong ng ISANG specific question\n" .
            "- Kung may emergency signs, agad na mag-alert\n" .
            "- Kung sapat na ang info, mag-provide ng final assessment at recommendations\n" .
            "- next_question ay dapat specific at relevant sa symptoms"
            :
            "\nCURRENT ASSESSMENT STRATEGY:\n" .
            "- MANDATORY: JSON response only, no other text\n" .
            "- If info insufficient, ask ONE specific question\n" .
            "- If emergency signs present, alert immediately\n" .
            "- If sufficient info gathered, provide final assessment and recommendations\n" .
            "- next_question should be specific and relevant to symptoms";

        $conversation[] = $strategyPrompt;

        return implode("\n\n", $conversation);
    }

    /**
     * Main response method with sequential questioning
     */
    public static function getResponse(string $prompt, int $maxAttempts = 3, array $context = [], string $language = 'en'): array
    {
        if (!$language || $language === 'auto') {
            $language = self::detectLanguageFromContext($context, $prompt);
        }

        $contextualPrompt = !empty($context) ? 
            self::buildMedicalContext($context, $language) . "\n\nLATEST PATIENT MESSAGE: " . $prompt :
            self::getSystemPrompt($language) . "\n\nPATIENT MESSAGE: " . $prompt;

        Log::info('GeminiService: Processing sequential medical query', [
            'language' => $language,
            'context_messages' => count($context),
            'prompt_length' => strlen($prompt)
        ]);

        $client = new Client(env('GEMINI_API_KEY'));
        $retryCount = 0;

        try {
            $rawResponse = self::callModelWithRetries(function () use ($client, $contextualPrompt) {
                return $client
                    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
                    ->generateContent(new TextPart($contextualPrompt));
            }, $retryCount, $maxAttempts);

            $text = self::extractTextFromResponse($rawResponse);
            $structuredResponse = self::parseAndValidateSequentialResponse($text, $language);
            
            return [
                'reply' => $structuredResponse['reply'],
                'retries' => $retryCount,
                'triage' => $structuredResponse['triage'],
                'emergency' => $structuredResponse['emergency'],
                'language' => $structuredResponse['language'],
                'next_question' => $structuredResponse['next_question'],
                'assessment_complete' => $structuredResponse['assessment_complete'],
                'recommendations' => $structuredResponse['recommendations'],
                'raw_response' => $text
            ];

        } catch (\Throwable $e) {
            Log::error('GeminiService failed completely', [
                'error' => $e->getMessage(),
                'retries' => $retryCount
            ]);

            return self::createSequentialFallbackResponse($prompt, $language, $retryCount);
        }
    }

    /**
     * Parse and validate sequential response
     */
    private static function parseAndValidateSequentialResponse(string $text, string $language): array
    {
        $jsonData = self::extractJsonFromText($text);
        
        if (!$jsonData) {
            return self::createStructuredFromPlainTextSequential($text, $language);
        }

        return self::validateSequentialJsonResponse($jsonData, $language);
    }

    /**
     * Validate sequential JSON response
     */
    private static function validateSequentialJsonResponse(array $data, string $language): array
    {
        $reply = $data['reply'] ?? '';
        $triage = $data['triage'] ?? [];
        $emergency = $data['emergency'] ?? false;
        $nextQuestion = $data['next_question'] ?? null;
        $assessmentComplete = $data['assessment_complete'] ?? false;
        $recommendations = $data['recommendations'] ?? [];

        // Validate triage level
        $validLevels = ['Emergency', 'Urgent', 'Non-Urgent', 'Assessing'];
        $triageLevel = $triage['level'] ?? 'Assessing';
        if (!in_array($triageLevel, $validLevels)) {
            $triageLevel = 'Assessing';
        }

        // Auto-set emergency flag for Emergency triage
        if ($triageLevel === 'Emergency') {
            $emergency = true;
            $assessmentComplete = true; // Emergency is immediately complete
        }

        // Ensure we have next question if assessment not complete
        if (!$assessmentComplete && empty($nextQuestion)) {
            $nextQuestion = self::getDefaultNextQuestion($language);
        }

        // Ensure we have recommendations if assessment complete
        if ($assessmentComplete && empty($recommendations)) {
            $recommendations = self::getDefaultRecommendations($triageLevel, $language);
        }

        // Clean next question
        if ($nextQuestion && !str_ends_with($nextQuestion, '?')) {
            $nextQuestion .= '?';
        }

        // Ensure triage has required fields
        $triage = array_merge([
            'level' => $triageLevel,
            'confidence' => $assessmentComplete ? 0.8 : 0.5,
            'reason' => $assessmentComplete ? 'Assessment completed based on symptoms' : 'Gathering information for assessment'
        ], $triage);

        return [
            'reply' => $reply,
            'triage' => $triage,
            'emergency' => $emergency,
            'language' => $language,
            'next_question' => $nextQuestion,
            'assessment_complete' => $assessmentComplete,
            'recommendations' => is_array($recommendations) ? $recommendations : []
        ];
    }

    /**
     * Create structured response from plain text for sequential flow
     */
    private static function createStructuredFromPlainTextSequential(string $text, string $language): array
    {
        // Check for emergency keywords
        $emergencyKeywords = ['chest pain', 'can\'t breathe', 'unconscious', 'severe bleeding', 'allergic reaction'];
        $lowerText = strtolower($text);
        $emergency = false;
        
        foreach ($emergencyKeywords as $keyword) {
            if (strpos($lowerText, $keyword) !== false) {
                $emergency = true;
                break;
            }
        }

        return [
            'reply' => $text,
            'triage' => [
                'level' => $emergency ? 'Emergency' : 'Assessing',
                'confidence' => $emergency ? 0.9 : 0.3,
                'reason' => $emergency ? 'Emergency keywords detected' : 'Basic text analysis'
            ],
            'emergency' => $emergency,
            'language' => $language,
            'next_question' => $emergency ? null : self::getDefaultNextQuestion($language),
            'assessment_complete' => $emergency,
            'recommendations' => $emergency ? self::getEmergencyRecommendations($language) : []
        ];
    }

    /**
     * Get default next question when AI doesn't provide one
     */
    private static function getDefaultNextQuestion(string $language): string
    {
        return $language === 'tl' 
            ? "Maari mo bang ilarawan nang mas detalyado ang inyong nararamdaman?"
            : "Can you describe your symptoms in more detail?";
    }

    /**
     * Get default recommendations based on triage level
     */
    private static function getDefaultRecommendations(string $triageLevel, string $language): array
    {
        if ($language === 'tl') {
            $recommendations = [
                'Emergency' => [
                    'Tumawag agad sa emergency hotline o pumunta sa pinakamalapit na hospital',
                    'Huwag mag-antay, ito ay emergency situation',
                    'Kung may kasama, sabihan sila na dalhin kayo sa hospital'
                ],
                'Urgent' => [
                    'Bisitahin ang clinic ngayon din o sa loob ng 24 oras',
                    'Bantayan ang mga sintomas kung lumala pa',
                    'Uminom ng maraming tubig at magpahinga'
                ],
                'Non-Urgent' => [
                    'Magpahinga at uminom ng maraming tubig',
                    'Bantayan ang mga sintomas sa susunod na araw',
                    'Kung hindi guminhawa sa loob ng 2-3 araw, bisitahin ang clinic'
                ]
            ];
        } else {
            $recommendations = [
                'Emergency' => [
                    'Call emergency services immediately or go to the nearest hospital',
                    'Do not wait, this is an emergency situation',
                    'If someone is with you, have them take you to the hospital'
                ],
                'Urgent' => [
                    'Visit the clinic today or within 24 hours',
                    'Monitor symptoms for any worsening',
                    'Stay hydrated and get rest'
                ],
                'Non-Urgent' => [
                    'Get adequate rest and stay hydrated',
                    'Monitor symptoms over the next day',
                    'If no improvement in 2-3 days, visit the clinic'
                ]
            ];
        }

        return $recommendations[$triageLevel] ?? $recommendations['Non-Urgent'];
    }

    /**
     * Get emergency recommendations
     */
    private static function getEmergencyRecommendations(string $language): array
    {
        return self::getDefaultRecommendations('Emergency', $language);
    }

    /**
     * Create sequential fallback response
     */
    private static function createSequentialFallbackResponse(string $message, string $language, int $retryCount): array
    {
        if ($language === 'tl') {
            $reply = "Pasensya na, may problema sa aming AI system ngayon. Para sa inyong kaligtasan, " .
                    "inirerekomenda namin na direktang makipag-ugnayan sa clinic.";
            $nextQuestion = "Kailangan ba ninyo ng emergency assistance ngayon?";
        } else {
            $reply = "I apologize, but our AI system is experiencing issues. For your safety, " .
                    "I recommend contacting the clinic directly.";
            $nextQuestion = "Do you need emergency assistance right now?";
        }

        return [
            'reply' => $reply,
            'retries' => $retryCount,
            'triage' => [
                'level' => 'Non-Urgent',
                'confidence' => 0.1,
                'reason' => 'System error - manual assessment required'
            ],
            'emergency' => false,
            'language' => $language,
            'next_question' => $nextQuestion,
            'assessment_complete' => false,
            'recommendations' => []
        ];
    }

    // Keep existing helper methods
    private static function detectLanguageFromContext(array $context, string $currentMessage): string
    {
        $allText = $currentMessage;
        foreach (array_slice($context, -3) as $msg) {
            $allText .= ' ' . ($msg['content'] ?? '');
        }

        $tagalogIndicators = [
            'ng', 'sa', 'ako', 'ikaw', 'po', 'opo', 'salamat', 'kumusta', 
            'masakit', 'lagnat', 'ubo', 'hindi', 'oo', 'mga', 'ang', 
            'nang', 'para', 'kailangan', 'gaano', 'saan', 'bakit'
        ];
        
        $lowerText = strtolower($allText);
        $tagalogCount = 0;
        foreach ($tagalogIndicators as $indicator) {
            if (strpos($lowerText, $indicator) !== false) {
                $tagalogCount++;
            }
        }

        return $tagalogCount >= 2 ? 'tl' : 'en';
    }

    private static function extractTextFromResponse($response): string
    {
        try {
            if (is_string($response)) {
                return $response;
            } elseif (is_object($response) && method_exists($response, 'text')) {
                return $response->text();
            } else {
                return (string) $response;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to extract text from Gemini response: ' . $e->getMessage());
            return '';
        }
    }

    private static function extractJsonFromText(string $text): ?array
    {
        $cleaned = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $text);
        $cleaned = preg_replace('/```\s*(.*?)\s*```/s', '$1', $cleaned);
        
        $firstBrace = strpos($cleaned, '{');
        if ($firstBrace !== false) {
            $cleaned = substr($cleaned, $firstBrace);
        }
        
        $lastBrace = strrpos($cleaned, '}');
        if ($lastBrace !== false) {
            $cleaned = substr($cleaned, 0, $lastBrace + 1);
        }

        try {
            $decoded = json_decode($cleaned, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : null;
        } catch (\JsonException $e) {
            $fixed = self::fixCommonJsonIssues($cleaned);
            try {
                $decoded = json_decode($fixed, true, 512, JSON_THROW_ON_ERROR);
                return is_array($decoded) ? $decoded : null;
            } catch (\JsonException $e2) {
                return null;
            }
        }
    }

    private static function fixCommonJsonIssues(string $json): string
    {
        $json = preg_replace('/,\s*}/', '}', $json);
        $json = preg_replace('/,\s*]/', ']', $json);
        $json = str_replace("'", '"', $json);
        return $json;
    }

    protected static function callModelWithRetries(callable $callable, ?int &$retryCount = null, int $maxAttempts = 3, int $baseDelayMs = 300): mixed
    {
        $attempt = 1;
        $lastException = null;

        if ($retryCount === null) {
            $retryCount = 0;
        }

        while ($attempt <= $maxAttempts) {
            try {
                return $callable();
            } catch (\Exception $e) {
                $lastException = $e;

                if ($attempt >= $maxAttempts) {
                    break;
                }

                $retryCount++;
                $backoffMs = $baseDelayMs * (2 ** ($attempt - 1));
                $jitterMs = random_int(0, 100);
                $totalDelayMs = $backoffMs + $jitterMs;

                usleep($totalDelayMs * 1000);
                $attempt++;
            }
        }
        
        throw $lastException ?: new \RuntimeException('Medical triage API completely unavailable');
    }
}