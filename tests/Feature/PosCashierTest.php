<?php

use App\Livewire\Pos\Cashier;
use App\Livewire\Pos\Receipt;
use App\Mail\OrderReceiptMail;
use App\Models\Category;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Pos',
        'name' => 'Kasir Pos',
        'email' => 'kasir@pos.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

it('adds a product, applies tax and pays the order', function () {
    $category = Category::factory()->create(['tenant_id' => $this->tenant->getKey()]);
    $product = Product::factory()->inCategory($category)->create(['price' => 25000, 'cost' => 8000]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->assertSee($product->name);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 50000)
        ->call('pay')
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($order->items()->count())->toBe(1)
        ->and((float) $order->subtotal)->toBe(25000.0)
        ->and((float) $order->tax_amount)->toBe(2500.0)
        ->and((float) $order->grand_total)->toBe(27500.0)
        ->and((float) $order->change_total)->toBe(22500.0);
});

it('renders the receipt for a paid order', function () {
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
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    $this->actingAs($this->owner)
        ->get(route('receipt', ['order' => $order->getKey()]))
        ->assertSuccessful()
        ->assertSee($order->number);
});

it('emails the receipt to the customer', function () {
    Mail::fake();

    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey()]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 100000)
        ->call('pay')
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    Livewire::actingAs($this->owner)
        ->test(Receipt::class, ['order' => $order])
        ->set('email', 'pelanggan@example.test')
        ->call('sendEmail')
        ->assertHasNoErrors()
        ->assertSet('sentTo', 'pelanggan@example.test');

    Mail::assertSent(OrderReceiptMail::class, fn (OrderReceiptMail $mail): bool => $mail->hasTo('pelanggan@example.test'));
});

it('requires a choice from a required modifier group and applies variant price', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'price' => 20000,
    ]);
    ProductVariant::factory()->forProduct($product)->create(['name' => 'Large', 'price' => 25000]);

    $group = ModifierGroup::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'min_select' => 1,
        'max_select' => 1,
        'is_required' => true,
    ]);
    $modifier = Modifier::factory()->forGroup($group)->create(['name' => 'Extra Shot', 'price' => 5000]);
    $product->modifierGroups()->attach($group);

    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)->call('chooseProduct', $product->getKey());

    $variant = $product->variants()->firstOrFail();

    $component
        ->set('modalVariantId', $variant->getKey())
        ->set('modalModifiers.'.$group->getKey(), $modifier->getKey())
        ->call('confirmProduct')
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 100000)
        ->call('pay')
        ->assertHasNoErrors();

    $item = Order::query()->latest('id')->firstOrFail()->items()->firstOrFail();

    expect((float) $item->unit_price)->toBe(30000.0);
});

it('allows filling the cart first but blocks payment until order type and customer name are provided', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 20000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->call('chooseProduct', $product->getKey())
        ->assertHasNoErrors()
        ->assertSet('showCart', true)
        ->call('openPaymentModal')
        ->assertHasErrors(['orderType', 'customerName']);

    expect(Order::query()->count())->toBe(1)
        ->and(Order::query()->firstOrFail()->items()->count())->toBe(1);
});

it('requires a table number for dine-in orders', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 20000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    $component
        ->set('orderType', 'dine_in')
        ->set('customerName', 'Rara')
        ->set('tableNumber', '')
        ->call('chooseProduct', $product->getKey())
        ->assertHasNoErrors()
        ->call('openPaymentModal')
        ->assertHasErrors('tableNumber');

    expect(Order::query()->count())->toBe(1);

    $component->set('tableNumber', '07')
        ->call('openPaymentModal')
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    expect($order->customer_name)->toBe('Rara')
        ->and($order->table_number)->toBe('07');
});

it('persists optional order notes and syncs edits into the open order', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 20000]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    $component
        ->set('orderType', 'takeaway')
        ->set('customerName', 'Dina')
        ->set('orderNotes', 'Less sugar')
        ->call('chooseProduct', $product->getKey())
        ->assertHasNoErrors();

    // Nama bisa berubah sebelum pesanan dibayar.
    $component->set('customerName', 'Dina Kartika');

    $order = Order::query()->latest('id')->firstOrFail()->refresh();

    expect($order->customer_name)->toBe('Dina Kartika')
        ->and($order->notes)->toBe('Less sugar')
        ->and($order->table_number)->toBeNull();
});

it('renders the cashier page over http', function () {
    $this->actingAs($this->owner)
        ->get('/pos')
        ->assertSuccessful()
        ->assertSeeLivewire(Cashier::class);
});

it('generates a reference automatically when a reference is required', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey()]);
    $qris = PaymentMethod::query()->where('code', 'qris')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('chooseProduct', $product->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $qris->getKey())
        ->set('amountPaid', 50000)
        ->call('pay')
        ->assertHasNoErrors();

    $order = Order::query()->where('status', OrderStatus::Paid)->firstOrFail();

    expect($order->payments()->firstOrFail()->reference)
        ->toStartWith('QRIS-'.$order->number.'-1');
});

it('renders the cashier through its route with the page layout', function () {
    $this->actingAs($this->owner)
        ->get(route('pos'))
        ->assertOk()
        ->assertSee('Kasir', false);
});
