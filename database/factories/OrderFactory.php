<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Modules\Pos\Enums\OrderChannel;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'outlet_id' => Outlet::factory(),
            'user_id' => null,
            'number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'channel' => OrderChannel::Offline,
            'type' => OrderType::DineIn,
            'status' => OrderStatus::Open,
            'customer_name' => null,
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_amount' => 0,
            'service_amount' => 0,
            'grand_total' => 0,
            'paid_total' => 0,
            'change_total' => 0,
            'ordered_at' => now(),
        ];
    }

    public function forOutlet(Outlet $outlet): static
    {
        return $this->state(fn (): array => [
            'outlet_id' => $outlet->getKey(),
            'tenant_id' => $outlet->tenant_id,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function takeaway(): static
    {
        return $this->state(fn (): array => ['type' => OrderType::Takeaway]);
    }
}
