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
        ]);

        // Create test users for demo tenant
        User::factory()->create([
            'tenant_id' => 1,
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
        ]);

        User::factory()->create([
            'tenant_id' => 1,
            'name' => 'Demo User',
            'email' => 'user@demo.com',
        ]);
    }
}
