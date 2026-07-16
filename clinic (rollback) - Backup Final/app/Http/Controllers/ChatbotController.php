<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\TriageService;
use App\Services\GeminiService;
use App\Models\ChatHistory;

class ChatbotController extends Controller
{
    /**
     * Handle incoming chat requests with sequential questioning flow.
     *
     * Returns JSON:
     * {
     *   reply: string,
     *   triage: array|null,
     *   emergency: bool,
     *   next_question: string|null,
     *   assessment_complete: bool,
     *   recommendations: array,
     *   retries: int,
     *   language: 'tl'|'en',
     *   conversation_id: string
     * }
     */
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $reqId = (string) Str::uuid();
        $originalMessage = (string) $request->input('message', '');
        $userId = auth()->check() ? auth()->id() : null;

        // Get or create conversation session
        $conversationId = session()->get('current_conversation_id');
        if (!$conversationId) {
            $conversationId = (string) Str::uuid();
            session()->put('current_conversation_id', $conversationId);
        }

        Log::info('Chatbot sequential request start', [
            'req_id' => $reqId,
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'message_preview' => substr($originalMessage, 0, 200)
        ]);

        // Detect language
        $language = $this->detectLanguage($originalMessage);

        // Process message with sequential flow
        $result = $this->processSequentialMessage($originalMessage, $userId, $reqId, $conversationId);

        // Extract sequential result
        $aiReplyText = $result['reply'] ?? '';
        $retryCount = intval($result['retries'] ?? 0);
        $triage = $result['triage'] ?? [];
        $emergency = (bool) ($result['emergency'] ?? false);
        $language = $result['language'] ?? $language;
        $nextQuestion = $result['next_question'] ?? null;
        $assessmentComplete = (bool) ($result['assessment_complete'] ?? false);
        $recommendations = $result['recommendations'] ?? [];

        // Get current conversation from session
        $conversationMessages = session()->get('conversation_messages', []);
        
        // Add user message
        $conversationMessages[] = [
            'role' => 'user',
            'content' => $originalMessage,
            'timestamp' => now()->toISOString()
        ];
        
        // Add AI response
        $conversationMessages[] = [
            'role' => 'assistant', 
            'content' => $aiReplyText,
            'timestamp' => now()->toISOString(),
            'triage' => $triage,
            'emergency' => $emergency,
            'next_question' => $nextQuestion,
            'assessment_complete' => $assessmentComplete,
            'recommendations' => $recommendations
        ];

        // Keep only last 20 messages
        $conversationMessages = array_slice($conversationMessages, -20);
        session()->put('conversation_messages', $conversationMessages);

        // Save conversation to database
        try {
            $this->saveSequentialConversation($conversationId, $userId, $conversationMessages, $language, $retryCount);
        } catch (\Throwable $e) {
            Log::warning('Failed to save ChatHistory', ['req_id' => $reqId, 'error' => $e->getMessage()]);
        }

        // Prepare response
        $responsePayload = [
            'reply' => $aiReplyText,
            'triage' => $triage,
            'emergency' => $emergency,
            'next_question' => $nextQuestion,
            'assessment_complete' => $assessmentComplete,
            'recommendations' => $recommendations,
            'retries' => $retryCount,
            'language' => $language,
            'conversation_id' => $conversationId,
        ];

        Log::info('Chatbot sequential request completed', [
            'req_id' => $reqId,
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'triage_level' => $triage['level'] ?? null,
            'assessment_complete' => $assessmentComplete,
            'has_next_question' => !empty($nextQuestion),
            'has_recommendations' => !empty($recommendations)
        ]);

