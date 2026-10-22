<?php

namespace Database\Factories;

use App\Models\ModifierGroup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ModifierGroup>
 */
class ModifierGroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => Str::title(fake()->words(2, true)),
            'min_select' => 0,
            'max_select' => 1,
            'is_required' => false,
            'sort_order' => 0,
        ];
    }
}
