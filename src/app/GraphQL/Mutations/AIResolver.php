<?php

namespace App\GraphQL\Mutations;

use App\Services\AIService;

class AIResolver
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateCourseDescription($root, array $args): array
    {
        return $this->aiService->generateCourseDescription(
            $args['title'],
            $args['level']
        );
    }

    public function generateQuizQuestions($root, array $args): array
    {
        return $this->aiService->generateQuizQuestions(
            $args['topic'],
            $args['count']
        );
    }
}
