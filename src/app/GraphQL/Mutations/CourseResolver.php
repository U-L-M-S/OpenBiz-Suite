<?php

namespace App\GraphQL\Mutations;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class CourseResolver
{
    public function publish($root, array $args): Course
    {
        $course = Course::findOrFail($args['id']);
        $course->publish();
        return $course;
    }

    public function unpublish($root, array $args): Course
    {
        $course = Course::findOrFail($args['id']);
        $course->unpublish();
        return $course;
    }

    public function enroll($root, array $args): Enrollment
    {
        $user = Auth::user();
        $course = Course::findOrFail($args['course_id']);

        $enrollment = Enrollment::firstOrCreate([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], [
            'status' => 'enrolled',
            'progress_percentage' => 0,
            'enrolled_at' => now(),
        ]);

        return $enrollment;
    }
}
