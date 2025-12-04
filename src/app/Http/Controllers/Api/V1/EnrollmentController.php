<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::where('tenant_id', $request->user()->tenant_id)
            ->with(['user', 'course'])
            ->when($request->has('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->has('course_id'), fn ($q) => $q->where('course_id', $request->course_id))
            ->paginate($request->get('per_page', 15));

        return response()->json($enrollments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $userId = $validated['user_id'] ?? $request->user()->id;

        $existing = Enrollment::where('user_id', $userId)
            ->where('course_id', $validated['course_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already enrolled'], 409);
        }

        $course = Course::findOrFail($validated['course_id']);

        $enrollment = Enrollment::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $userId,
            'course_id' => $validated['course_id'],
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'amount_paid' => $course->is_free ? 0 : $course->price,
        ]);

        return response()->json($enrollment->load(['user', 'course']), 201);
    }

    public function show(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorize('view', $enrollment);

        return response()->json($enrollment->load(['user', 'course', 'lessonProgress']));
    }

    public function myEnrollments(Request $request): JsonResponse
    {
        $enrollments = Enrollment::where('user_id', $request->user()->id)
            ->with(['course'])
            ->paginate($request->get('per_page', 15));

        return response()->json($enrollments);
    }

    public function progress(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorize('view', $enrollment);

        $enrollment->updateProgress();

        return response()->json([
            'enrollment' => $enrollment,
            'progress' => $enrollment->progress_percentage,
            'lessons_completed' => $enrollment->lessonProgress()->where('status', 'completed')->count(),
            'total_lessons' => $enrollment->course->lessons()->count(),
        ]);
    }
}
