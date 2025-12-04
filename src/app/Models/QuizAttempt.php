<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'quiz_id',
        'enrollment_id',
        'score',
        'max_score',
        'percentage',
        'passed',
        'answers',
        'started_at',
        'completed_at',
        'time_taken_seconds',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function submit(array $answers): void
    {
        $score = 0;
        $maxScore = 0;

        foreach ($this->quiz->questions as $question) {
            $maxScore += $question->points;
            $userAnswer = $answers[$question->id] ?? null;

            if ($question->type === 'true_false' || $question->type === 'multiple_choice') {
                $correctAnswer = $question->correctAnswers->first();
                if ($correctAnswer && $userAnswer == $correctAnswer->id) {
                    $score += $question->points;
                }
            }
        }

        $percentage = $maxScore > 0 ? (int) (($score / $maxScore) * 100) : 0;

        $this->update([
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $percentage >= $this->quiz->passing_score,
            'answers' => $answers,
            'completed_at' => now(),
            'time_taken_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }
}
