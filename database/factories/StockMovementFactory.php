<?php

namespace Database\Factories;

use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Modules\Inventory\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);

        return [
            'tenant_id' => Tenant::factory(),
            'stock_item_id' => StockItem::factory(),
            'type' => StockMovementType::In,
            'quantity' => $quantity,
            'balance_after' => $quantity,
            'reference' => null,
            'note' => null,
            'user_id' => null,
        ];
    }
}
