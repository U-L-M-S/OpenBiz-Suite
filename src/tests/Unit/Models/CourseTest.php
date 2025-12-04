<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
    }

    public function test_course_can_be_created(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Introduction to Laravel',
            'level' => 'beginner',
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Introduction to Laravel',
        ]);
    }

    public function test_course_has_auto_generated_slug(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Advanced PHP Techniques',
            'level' => 'advanced',
        ]);

        $this->assertNotNull($course->slug);
        $this->assertStringContainsString('advanced-php-techniques', $course->slug);
    }

    public function test_course_can_be_published(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Test Course',
            'level' => 'beginner',
            'is_published' => false,
        ]);

        $course->publish();

        $this->assertTrue($course->is_published);
        $this->assertNotNull($course->published_at);
    }

    public function test_course_can_be_unpublished(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Test Course',
            'level' => 'beginner',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $course->unpublish();

        $this->assertFalse($course->is_published);
        $this->assertNull($course->published_at);
    }

    public function test_course_has_modules(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Course with Modules',
            'level' => 'intermediate',
        ]);

        CourseModule::create([
            'tenant_id' => $this->tenant->id,
            'course_id' => $course->id,
            'title' => 'Module 1',
            'order' => 1,
        ]);

        $this->assertCount(1, $course->modules);
    }
}
