<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Regular', 'Large', 'Medium']),
            'sku' => null,
            'price' => fake()->randomElement([0, 5000, 8000]),
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (): array => [
            'product_id' => $product->getKey(),
            'tenant_id' => $product->tenant_id,
        ]);
    }
}
