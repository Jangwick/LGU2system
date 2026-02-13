<?php

namespace App\Services;

class TriageService
{
    public static function process(string $message): array
    {
        $messageLower = strtolower($message);

        $keywords = [
            'emergency' => ['chest pain', 'hirap huminga', 'severe bleeding', 'matinding sakit', 'nahihilo', 'loss of consciousness'],
            'urgent' => ['fever', 'lagnat', 'cough', 'ubo', 'vomiting', 'pagsusuka'],
            'non-urgent' => ['headache', 'sakit ng ulo', 'sore throat', 'masakit ang lalamunan']
        ];

        foreach ($keywords['emergency'] as $word) {
            if (strpos($messageLower, $word) !== false) {
                return ['level' => 'Emergency', 'advice' => 'Seek immediate medical attention.'];
            }
        }

        foreach ($keywords['urgent'] as $word) {
            if (strpos($messageLower, $word) !== false) {
                return ['level' => 'Urgent', 'advice' => 'Visit the clinic today for evaluation.'];
            }
        }

        return ['level' => 'Non-Urgent', 'advice' => 'Home care is recommended, but monitor your symptoms.'];
    }
}
