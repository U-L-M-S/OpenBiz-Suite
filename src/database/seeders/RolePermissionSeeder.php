<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $tenantId = 1; // Demo tenant

        // Create permissions
        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',

            // Tenants
            'tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete',

            // Roles & Permissions
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.assign',

            // Employees
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',

            // Departments
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',

            // Time Tracking
            'timesheets.view', 'timesheets.create', 'timesheets.edit', 'timesheets.delete',
            'timesheets.approve',

            // Leave Management
            'leave.view', 'leave.create', 'leave.edit', 'leave.delete',
            'leave.approve',

            // Assets
            'assets.view', 'assets.create', 'assets.edit', 'assets.delete',
            'assets.assign', 'assets.scan',

            // LMS
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
            'courses.enroll', 'courses.complete',

            // Settings
            'settings.view', 'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'tenant_id' => $tenantId,
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $admin->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'timesheets.view', 'timesheets.approve',
            'leave.view', 'leave.approve',
            'assets.view', 'assets.create', 'assets.edit', 'assets.delete', 'assets.assign',
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
            'settings.view', 'settings.edit',
        ]);

        $hrManager = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'HR Manager',
            'guard_name' => 'web',
        ]);
        $hrManager->givePermissionTo([
            'employees.view', 'employees.create', 'employees.edit',
            'departments.view',
            'timesheets.view', 'timesheets.approve',
            'leave.view', 'leave.approve',
            'courses.view',
        ]);

        $employee = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);
        $employee->givePermissionTo([
            'employees.view',
            'timesheets.view', 'timesheets.create',
            'leave.view', 'leave.create',
            'assets.view', 'assets.scan',
            'courses.view', 'courses.enroll', 'courses.complete',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
