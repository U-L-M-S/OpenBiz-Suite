<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
    }

    public function test_employee_can_be_created(): void
    {
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('employees', [
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_employee_has_auto_generated_employee_id(): void
    {
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'status' => 'active',
        ]);

        $this->assertNotNull($employee->employee_id);
        $this->assertStringStartsWith('EMP', $employee->employee_id);
    }

    public function test_employee_belongs_to_department(): void
    {
        $department = Department::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Engineering',
        ]);

        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'department_id' => $department->id,
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
            'email' => 'bob.wilson@example.com',
            'status' => 'active',
        ]);

        $this->assertEquals($department->id, $employee->department->id);
    }

    public function test_employee_full_name_attribute(): void
    {
        $employee = Employee::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice.johnson@example.com',
            'status' => 'active',
        ]);

        $this->assertEquals('Alice Johnson', $employee->full_name);
    }
}
