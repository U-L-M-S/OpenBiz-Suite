<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed tenants first
        $this->call([
            TenantSeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Create test users for demo tenant with roles
        $admin = User::factory()->create([
            'tenant_id' => 1,
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
        ]);
        $admin->assignRole('Super Admin');

        $hrManager = User::factory()->create([
            'tenant_id' => 1,
            'name' => 'HR Manager',
            'email' => 'hr@demo.com',
        ]);
        $hrManager->assignRole('HR Manager');

        $user = User::factory()->create([
            'tenant_id' => 1,
            'name' => 'Demo Employee',
            'email' => 'user@demo.com',
        ]);
        $user->assignRole('Employee');
    }
}
