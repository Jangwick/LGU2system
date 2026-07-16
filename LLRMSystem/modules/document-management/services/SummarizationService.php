<?php
/**
 * Summarization Service
 * 
 * Local extractive summarization algorithm — pure PHP, no external API.
 * Uses a TextRank-inspired approach to identify the most important
 * sentences from OCR-extracted text and present them as key points.
 */

class SummarizationService {
    private $stopWords;
    private $minSentenceLength;
    private $maxSentenceLength;

    public function __construct() {
        $this->stopWords = $this->getStopWords();
        $this->minSentenceLength = 20;
        $this->maxSentenceLength = 500;
    }

    /**
     * Generate key points from text
     * Returns an array of key point strings (bullet points)
     */
    public function generateKeyPoints($text, $count = 7) {
        $text = $this->cleanText($text);

        if (empty($text) || strlen(trim($text)) < 50) {
            return [];
        }

        // Split into sentences
        $sentences = $this->splitSentences($text);

        if (count($sentences) <= $count) {
            // If we have fewer sentences than requested count, return all
            return $this->formatAsBulletPoints($sentences);
        }

        // Calculate word frequencies
        $wordFreq = $this->calculateWordFrequencies($text);

        // Score each sentence
        $scored = $this->scoreSentences($sentences, $wordFreq);

        // Sort by score descending and pick top N
        arsort($scored);

        // Get top sentences but maintain original order
        $topIndexes = array_slice(array_keys($scored), 0, $count);
        sort($topIndexes);

        $keyPoints = [];
        foreach ($topIndexes as $idx) {
            $keyPoints[] = $sentences[$idx];
        }

        return $this->formatAsBulletPoints($keyPoints);
    }

    /**
     * Generate key points and return as a single string (for DB storage)
     */
    public function generateKeyPointsString($text, $count = 7) {
        $points = $this->generateKeyPoints($text, $count);
        return implode("\n", $points);
    }

    /**
     * Extract top keywords from text
     */
    public function extractKeywords($text, $count = 10) {
        $text = $this->cleanText($text);
        $wordFreq = $this->calculateWordFrequencies($text);
        arsort($wordFreq);
        return array_slice(array_keys($wordFreq), 0, $count);
    }

    /**
     * Clean text — remove OCR artifacts, normalize whitespace
     */
    private function cleanText($text) {
        if ($text === null || !is_string($text)) {
            return '';
        }
        // Remove page break markers
        $text = preg_replace('/--- Page Break ---/', "\n", $text);
        // Remove OCR markers
        $text = preg_replace('/\[OCR\]/', '', $text);
        $text = preg_replace('/\[Page OCR failed:.*?\]/', '', $text);
        $text = preg_replace('/\[Legacy.*?\]/', '', $text);
        // Normalize whitespace
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        return trim($text);
    }

    /**
     * Split text into sentences
     */
    private function splitSentences($text) {
        // Normalize line breaks into spaces for sentence splitting
        $normalized = preg_replace('/\n+/', ' ', $text);

        // Split on sentence-ending punctuation followed by space or end
        $sentences = preg_split('/(?<=[.!?])\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        // Filter and clean sentences
        $result = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            // Skip too-short or too-long sentences
            $len = strlen($sentence);
            if ($len < $this->minSentenceLength) {
                continue;
            }
            if ($len > $this->maxSentenceLength) {
                // Try to split very long sentences on semicolons or commas
                $parts = preg_split('/[;]/', $sentence);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (strlen($part) >= $this->minSentenceLength && strlen($part) <= $this->maxSentenceLength) {
                        $result[] = $part;
                    }
                }
                continue;
            }

            // Skip sentences that are mostly numbers or special characters
            $alphaCount = preg_match_all('/[a-zA-Z]/', $sentence);
            if ($alphaCount < 10) {
                continue;
            }

            // Skip sentences that look like headers (all caps, short)
            if (strtoupper($sentence) === $sentence && strlen($sentence) < 60) {
                continue;
            }

            $result[] = $sentence;
        }

