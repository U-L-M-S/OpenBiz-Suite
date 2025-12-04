<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'course_id',
        'status',
        'progress_percentage',
        'enrolled_at',
        'started_at',
        'completed_at',
        'expires_at',
        'amount_paid',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function start(): void
    {
        if ($this->status === 'enrolled') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(): void
    {
        $totalLessons = $this->course->lessons()->count();
        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = $this->lessonProgress()
            ->where('status', 'completed')
            ->count();

        $percentage = (int) (($completedLessons / $totalLessons) * 100);

        $this->update(['progress_percentage' => $percentage]);

        if ($percentage === 100 && $this->status !== 'completed') {
            $this->complete();
        }
    }
}
