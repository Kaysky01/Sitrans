<?php

use App\Livewire\Pos\Cashier;
use App\Models\Order;
use App\Models\Product;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Diskon',
        'name' => 'Owner Diskon',
        'email' => 'owner@diskon.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

it('applies a discount below the threshold without a pin', function () {
    $this->tenant->setSetting('discount_pin_threshold', 10000);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openDiscountModal')
        ->set('discountType', 'fixed')
        ->set('discountValue', 5000)
        ->call('applyDiscount')
        ->assertHasNoErrors();

    expect((float) Order::query()->latest('id')->firstOrFail()->discount_total)->toBe(5000.0);
});

it('requires a supervisor pin above the threshold', function () {
    $this->tenant->setSetting('discount_pin_threshold', 10000);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openDiscountModal')
        ->set('discountType', 'fixed')
        ->set('discountValue', 20000)
        ->call('applyDiscount')
        ->assertHasErrors(['pin']);

    expect((float) Order::query()->latest('id')->firstOrFail()->discount_total)->toBe(0.0);
});

it('accepts a discount above the threshold with a valid pin', function () {
    $this->tenant->setSetting('discount_pin_threshold', 10000);
    $this->owner->update(['pin' => '4321']);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openDiscountModal')
        ->set('discountType', 'percent')
        ->set('discountValue', 80)
        ->set('discountPin', '4321')
        ->call('applyDiscount')
        ->assertHasNoErrors();

    expect((float) Order::query()->latest('id')->firstOrFail()->discount_total)->toBe(20000.0);
});
