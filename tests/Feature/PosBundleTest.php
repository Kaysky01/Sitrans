<?php

use App\Livewire\Pos\Cashier;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StockItem;
use App\Modules\Pos\Actions\AddOrderItemAction;
use App\Modules\Pos\Actions\PayOrderAction;
use App\Modules\Pos\Actions\VoidOrderAction;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\OutletManager;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Paket',
        'name' => 'Kasir Paket',
        'email' => 'kasir@paket.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

function trackedProduct(float $price, float $cost, float $stock): Product
{
    $product = Product::factory()->create([
        'tenant_id' => test()->tenant->getKey(),
        'price' => $price,
        'cost' => $cost,
        'track_stock' => true,
    ]);

    StockItem::factory()->forProduct($product, quantity: $stock)->create();

    return $product;
}

function makeBundle(float $price, array $components): Bundle
{
    $bundle = Bundle::factory()->create([
        'tenant_id' => test()->tenant->getKey(),
        'price' => $price,
    ]);

    foreach ($components as [$product, $quantity]) {
        BundleItem::create([
            'bundle_id' => $bundle->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => $quantity,
        ]);
    }

    return $bundle;
}

it('adds a bundle as a single line with summed component cost', function () {
    $coffee = trackedProduct(18000, 6000, 20);
    $cake = trackedProduct(22000, 8000, 20);
    $bundle = makeBundle(32000, [[$coffee, 1], [$cake, 1]]);

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class)
        ->assertSee($bundle->name);

    startCashierOrder($component)
        ->call('addBundle', $bundle->getKey())
        ->assertHasNoErrors();

    $order = Order::query()->latest('id')->firstOrFail();

    expect($order->items()->count())->toBe(1);

    $item = $order->items()->firstOrFail();

    expect($item->bundle_id)->toBe($bundle->getKey())
        ->and((float) $item->unit_price)->toBe(32000.0)
        ->and((float) $item->cogs)->toBe(14000.0);
});

it('deducts component stock when a bundle is paid', function () {
    $coffee = trackedProduct(18000, 6000, 20);
    $bundle = makeBundle(32000, [[$coffee, 2]]);
    $cash = PaymentMethod::query()->where('code', 'cash')->firstOrFail();

    $component = Livewire::actingAs($this->owner)
        ->test(Cashier::class);

    startCashierOrder($component)
        ->call('addBundle', $bundle->getKey())
        ->call('openPaymentModal')
        ->set('paymentMethodId', $cash->getKey())
        ->set('amountPaid', 100000)
        ->call('pay')
        ->assertHasNoErrors();

    expect($coffee->refresh()->is_sold_out)->toBeFalse()
        ->and((float) StockItem::query()->where('product_id', $coffee->getKey())->firstOrFail()->quantity)->toBe(18.0);
});

it('restores component stock when a bundle order is voided', function () {
    $coffee = trackedProduct(18000, 6000, 20);
    $bundle = makeBundle(32000, [[$coffee, 2]]);

    $order = Order::factory()->forOutlet(app(OutletManager::class)->current())->create();
    app(AddOrderItemAction::class)->execute($order, [
        'bundle_id' => $bundle->getKey(),
        'name' => $bundle->name,
        'unit_price' => 32000,
        'quantity' => 1,
        'cogs' => 12000,
    ]);
    app(PayOrderAction::class)->execute($order, [[
        'method_name' => 'Tunai',
        'method_type' => PaymentType::Cash,
        'amount' => 40000,
    ]]);

    expect((float) StockItem::query()->where('product_id', $coffee->getKey())->firstOrFail()->quantity)->toBe(18.0);

    app(VoidOrderAction::class)->execute($order, $this->owner, 'Salah input');

    expect((float) StockItem::query()->where('product_id', $coffee->getKey())->firstOrFail()->quantity)->toBe(20.0);
});
