<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_can_list_courses(): void
    {
        Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Test Course',
            'level' => 'beginner',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'level'],
                ],
            ]);
    }

    public function test_can_create_course(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/courses', [
                'title' => 'New Course',
                'description' => 'Course description',
                'level' => 'intermediate',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'New Course',
                'level' => 'intermediate',
            ]);
    }

    public function test_can_show_course(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Show Course',
            'level' => 'advanced',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Show Course',
            ]);
    }

    public function test_can_publish_course(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Unpublished Course',
            'level' => 'beginner',
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/courses/{$course->id}/publish");

        $response->assertStatus(200);

        $this->assertTrue($course->fresh()->is_published);
    }

    public function test_can_unpublish_course(): void
    {
        $course = Course::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Published Course',
            'level' => 'beginner',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/courses/{$course->id}/unpublish");

        $response->assertStatus(200);

        $this->assertFalse($course->fresh()->is_published);
    }
}
