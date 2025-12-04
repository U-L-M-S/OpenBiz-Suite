<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $quizzes = Quiz::where('tenant_id', $request->user()->tenant_id)
            ->with(['course', 'lesson'])
            ->withCount('questions')
            ->when($request->boolean('published_only'), fn ($q) => $q->where('is_published', true))
            ->paginate($request->get('per_page', 15));

        return response()->json($quizzes);
    }

    public function show(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('view', $quiz);

        return response()->json($quiz->load(['course', 'lesson', 'questions.answers']));
    }

    public function start(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('view', $quiz);

        if ($quiz->max_attempts) {
            $attempts = QuizAttempt::where('user_id', $request->user()->id)
                ->where('quiz_id', $quiz->id)
                ->count();

            if ($attempts >= $quiz->max_attempts) {
                return response()->json(['message' => 'Maximum attempts reached'], 403);
            }
        }

        $attempt = QuizAttempt::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'started_at' => now(),
        ]);

        $questions = $quiz->questions()->with('answers')->get();

        if ($quiz->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        return response()->json([
            'attempt_id' => $attempt->id,
            'quiz' => $quiz,
            'questions' => $questions,
            'time_limit' => $quiz->time_limit_minutes,
        ]);
    }

    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt): JsonResponse
    {
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($attempt->completed_at) {
            return response()->json(['message' => 'Quiz already submitted'], 400);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $attempt->submit($validated['answers']);

        $response = [
            'score' => $attempt->score,
            'max_score' => $attempt->max_score,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'time_taken' => $attempt->time_taken_seconds,
        ];

        if ($quiz->show_correct_answers) {
            $response['answers'] = $attempt->answers;
            $response['correct_answers'] = $quiz->questions->mapWithKeys(function ($q) {
                return [$q->id => $q->correctAnswers->pluck('id')];
            });
        }

        return response()->json($response);
    }

    public function attempts(Request $request, Quiz $quiz): JsonResponse
    {
        $attempts = QuizAttempt::where('user_id', $request->user()->id)
            ->where('quiz_id', $quiz->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attempts);
    }
}
