<?php

use App\Livewire\Pos\Cashier;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Split',
        'name' => 'Kasir Split',
        'email' => 'kasir@split.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

it('settles an order with two payment methods', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();
    $qris = PaymentMethod::query()->where('code', 'qris')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 10000)
        ->call('addPaymentLine')
        ->set('extraPayments.0.method_id', $qris->getKey())
        ->set('extraPayments.0.amount', 17500)
        ->set('extraPayments.0.reference', 'REF-1')
        ->call('pay')
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($order->payments()->count())->toBe(2)
        ->and((float) $order->paid_total)->toBe(27500.0)
        ->and((float) $order->change_total)->toBe(0.0);
});

it('generates a reference on a non-cash split line', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();
    $qris = PaymentMethod::query()->where('code', 'qris')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 27500)
        ->call('addPaymentLine')
        ->set('extraPayments.0.method_id', $qris->getKey())
        ->set('extraPayments.0.amount', 5000)
        ->call('pay')
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    expect($order->payments()->where('payment_method_id', $qris->getKey())->firstOrFail()->reference)
        ->toStartWith('QRIS-'.$order->number.'-2');
});
