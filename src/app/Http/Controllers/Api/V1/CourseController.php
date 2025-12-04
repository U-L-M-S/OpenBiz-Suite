<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = Course::where('tenant_id', $request->user()->tenant_id)
            ->with(['instructor', 'modules'])
            ->withCount(['enrollments', 'lessons'])
            ->when($request->boolean('published_only'), fn ($q) => $q->where('is_published', true))
            ->paginate($request->get('per_page', 15));

        return response()->json($courses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructor_id' => 'nullable|exists:users,id',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'is_free' => 'boolean',
            'learning_outcomes' => 'nullable|array',
            'points_reward' => 'nullable|integer',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $course = Course::create($validated);

        return response()->json($course->load('instructor'), 201);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        $this->authorize('view', $course);

        return response()->json($course->load(['instructor', 'modules.lessons', 'quizzes']));
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'instructor_id' => 'nullable|exists:users,id',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'is_free' => 'boolean',
            'learning_outcomes' => 'nullable|array',
            'points_reward' => 'nullable|integer',
        ]);

        $course->update($validated);

        return response()->json($course->load('instructor'));
    }

    public function destroy(Request $request, Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->json(null, 204);
    }

    public function publish(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $course->publish();

        return response()->json($course);
    }

    public function unpublish(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $course->unpublish();

        return response()->json($course);
    }
}
