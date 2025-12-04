<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => Str::slug(fake()->company()) . '-' . Str::random(6),
            'domain' => fake()->domainName(),
            'is_active' => true,
        ];
    }
}
