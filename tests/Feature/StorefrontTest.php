<?php

use App\Livewire\Public\Storefront;
use App\Models\Order;
use App\Models\Product;
use App\Modules\Pos\Enums\OrderChannel;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Publik',
        'name' => 'Owner Publik',
        'email' => 'owner@publik.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
});

it('renders the public storefront for an active tenant', function () {
    app(TenantManager::class)->set($this->tenant);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'name' => 'Es Kopi Susu', 'price' => 22000]);
    app(TenantManager::class)->forget();

    $this->get(route('storefront', ['tenant' => $this->tenant->slug]))
        ->assertOk()
        ->assertSee('Es Kopi Susu');
});

it('hides the storefront for a suspended tenant', function () {
    $this->tenant->update(['status' => 'suspended']);
    $this->tenant->refresh();

    $this->get(route('storefront', ['tenant' => $this->tenant->slug]))->assertNotFound();
});

it('requires customer details before an online order can be placed', function () {
    app(TenantManager::class)->set($this->tenant);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);
    app(TenantManager::class)->forget();

    // Tanpa nama pelanggan.
    Livewire::test(Storefront::class, ['tenant' => $this->tenant])
        ->call('addToCart', $product->getKey())
        ->set('type', 'pickup')
        ->call('placeOrder')
        ->assertHasErrors('customerName');

    // Tanpa memilih tipe pesanan.
    Livewire::test(Storefront::class, ['tenant' => $this->tenant])
        ->call('addToCart', $product->getKey())
        ->set('customerName', 'Sari')
        ->set('type', '')
        ->call('placeOrder')
        ->assertHasErrors('type');

    // Dine In membutuhkan nomor meja.
    Livewire::test(Storefront::class, ['tenant' => $this->tenant])
        ->call('addToCart', $product->getKey())
        ->set('customerName', 'Sari')
        ->set('type', 'dine_in')
        ->call('placeOrder')
        ->assertHasErrors('tableNumber');

    // Detail lengkap: pesanan tersimpan dengan meja & catatan.
    Livewire::test(Storefront::class, ['tenant' => $this->tenant])
        ->call('addToCart', $product->getKey())
        ->set('customerName', 'Sari')
        ->set('type', 'dine_in')
        ->set('tableNumber', '12')
        ->set('notes', 'Tanpa gula')
        ->call('placeOrder')
        ->assertHasNoErrors();

    $order = Order::query()->withoutGlobalScopes()->latest('id')->firstOrFail();

    expect($order->customer_name)->toBe('Sari')
        ->and($order->table_number)->toBe('12')
        ->and($order->notes)->toBe('Tanpa gula');
});

it('places an online order from the storefront', function () {
    app(TenantManager::class)->set($this->tenant);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey(), 'price' => 25000]);
    app(TenantManager::class)->forget();

    Livewire::test(Storefront::class, ['tenant' => $this->tenant])
        ->call('addToCart', $product->getKey())
        ->set('customerName', 'Budi')
        ->set('type', 'pickup')
        ->call('placeOrder')
        ->assertHasNoErrors();

    $order = Order::query()->withoutGlobalScopes()->latest('id')->firstOrFail();

    expect($order->channel)->toBe(OrderChannel::Online)
        ->and($order->status)->toBe(OrderStatus::Paid)
        ->and($order->customer_name)->toBe('Budi')
        ->and((float) $order->grand_total)->toBe(27500.0);
});
