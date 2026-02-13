<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'ai_response',
        'triage_data',
        'follow_up_questions',
        'needs_more_info',
        'retries',
        'language',
        'emergency',
        'confidence_score',
        'session_id',
    ];

    protected $casts = [
        'triage_data' => 'array',
        'follow_up_questions' => 'array', 
        'needs_more_info' => 'boolean',
        'emergency' => 'boolean',
        'retries' => 'integer',
        'confidence_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'needs_more_info' => false,
        'emergency' => false,
        'retries' => 0,
        'language' => 'en',
    ];

    /**
     * Get the user that owns the chat history
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by language
     */
    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope for emergency cases
     */
    public function scopeEmergency($query)
    {
        return $query->where('emergency', true);
    }

    /**
     * Scope for cases needing more information
     */
    public function scopeNeedsMoreInfo($query)
    {
        return $query->where('needs_more_info', true);
    }

    /**
     * Scope for recent conversations
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Get triage level from triage_data
     */
    public function getTriageLevelAttribute(): ?string
    {
        return $this->triage_data['level'] ?? null;
    }

    /**
     * Get triage confidence from triage_data
     */
    public function getTriageConfidenceAttribute(): ?float
    {
        return $this->triage_data['confidence'] ?? null;
    }

    /**
     * Check if this is a high priority case
     */
    public function isHighPriority(): bool
    {
        $level = $this->triage_level;
        return in_array(strtolower($level ?? ''), ['emergency', 'urgent', 'high']);
    }

    /**
     * Get formatted follow-up questions for display
     */
    public function getFormattedFollowUpQuestions(): array
    {
        if (empty($this->follow_up_questions)) {
            return [];
        }

        return array_map(function ($question) {
            return [
                'text' => $question,
                'id' => md5($question),
            ];
        }, $this->follow_up_questions);
    }

    /**
     * Static method to get conversation context for a user
     */
    public static function getConversationContext(?int $userId, int $limit = 10): array
    {
        $query = static::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        $messages = $query->get(['message', 'ai_response', 'created_at']);
        
        $context = [];
        foreach ($messages->reverse() as $chat) {
            $context[] = ['role' => 'user', 'content' => $chat->message];
            if ($chat->ai_response) {
                $context[] = ['role' => 'assistant', 'content' => $chat->ai_response];
            }
        }

        return $context;
    }

    /**
     * Analytics helper: Get triage distribution
     */
    public static function getTriageDistribution(int $days = 7): array
    {
        $data = static::where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('triage_data')
            ->get()
            ->groupBy('triage_level')
            ->map->count();

        return [
            'Emergency' => $data['Emergency'] ?? 0,
            'Urgent' => $data['Urgent'] ?? 0, 
            'Non-Urgent' => $data['Non-Urgent'] ?? 0,
            'HIGH' => $data['HIGH'] ?? 0,
            'MEDIUM' => $data['MEDIUM'] ?? 0,
            'LOW' => $data['LOW'] ?? 0,
        ];
    }

    /**
     * Analytics helper: Get language usage
     */
    public static function getLanguageStats(int $days = 7): array
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->pluck('count', 'language')
            ->toArray();
    }
}