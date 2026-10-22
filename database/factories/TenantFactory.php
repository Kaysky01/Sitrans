<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'api_key' => 'sk_'.Str::random(48),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('08##########'),
            'status' => Tenant::STATUS_ACTIVE,
            'trial_ends_at' => null,
            'settings' => [],
        ];
    }

    public function trial(?int $days = 14): static
    {
        return $this->state(fn (): array => [
            'status' => Tenant::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays($days ?? 14),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => Tenant::STATUS_SUSPENDED,
        ]);
    }
}
