<?php

namespace Database\Factories;

use App\Models\CashShift;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashShift>
 */
class CashShiftFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'outlet_id' => Outlet::factory(),
            'user_id' => User::factory(),
            'opened_at' => now(),
            'closed_at' => null,
            'opening_float' => 0,
            'counted_cash' => null,
            'expected_cash' => 0,
            'difference' => 0,
            'status' => CashShift::STATUS_OPEN,
            'notes' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => CashShift::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }
}
