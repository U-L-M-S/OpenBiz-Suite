<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
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

    public function test_can_list_employees(): void
    {
        Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'email'],
                ],
            ]);
    }

    public function test_can_create_employee(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/employees', [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@example.com',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'first_name' => 'Jane',
                'last_name' => 'Smith',
            ]);
    }

    public function test_can_show_employee(): void
    {
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
            'email' => 'bob@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJson([
                'first_name' => 'Bob',
            ]);
    }

    public function test_can_update_employee(): void
    {
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Original',
            'last_name' => 'Name',
            'email' => 'original@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/employees/{$employee->id}", [
                'first_name' => 'Updated',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'first_name' => 'Updated',
            ]);
    }

    public function test_can_delete_employee(): void
    {
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Delete',
            'last_name' => 'Me',
            'email' => 'delete@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/employees/{$employee->id}");

        $response->assertStatus(204);
    }
}
