<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Outlet '.fake()->city(),
            'code' => 'OUT-'.Str::upper(Str::random(4)),
            'address' => fake()->address(),
            'phone' => fake()->numerify('08##########'),
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ];
    }
}
