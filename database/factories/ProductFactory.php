<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => null,
            'name' => Str::title(fake()->unique()->words(2, true)),
            'sku' => 'SKU-'.Str::upper(Str::random(6)),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([15000, 18000, 22000, 25000, 28000, 32000]),
            'cost' => fake()->numberBetween(4000, 14000),
            'is_active' => true,
            'is_sold_out' => false,
            'track_stock' => false,
            'unit' => 'porsi',
            'sort_order' => 0,
        ];
    }

    public function inCategory(Category $category): static
    {
        return $this->state(fn (): array => [
            'category_id' => $category->getKey(),
            'tenant_id' => $category->tenant_id,
        ]);
    }

    public function soldOut(): static
    {
        return $this->state(fn (): array => ['is_sold_out' => true]);
    }
}
