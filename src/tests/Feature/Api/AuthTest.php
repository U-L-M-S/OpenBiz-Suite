<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
            ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'wrong@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200);
    }

    public function test_user_can_get_current_user(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Current User',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Current User',
            ]);
    }
}
