<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'quantity' => fake()->numberBetween(0, 100),
            'reorder_level' => 5,
        ];
    }

    public function forProduct(Product $product, ?int $variantId = null, float $quantity = 0, float $reorderLevel = 0): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->getKey(),
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'reorder_level' => $reorderLevel,
        ]);
    }
}
