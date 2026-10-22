<?php

use App\Models\Product;
use App\Models\StockItem;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;

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
});

function tracked(float $quantity, float $reorderLevel): StockItem
{
    $product = Product::factory()->create([
        'tenant_id' => test()->tenant->getKey(),
        'track_stock' => true,
    ]);

    return StockItem::factory()->forProduct($product, quantity: $quantity, reorderLevel: $reorderLevel)->create();
}

it('alerts owners the first time stock falls to the reorder level', function () {
    $stock = tracked(10, 5);

    app(StockLedger::class)->apply($stock, StockMovementType::Out, 7, 'Penjualan');

    expect($this->owner->fresh()->notifications()->count())->toBe(1);
});

it('does not repeat the alert while stock stays low', function () {
    $stock = tracked(10, 5);
    $ledger = app(StockLedger::class);

    $ledger->apply($stock, StockMovementType::Out, 7, 'Penjualan');
    $ledger->apply($stock->fresh(), StockMovementType::Out, 1, 'Penjualan');

    expect($this->owner->fresh()->notifications()->count())->toBe(1);
});

it('escalates the alert when stock runs out', function () {
    $stock = tracked(10, 5);
    $ledger = app(StockLedger::class);

    $ledger->apply($stock, StockMovementType::Out, 7, 'Penjualan');
    $ledger->apply($stock->fresh(), StockMovementType::Out, 3, 'Penjualan');

    expect($this->owner->fresh()->notifications()->count())->toBe(2);
});

it('stays silent when stock is topped up', function () {
    $stock = tracked(10, 5);
    $ledger = app(StockLedger::class);

    $ledger->apply($stock, StockMovementType::Out, 7, 'Penjualan');
    $ledger->apply($stock->fresh(), StockMovementType::In, 20, 'Restock');

    expect($this->owner->fresh()->notifications()->count())->toBe(1);
});
