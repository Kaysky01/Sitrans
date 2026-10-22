<?php

use App\Livewire\Pos\Cashier;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Modules\Pos\Actions\AddOrderItemAction;
use App\Modules\Pos\Actions\CreateOrderAction;
use App\Modules\Pos\Actions\PayOrderAction;
use App\Modules\Pos\Actions\RecalculateOrderAction;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Pos\Services\CashShiftService;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\OutletManager;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Shift',
        'name' => 'Kasir Shift',
        'email' => 'kasir@shift.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

it('opens a shift and reuses the open one', function () {
    $outlet = app(OutletManager::class)->current();
    $service = app(CashShiftService::class);

    $shift = $service->open($outlet, $this->owner, 100000);

    expect($shift->status)->toBe(CashShift::STATUS_OPEN)
        ->and((float) $shift->opening_float)->toBe(100000.0)
        ->and($service->open($outlet, $this->owner, 50000)->getKey())->toBe($shift->getKey());
});

it('reconciles expected cash and records the closing variance', function () {
    $outlet = app(OutletManager::class)->current();
    $service = app(CashShiftService::class);
    $shift = $service->open($outlet, $this->owner, 100000);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);

    $order = app(CreateOrderAction::class)->execute($outlet, [], $this->owner);
    $order->fill(['cash_shift_id' => $shift->getKey()])->save();

    app(AddOrderItemAction::class)->execute($order, [
        'product_id' => $product->getKey(),
        'name' => $product->name,
        'unit_price' => 25000,
        'quantity' => 1,
        'cogs' => 0,
    ]);

    app(RecalculateOrderAction::class)->execute($order);

    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    app(PayOrderAction::class)->execute($order->fresh(), [[
        'payment_method_id' => $cash->getKey(),
        'method_name' => 'Tunai',
        'method_type' => PaymentType::Cash,
        'amount' => 30000,
    ]]);

    $summary = $service->summary($shift->fresh());

    expect($summary['cash_sales'])->toBe(30000.0)
        ->and($summary['orders'])->toBe(1)
        ->and($summary['expected_cash'])->toBe(127500.0);

    $closed = $service->close($shift->fresh(), $this->owner, 127000, 'Kurang 500');

    expect($closed->status)->toBe(CashShift::STATUS_CLOSED)
        ->and((float) $closed->difference)->toBe(-500.0);
});

it('opens a shift from the cashier screen', function () {
    Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->set('openingFloat', 150000)
        ->call('openShift')
        ->assertHasNoErrors();

    $shift = CashShift::query()->where('status', CashShift::STATUS_OPEN)->firstOrFail();

    expect((float) $shift->opening_float)->toBe(150000.0);
});

it('blocks payment until a shift is opened when required', function () {
    $this->tenant->setSetting('require_shift', true);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 20000]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 50000)
        ->call('pay')
        ->assertHasErrors(['paymentMethodId']);

    expect(Order::query()->latest('id')->firstOrFail()->status)->toBe(OrderStatus::Open);
});

it('links a paid order to the open shift', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 20000]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->call('openShift');

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 50000)
        ->call('pay')
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();
    $shift = CashShift::query()->where('status', CashShift::STATUS_OPEN)->firstOrFail();

    expect($order->cash_shift_id)->toBe($shift->getKey());
});
