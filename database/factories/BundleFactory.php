<?php

namespace Database\Factories;

use App\Models\Bundle;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Bundle>
 */
class BundleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => null,
            'name' => 'Paket '.Str::title(fake()->word()),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([35000, 45000, 55000]),
            'is_active' => true,
        ];
    }
}
