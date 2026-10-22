<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => null,
            'action' => 'order.void',
            'description' => fake()->sentence(),
            'subject_type' => null,
            'subject_id' => null,
            'meta' => null,
            'ip_address' => fake()->ipv4(),
        ];
    }
}
