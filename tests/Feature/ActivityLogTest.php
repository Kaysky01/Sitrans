<?php

use App\Filament\App\Resources\ActivityLogs\ActivityLogResource;
use App\Livewire\Pos\Cashier;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Pos\Actions\VoidOrderAction;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Audit',
        'name' => 'Owner Audit',
        'email' => 'owner@audit.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->outlet = $result['outlet'];

    app(TenantManager::class)->set($this->tenant);
});

it('records an entry when an order is voided', function () {
    $order = Order::factory()->forOutlet($this->outlet)->paid()->create([
        'subtotal' => 25000,
        'grand_total' => 27500,
        'paid_at' => now(),
    ]);

    app(VoidOrderAction::class)->execute($order, $this->owner, 'Salah input');

    $log = ActivityLog::query()->where('action', 'order.void')->firstOrFail();

    expect($log->subject_id)->toBe($order->getKey())
        ->and($log->user_id)->toBe($this->owner->getKey())
        ->and($log->tenant_id)->toBe($this->tenant->getKey())
        ->and($log->meta['reason'])->toBe('Salah input');
});

it('records an entry when a supervisor approves a discount', function () {
    $this->tenant->setSetting('discount_pin_threshold', 10000);
    $this->owner->update(['pin' => '4321']);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openDiscountModal')
        ->set('discountType', 'fixed')
        ->set('discountValue', 20000)
        ->set('discountPin', '4321')
        ->call('applyDiscount')
        ->assertHasNoErrors();

    expect(ActivityLog::query()->where('action', 'discount.approved')->count())->toBe(1);
});

it('records an entry for a manual stock adjustment', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'track_stock' => true]);
    $item = app(StockLedger::class)->forProduct($product);

    app(StockLedger::class)->apply($item, StockMovementType::In, 12, 'Restock pagi', $this->owner->getKey());

    $log = ActivityLog::query()->where('action', 'inventory.adjust')->firstOrFail();

    expect((float) $log->meta['balance_after'])->toBe(12.0);
});

it('gates the activity log resource by permission', function () {
    $this->actingAs($this->owner);
    expect(ActivityLogResource::canViewAny())->toBeTrue();

    $cashier = User::factory()->create(['last_active_tenant_id' => $this->tenant->getKey()]);
    $cashier->tenants()->attach($this->tenant->getKey(), [
        'role' => RoleDefinitions::CASHIER,
        'is_active' => true,
        'joined_at' => now(),
    ]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant->getKey());
    $cashier->assignRole(RoleDefinitions::CASHIER);

    $this->actingAs($cashier);
    expect(ActivityLogResource::canViewAny())->toBeFalse();
});
