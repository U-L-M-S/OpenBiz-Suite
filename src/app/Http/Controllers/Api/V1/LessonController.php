<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson): JsonResponse
    {
        $this->authorize('view', $lesson);

        return response()->json($lesson->load(['module.course', 'quiz']));
    }

    public function start(Request $request, Lesson $lesson): JsonResponse
    {
        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $lesson->module->course_id)
            ->firstOrFail();

        $progress = LessonProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => 'not_started',
            ]
        );

        $progress->start();

        return response()->json([
            'lesson' => $lesson->load('quiz'),
            'progress' => $progress,
        ]);
    }

    public function complete(Request $request, Lesson $lesson): JsonResponse
    {
        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $lesson->module->course_id)
            ->firstOrFail();

        $progress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $progress->complete();

        return response()->json([
            'lesson' => $lesson,
            'progress' => $progress->fresh(),
            'course_progress' => $enrollment->fresh()->progress_percentage,
        ]);
    }

    public function trackTime(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'seconds' => 'required|integer|min:1',
        ]);

        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $lesson->module->course_id)
            ->firstOrFail();

        $progress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $progress->addTime($validated['seconds']);

        return response()->json($progress);
    }
}
