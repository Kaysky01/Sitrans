<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomElement([15000, 22000, 25000, 32000]);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'tenant_id' => Tenant::factory(),
            'order_id' => Order::factory(),
            'product_id' => null,
            'name' => fake()->words(2, true),
            'variant_name' => null,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'discount_amount' => 0,
            'cogs' => 0,
            'line_total' => $unitPrice * $quantity,
            'notes' => null,
        ];
    }

    public function forOrder(Order $order, ?Product $product = null): static
    {
        return $this->state(fn (): array => [
            'order_id' => $order->getKey(),
            'tenant_id' => $order->tenant_id,
            'product_id' => $product?->getKey(),
            'name' => $product?->name ?? fake()->words(2, true),
        ]);
    }
}
