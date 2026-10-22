<?php

use App\Livewire\OutletSwitcher;
use App\Livewire\Pos\Cashier;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\OutletManager;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Cabang',
        'name' => 'Owner Cabang',
        'email' => 'owner@cabang.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);

    $this->mainOutlet = Outlet::query()->orderBy('id')->firstOrFail();
    $this->secondOutlet = $this->tenant->outlets()->create([
        'name' => 'Cabang Dua',
        'is_active' => true,
    ]);
});

it('falls back to the first active outlet when nothing is selected', function () {
    expect(app(OutletManager::class)->current()?->getKey())->toBe($this->mainOutlet->getKey());
});

it('uses the active outlet when creating a sale', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey()]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    app(OutletManager::class)->set($this->secondOutlet);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 50000)
        ->call('pay')
        ->assertHasNoErrors();

    expect(Order::query()->latest('id')->firstOrFail()->outlet_id)->toBe($this->secondOutlet->getKey());
});

it('switches the active outlet from the topbar component', function () {
    Livewire::actingAs($this->owner)
        ->test(OutletSwitcher::class)
        ->assertSet('outletId', $this->mainOutlet->getKey())
        ->set('outletId', $this->secondOutlet->getKey())
        ->assertRedirect();

    expect(app(OutletManager::class)->id())->toBe($this->secondOutlet->getKey())
        ->and(session('active_outlet_id'))->toBe($this->secondOutlet->getKey());
});