        return response()->json($responsePayload);
    }

    /**
     * Process message with sequential questioning approach
     */
    private function processSequentialMessage(string $message, $userId = null, string $reqId = null, string $conversationId = null, int $maxRetries = 3): array
    {
        $reqId = $reqId ?? (string) Str::uuid();
        $context = session()->get('chat_context', []);

        // Add user message to context
        $context[] = ['role' => 'user', 'content' => $message];
        $context = array_slice($context, -12); // Keep last 12 messages

        // Detect language
        $language = $this->detectLanguage($message);
        
        // Initialize response variables
        $aiReplyText = '';
        $retryCount = 0;
        $triage = null;
        $emergency = false;
        $nextQuestion = null;
        $assessmentComplete = false;
        $recommendations = [];

        // Call enhanced GeminiService
        try {
            if (class_exists(GeminiService::class)) {
                $result = GeminiService::getResponse($message, $maxRetries, $context, $language);
                
                // Extract sequential fields
                $aiReplyText = (string) ($result['reply'] ?? '');
                $retryCount = intval($result['retries'] ?? 0);
                $triage = $result['triage'] ?? null;
                $emergency = (bool) ($result['emergency'] ?? false);
                $language = $result['language'] ?? $language;
                $nextQuestion = $result['next_question'] ?? null;
                $assessmentComplete = (bool) ($result['assessment_complete'] ?? false);
                $recommendations = $result['recommendations'] ?? [];

                Log::info('GeminiService sequential response processed', [
                    'req_id' => $reqId,
                    'conversation_id' => $conversationId,
                    'has_triage' => !empty($triage),
                    'assessment_complete' => $assessmentComplete,
                    'has_next_question' => !empty($nextQuestion),
                    'has_recommendations' => !empty($recommendations),
                    'language' => $language
                ]);

            } else {
                $aiReplyText = "⚠️ AI backend not configured. Please contact system administrator.";
                $retryCount = 0;
                Log::warning('GeminiService not available');
            }
        } catch (\Throwable $e) {
            Log::error('GeminiService error', ['req_id' => $reqId, 'error' => $e->getMessage()]);
            
            // Sequential fallback
            $fallback = $this->createSequentialFallback($message, $language, $maxRetries);
            $aiReplyText = $fallback['reply'];
            $retryCount = $fallback['retries'];
            $nextQuestion = $fallback['next_question'];
            $assessmentComplete = $fallback['assessment_complete'];
            $recommendations = $fallback['recommendations'];
        }

        // Fallback triage processing if needed
        if (empty($triage)) {
            try {
                $triage = TriageService::process($message);
                if (!is_array($triage)) {
                    $triage = ['level' => 'Assessing', 'confidence' => 0.5, 'reason' => 'Fallback triage processing'];
                }
                $triageLevel = $triage['level'] ?? 'Assessing';
                $emergency = strcasecmp($triageLevel, 'emergency') === 0;
                
                // If emergency detected, complete assessment
                if ($emergency) {
                    $assessmentComplete = true;
                    $nextQuestion = null;
                    $recommendations = $this->getEmergencyRecommendations($language);
                }
                
                Log::info('Fallback triage applied', ['req_id' => $reqId, 'level' => $triageLevel]);
            } catch (\Throwable $e) {
                Log::error('TriageService error', ['req_id' => $reqId, 'error' => $e->getMessage()]);
                $triage = ['level' => 'Assessing', 'confidence' => 0.1, 'reason' => 'Unable to determine triage'];
                $emergency = false;
            }
        }

        // Store assistant reply in context
        $context[] = ['role' => 'assistant', 'content' => $aiReplyText];
        session()->put('chat_context', array_slice($context, -12));

        return [
            'reply' => $aiReplyText,
            'triage' => $triage,
            'emergency' => $emergency,
            'language' => $language,
            'retries' => $retryCount,
            'next_question' => $nextQuestion,
            'assessment_complete' => $assessmentComplete,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Save sequential conversation to database
     */
    private function saveSequentialConversation(string $conversationId, $userId, array $messages, string $language, int $retries): void
    {
        if (!class_exists(ChatHistory::class)) {
            return;
        }

        // Get the last assistant message for summary data
        $lastAssistantMessage = null;
        foreach (array_reverse($messages) as $msg) {
            if ($msg['role'] === 'assistant') {
                $lastAssistantMessage = $msg;
                break;
            }
        }

        // Prepare conversation data
        $conversationData = [
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'messages' => json_encode($messages),
            'language' => $language,
            'retries' => $retries,
            'updated_at' => now(),
        ];

        // Add data from last assistant message
        if ($lastAssistantMessage) {
            $conversationData = array_merge($conversationData, [
                'triage_data' => json_encode($lastAssistantMessage['triage'] ?? []),
                'next_question' => $lastAssistantMessage['next_question'] ?? null,
                'assessment_complete' => $lastAssistantMessage['assessment_complete'] ?? false,
                'recommendations' => json_encode($lastAssistantMessage['recommendations'] ?? []),
                'emergency' => $lastAssistantMessage['emergency'] ?? false,
            ]);
        }

        // Update or create conversation
        $existingChat = ChatHistory::where('conversation_id', $conversationId)->first();
        
        if ($existingChat) {
            $existingChat->update($conversationData);
        } else {
            $conversationData['created_at'] = now();
            
            // Set initial message and ai_response for compatibility
            $firstUserMessage = '';
            $lastAiResponse = '';
            
            foreach ($messages as $msg) {
                if ($msg['role'] === 'user' && empty($firstUserMessage)) {
                    $firstUserMessage = $msg['content'];
                }
                if ($msg['role'] === 'assistant') {
                    $lastAiResponse = $msg['content'];
                }
            }
            
            $conversationData['message'] = $firstUserMessage;
            $conversationData['ai_response'] = $lastAiResponse;
            
            ChatHistory::create($conversationData);
        }
    }

    /**
     * Create sequential fallback response
     */
    private function createSequentialFallback(string $message, string $language, int $retryCount): array
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
            'next_question' => $nextQuestion,
            'assessment_complete' => false,
            'recommendations' => []
        ];
    }

    /**
     * Get emergency recommendations
     */
    private function getEmergencyRecommendations(string $language): array
    {
        if ($language === 'tl') {
            return [
                'Tumawag agad sa emergency hotline o pumunta sa pinakamalapit na hospital',
                'Huwag mag-antay, ito ay emergency situation',
                'Kung may kasama, sabihan sila na dalhin kayo sa hospital'
            ];
        }

        return [
            'Call emergency services immediately or go to the nearest hospital',
            'Do not wait, this is an emergency situation',
            'If someone is with you, have them take you to the hospital'
        ];
    }

    /**
     * Start new conversation - clears session
     */
    public function newConversation(Request $request)
    {
        session()->forget(['current_conversation_id', 'chat_context', 'conversation_messages']);
        
        $newConversationId = (string) Str::uuid();
        session()->put('current_conversation_id', $newConversationId);

        return response()->json([
            'status' => 'success',
            'conversation_id' => $newConversationId,
            'message' => 'New conversation started'
        ]);
    }

    /**
     * Compatibility endpoint: POST /chatbot/send
     */
    public function sendMessage(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $userId = auth()->check() ? auth()->id() : null;
        $originalMessage = (string) $request->input('message', '');
        $reqId = (string) Str::uuid();

        // Get or create conversation session
        $conversationId = session()->get('current_conversation_id');
        if (!$conversationId) {
            $conversationId = (string) Str::uuid();
            session()->put('current_conversation_id', $conversationId);
        }

        // Process the message sequentially
        $result = $this->processSequentialMessage($originalMessage, $userId, $reqId, $conversationId);

        // Update conversation in session
        $conversationMessages = session()->get('conversation_messages', []);
        
        $conversationMessages[] = [
            'role' => 'user',
            'content' => $originalMessage,
            'timestamp' => now()->toISOString()
        ];
        
        $conversationMessages[] = [
            'role' => 'assistant', 
            'content' => $result['reply'] ?? '',
            'timestamp' => now()->toISOString(),
            'triage' => $result['triage'] ?? [],
            'emergency' => $result['emergency'] ?? false,
            'next_question' => $result['next_question'] ?? null,
            'assessment_complete' => $result['assessment_complete'] ?? false,
            'recommendations' => $result['recommendations'] ?? []
        ];

        $conversationMessages = array_slice($conversationMessages, -20);
        session()->put('conversation_messages', $conversationMessages);

        // Save conversation
        try {
            $this->saveSequentialConversation(
                $conversationId, 
                $userId, 
                $conversationMessages, 
                $result['language'] ?? 'en', 
                $result['retries'] ?? 0
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to save conversation (sendMessage)', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'message' => $originalMessage,
            'ai_response' => $result['reply'] ?? '',
            'triage' => $result['triage'] ?? [],
            'retries' => $result['retries'] ?? 0,
            'language' => $result['language'] ?? $this->detectLanguage($originalMessage),
            'next_question' => $result['next_question'] ?? null,
            'assessment_complete' => $result['assessment_complete'] ?? false,
            'recommendations' => $result['recommendations'] ?? [],
            'conversation_id' => $conversationId,
        ]);
    }

    /**
     * GET /chatbot/history - Return conversations with sequential structure
     */
    public function history(Request $request)
    {
        if (!class_exists(ChatHistory::class)) {
            return response()->json([]);
        }

        $userId = auth()->check() ? auth()->id() : null;

        $conversations = ChatHistory::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get(['conversation_id', 'messages', 'triage_data', 'next_question', 'assessment_complete', 'recommendations', 'language', 'created_at', 'updated_at']);

        $formattedConversations = $conversations->map(function($conversation) {
            $messages = json_decode($conversation->messages ?? '[]', true) ?: [];
            
            return [
                'conversation_id' => $conversation->conversation_id,
                'messages' => $messages,
                'last_triage' => json_decode($conversation->triage_data ?? '{}', true),
                'next_question' => $conversation->next_question,
                'assessment_complete' => $conversation->assessment_complete ?? false,
                'recommendations' => json_decode($conversation->recommendations ?? '[]', true),
                'language' => $conversation->language ?? 'en',
                'created_at' => $conversation->created_at,
                'updated_at' => $conversation->updated_at,
            ];
        });

        return response()->json($formattedConversations);
    }

    /**
     * GET /chatbot/conversation/{id} - Get specific conversation
     */
    public function getConversation(Request $request, string $conversationId)
    {
        if (!class_exists(ChatHistory::class)) {
            return response()->json(['error' => 'History not available'], 404);
        }

        $userId = auth()->check() ? auth()->id() : null;

        $conversation = ChatHistory::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $messages = json_decode($conversation->messages ?? '[]', true) ?: [];

        return response()->json([
            'conversation_id' => $conversation->conversation_id,
            'messages' => $messages,
            'triage_data' => json_decode($conversation->triage_data ?? '{}', true),
            'next_question' => $conversation->next_question,
            'assessment_complete' => $conversation->assessment_complete ?? false,
            'recommendations' => json_decode($conversation->recommendations ?? '[]', true),
            'language' => $conversation->language ?? 'en',
            'created_at' => $conversation->created_at,
            'updated_at' => $conversation->updated_at,
        ]);
    }

    /**
     * Enhanced language detection
     */
    private function detectLanguage(string $text): string
    {
        $tagalogWords = [
            'ng', 'sa', 'ako', 'ikaw', 'lagnat', 'ubo', 'sakit', 'masakit', 'puso', 'dugo', 
            'kumusta', 'po', 'opo', 'salamat', 'kailangan', 'kami', 'namin',
            'mga', 'ang', 'nang', 'para', 'hindi', 'oo', 'paano', 'saan', 'bakit'
        ];
        
        $lc = strtolower($text);
        $tagalogCount = 0;
        
        foreach ($tagalogWords as $word) {
            if (strpos($lc, $word) !== false) {
                $tagalogCount++;
            }
        }
        
        return $tagalogCount >= 2 ? 'tl' : 'en';
    }
}