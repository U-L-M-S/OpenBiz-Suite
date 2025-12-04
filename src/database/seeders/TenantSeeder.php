<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::create([
            'name' => 'Demo Company',
            'slug' => 'demo',
            'domain' => 'demo.openbiz.local',
            'is_active' => true,
            'settings' => [
                'timezone' => 'Europe/Berlin',
                'currency' => 'EUR',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
            ],
        ]);

        Tenant::create([
            'name' => 'Test Organization',
            'slug' => 'test',
            'domain' => 'test.openbiz.local',
            'is_active' => true,
            'settings' => [
                'timezone' => 'Europe/Berlin',
                'currency' => 'EUR',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
            ],
        ]);
    }
}