        return $result;
    }

    /**
     * Calculate word frequencies (excluding stop words)
     */
    private function calculateWordFrequencies($text) {
        // Extract words
        $words = str_word_count(strtolower($text), 1);

        $freq = [];
        foreach ($words as $word) {
            $word = trim($word, ".,;:!?\"'()[]{}");

            // Skip stop words and very short words
            if (in_array($word, $this->stopWords) || strlen($word) < 3) {
                continue;
            }

            if (!isset($freq[$word])) {
                $freq[$word] = 0;
            }
            $freq[$word]++;
        }

        // Normalize frequencies (divide by max frequency)
        $maxFreq = !empty($freq) ? max($freq) : 1;
        foreach ($freq as &$f) {
            $f = $f / $maxFreq;
        }

        return $freq;
    }

    /**
     * Score sentences based on word frequency, position, and length
     */
    private function scoreSentences($sentences, $wordFreq) {
        $totalSentences = count($sentences);
        $scores = [];

        foreach ($sentences as $index => $sentence) {
            $words = str_word_count(strtolower($sentence), 1);
            $wordCount = count($words);

            if ($wordCount == 0) {
                $scores[$index] = 0;
                continue;
            }

            // 1. Word frequency score (sum of word frequencies / sentence length)
            $freqScore = 0;
            foreach ($words as $word) {
                $word = trim($word, ".,;:!?\"'()[]{}");
                if (isset($wordFreq[$word])) {
                    $freqScore += $wordFreq[$word];
                }
            }
            $freqScore = $freqScore / sqrt($wordCount); // Normalize by sqrt of length

            // 2. Position score — earlier sentences get higher scores
            $positionScore = 1 - ($index / $totalSentences);
            // First sentence gets extra boost
            if ($index === 0) {
                $positionScore *= 1.5;
            }
            // Sentences in the first third get a boost
            if ($index < $totalSentences / 3) {
                $positionScore *= 1.2;
            }

            // 3. Length penalty — prefer medium-length sentences
            $len = strlen($sentence);
            $idealMin = 50;
            $idealMax = 250;
            if ($len < $idealMin) {
                $lengthScore = $len / $idealMin;
            } elseif ($len > $idealMax) {
                $lengthScore = $idealMax / $len;
            } else {
                $lengthScore = 1.0;
            }

            // 4. Keyword presence bonus — sentences with legislative keywords get boosted
            $keywordScore = $this->calculateKeywordBonus($sentence);

            // 5. Numerical data bonus — sentences with numbers/dates may contain key facts
            $numberBonus = 0;
            if (preg_match('/\b\d{4}\b/', $sentence) || preg_match('/\bSection\s+\d+/i', $sentence)) {
                $numberBonus = 0.5;
            }

            // Final weighted score
            $scores[$index] = ($freqScore * 2.0) + ($positionScore * 1.5) + ($lengthScore * 0.5) + ($keywordScore * 1.0) + ($numberBonus * 0.5);
        }

        return $scores;
    }

    /**
     * Calculate bonus for sentences containing legislative keywords
     */
    private function calculateKeywordBonus($sentence) {
        $keywords = [
            'shall', 'must', 'required', 'prohibited', 'shall not',
            'ordinance', 'resolution', 'section', 'article', 'chapter',
            'hereby', 'enacted', 'approved', 'adopted', 'amended',
            'municipality', 'city', 'barangay', 'council', 'sanggunian',
            'budget', 'appropriation', 'fund', 'allocate', 'authorize',
            'penalty', 'fine', 'violation', 'compliance', 'implement',
            'establish', 'create', 'provide', 'ensure', 'promote',
            'pursuant', 'accordingly', 'therefore', 'herein',
        ];

        $lower = strtolower($sentence);
        $bonus = 0;
        foreach ($keywords as $kw) {
            if (strpos($lower, $kw) !== false) {
                $bonus += 0.3;
            }
        }

        return min($bonus, 2.0); // Cap at 2.0
    }

    /**
     * Format sentences as bullet points
     */
    private function formatAsBulletPoints($sentences) {
        $points = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            // Ensure sentence ends with punctuation
            if (!preg_match('/[.!?]$/', $sentence)) {
                $sentence .= '.';
            }
            $points[] = '• ' . $sentence;
        }
        return $points;
    }

    /**
     * Get English stop words
     */
    private function getStopWords() {
        return [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to',
            'for', 'of', 'with', 'by', 'from', 'up', 'about', 'into', 'through',
            'during', 'before', 'after', 'above', 'below', 'between', 'under',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has',
            'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
            'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those',
            'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which',
            'who', 'when', 'where', 'why', 'how', 'all', 'each', 'every',
            'both', 'few', 'more', 'most', 'other', 'some', 'such', 'no',
            'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very',
            'just', 'as', 'if', 'also', 'any', 'its', 'their', 'his', 'her',
            'our', 'your', 'my', 'me', 'him', 'them', 'us',
            'which', 'whose', 'whom', 'there', 'here', 'now', 'then',
            'out', 'off', 'over', 'again', 'further', 'once',
        ];
    }
}
