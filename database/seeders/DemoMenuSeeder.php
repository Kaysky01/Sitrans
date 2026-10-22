<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Modules\Inventory\Services\StockLedger;
use Illuminate\Database\Seeder;

/**
 * Seeds a small, realistic cafe menu for the demo tenant so the cashier and
 * reporting screens are usable immediately after install.
 */
class DemoMenuSeeder extends Seeder
{
    public function run(): void
    {
        $coffee = Category::create(['name' => 'Kopi', 'is_active' => true, 'sort_order' => 1]);
        $nonCoffee = Category::create(['name' => 'Non-Kopi', 'is_active' => true, 'sort_order' => 2]);
        $food = Category::create(['name' => 'Makanan', 'is_active' => true, 'sort_order' => 3]);
        $snack = Category::create(['name' => 'Camilan', 'is_active' => true, 'sort_order' => 4]);

        $sugar = ModifierGroup::create([
            'name' => 'Level Gula',
            'min_select' => 1,
            'max_select' => 1,
            'is_required' => true,
            'sort_order' => 1,
        ]);

        foreach (['Normal', 'Less Sugar', 'No Sugar'] as $index => $label) {
            Modifier::create([
                'modifier_group_id' => $sugar->getKey(),
                'name' => $label,
                'price' => 0,
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }

        $topping = ModifierGroup::create([
            'name' => 'Topping',
            'min_select' => 0,
            'max_select' => 2,
            'is_required' => false,
            'sort_order' => 2,
        ]);

        foreach ([
            ['Extra Shot', 6000],
            ['Boba', 5000],
            ['Whipped Cream', 4000],
        ] as $index => [$label, $price]) {
            Modifier::create([
                'modifier_group_id' => $topping->getKey(),
                'name' => $label,
                'price' => $price,
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }

        $drinks = [
            ['Kopi Susu Senja', 24000, 9000, $coffee, 'products/coffee.jpg'],
            ['Americano', 20000, 6000, $coffee, 'products/coffee.jpg'],
            ['Cappuccino', 28000, 10000, $coffee, 'products/coffee.jpg'],
            ['Matcha Latte', 30000, 12000, $nonCoffee, 'products/matcha.jpg'],
            ['Chocolate', 26000, 9000, $nonCoffee, 'products/chocolate.jpg'],
            ['Lemon Tea', 22000, 5000, $nonCoffee, 'products/tea.jpg'],
        ];

        foreach ($drinks as $index => [$name, $price, $cost, $category, $imagePath]) {
            $product = Product::create([
                'category_id' => $category->getKey(),
                'name' => $name,
                'sku' => 'DRK-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'price' => $price,
                'cost' => $cost,
                'image_path' => $imagePath,
                'is_active' => true,
                'sort_order' => $index,
            ]);

            $product->modifierGroups()->attach([$sugar->getKey(), $topping->getKey()]);
        }

        foreach ([
            ['Croissant Butter', 32000, 15000, 'products/croissant.jpg'],
            ['Roti Bakar Srikaya', 28000, 11000, 'products/toast.jpg'],
            ['Nasi Goreng Kampung', 35000, 16000, 'products/croissant.jpg'],
        ] as $index => [$name, $price, $cost, $imagePath]) {
            $this->createTrackedProduct($food, $name, $price, $cost, $index, 24, 6, $imagePath);
        }

        foreach ([
            ['Kentang Goreng', 25000, 10000, 'products/fries.jpg'],
            ['Pisang Goreng Keju', 24000, 9000, 'products/toast.jpg'],
        ] as $index => [$name, $price, $cost, $imagePath]) {
            $this->createTrackedProduct($snack, $name, $price, $cost, $index, 30, 8, $imagePath);
        }
    }

    private function createTrackedProduct(Category $category, string $name, int $price, int $cost, int $sortOrder, int $quantity, int $reorderLevel, ?string $imagePath = null): void
    {
        $product = Product::create([
            'category_id' => $category->getKey(),
            'name' => $name,
            'price' => $price,
            'cost' => $cost,
            'image_path' => $imagePath,
            'is_active' => true,
            'track_stock' => true,
            'sort_order' => $sortOrder,
        ]);

        $ledger = app(StockLedger::class);
        $stock = $ledger->forProduct($product);
        $stock->reorder_level = $reorderLevel;
        $stock->save();

        $ledger->apply($stock, StockMovementType::In, $quantity, 'Stok awal demo');
    }
}
