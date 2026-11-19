<?php

use App\Exceptions\ConflictError;
use App\Filament\App\Resources\StockItems\Pages\ListStockItems;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Pos\Actions\PayOrderAction;
use App\Modules\Pos\Actions\VoidOrderAction;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Stok',
        'name' => 'Owner Stok',
        'email' => 'owner@stok.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);

    $this->outlet = Outlet::query()->firstOrFail();
});

function payOrderFor(Product $product, float $quantity): Order
{
    $outlet = Outlet::query()->firstOrFail();

    $order = Order::factory()->forOutlet($outlet)->create([
        'grand_total' => $product->price * $quantity,
    ]);

    OrderItem::factory()->forOrder($order, $product)->create([
        'quantity' => $quantity,
        'unit_price' => $product->price,
        'line_total' => $product->price * $quantity,
    ]);

    app(PayOrderAction::class)->execute($order, [[
        'method_name' => 'Tunai',
        'method_type' => PaymentType::Cash,
        'amount' => $order->grand_total,
    ]]);

    return $order;
}

it('records a movement and raises the balance when stock is received', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true]);
    $stock = StockItem::factory()->forProduct($product, null, 5, 2)->create();

    app(StockLedger::class)->apply($stock, StockMovementType::In, 10, 'Kiriman pemasok', $this->owner->getKey());

    expect($stock->fresh()->quantity)->toBe(15.0);

    $movement = StockMovement::query()->where('stock_item_id', $stock->getKey())->latest('id')->first();

    expect($movement->type)->toBe(StockMovementType::In)
        ->and($movement->quantity)->toBe(10.0)
        ->and($movement->balance_after)->toBe(15.0)
        ->and($movement->user_id)->toBe($this->owner->getKey());
});

it('deducts stock when a tracked product is sold', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true, 'price' => 20000]);
    $stock = StockItem::factory()->forProduct($product, null, 10, 2)->create();

    $order = payOrderFor($product, 3);

    expect($stock->fresh()->quantity)->toBe(7.0);

    $movement = StockMovement::query()->where('reference', $order->number)->first();

    expect($movement->type)->toBe(StockMovementType::Sale)
        ->and($movement->quantity)->toBe(-3.0);
});

it('marks a tracked product as sold out once stock reaches zero', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true, 'price' => 20000]);
    StockItem::factory()->forProduct($product, null, 2, 1)->create();

    payOrderFor($product, 2);

    expect($product->fresh()->is_sold_out)->toBeTrue();
});

it('restores stock when an order is voided', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true, 'price' => 20000]);
    $stock = StockItem::factory()->forProduct($product, null, 5, 1)->create();

    $order = payOrderFor($product, 4);
    expect($stock->fresh()->quantity)->toBe(1.0)
        ->and($product->fresh()->is_sold_out)->toBeFalse();

    app(VoidOrderAction::class)->execute($order, $this->owner, 'Salah input');

    expect($stock->fresh()->quantity)->toBe(5.0)
        ->and($product->fresh()->is_sold_out)->toBeFalse();

    expect(StockMovement::query()->where('reference', $order->number)->count())->toBe(2);
});

it('never deducts the same order twice', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true, 'price' => 20000]);
    $stock = StockItem::factory()->forProduct($product, null, 10, 2)->create();

    $order = payOrderFor($product, 3);

    expect(fn () => app(PayOrderAction::class)->execute($order, [[
        'method_name' => 'Tunai',
        'method_type' => PaymentType::Cash,
        'amount' => 0,
    ]]))->toThrow(ConflictError::class);

    expect($stock->fresh()->quantity)->toBe(7.0)
        ->and(StockMovement::query()->where('reference', $order->number)->count())->toBe(1);
});

it('receives stock through the back office action', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true]);

    Livewire::actingAs($this->owner)
        ->test(ListStockItems::class)
        ->callAction('receive', data: [
            'product_id' => $product->getKey(),
            'product_variant_id' => null,
            'quantity' => 12,
            'reorder_level' => 3,
            'note' => 'Tes terima',
        ])
        ->assertHasNoActionErrors();

    $stock = StockItem::query()->where('product_id', $product->getKey())->firstOrFail();

    expect($stock->quantity)->toBe(12.0)
        ->and($stock->reorder_level)->toBe(3.0);
});

it('adjusts stock through the table action', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true]);
    $stock = StockItem::factory()->forProduct($product, null, 10, 2)->create();

    Livewire::actingAs($this->owner)
        ->test(ListStockItems::class)
        ->callTableAction('adjust', $stock, data: [
            'type' => StockMovementType::Waste->value,
            'quantity' => 4,
            'note' => 'Tumpah',
        ])
        ->assertHasNoTableActionErrors();

    expect($stock->fresh()->quantity)->toBe(6.0);
});

it('ignores products that do not track stock', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => false, 'price' => 20000]);

    payOrderFor($product, 2);

    expect(StockItem::query()->where('product_id', $product->getKey())->exists())->toBeFalse();
});
