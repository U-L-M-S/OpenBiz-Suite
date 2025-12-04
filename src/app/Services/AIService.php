<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');
    }

    public function generateCourseDescription(string $title, string $level): array
    {
        $prompt = "Generate a professional course description for a course titled '{$title}' at the {$level} level. The description should be 2-3 paragraphs, engaging, and highlight what students will learn.";

        return $this->chat($prompt);
    }

    public function generateQuizQuestions(string $topic, int $count): array
    {
        $prompt = "Generate {$count} multiple choice quiz questions about '{$topic}'. Format each question as JSON with: question, options (array of 4 choices), correct_answer (index 0-3), explanation.";

        return $this->chat($prompt);
    }

    public function generateContent(string $prompt): array
    {
        return $this->chat($prompt);
    }

    protected function chat(string $prompt): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'OpenAI API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'content' => null,
                'error' => $response->json('error.message', 'API request failed'),
            ];
        } catch (\Exception $e) {
            Log::error('AI Service error: ' . $e->getMessage());
            return [
                'success' => false,
                'content' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
