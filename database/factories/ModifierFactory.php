<?php

namespace Database\Factories;

use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Modifier>
 */
class ModifierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'modifier_group_id' => ModifierGroup::factory(),
            'name' => Str::title(fake()->words(1, true)),
            'price' => fake()->randomElement([0, 3000, 5000, 8000]),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forGroup(ModifierGroup $group): static
    {
        return $this->state(fn (): array => [
            'modifier_group_id' => $group->getKey(),
            'tenant_id' => $group->tenant_id,
        ]);
    }
}
