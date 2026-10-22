<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Modules\Tenancy\Actions\RegisterTenantAction;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Online',
        'name' => 'Owner Online',
        'email' => 'owner@online.test',
        'password' => 'password123',
    ]);

    $this->tenant = $result['tenant'];
    $this->key = $result['tenant']->api_key;
});

it('rejects requests without an API key', function () {
    $this->getJson('/api/v1/menu')->assertUnauthorized();
});

it('rejects an unknown API key', function () {
    $this->withHeader('X-Api-Key', 'sk_tidak-ada')->getJson('/api/v1/menu')->assertUnauthorized();
});

it('returns the menu for the tenant', function () {
    Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Kopi Susu',
        'price' => 24000,
    ]);

    $this->withHeader('X-Api-Key', $this->key)
        ->getJson('/api/v1/menu')
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Kopi Susu')
        ->assertJsonPath('data.0.price', 24000);
});

it('places a paid online order', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Matcha Latte',
        'price' => 30000,
        'cost' => 12000,
    ]);

    $response = $this->withHeader('X-Api-Key', $this->key)->postJson('/api/v1/orders', [
        'customer_name' => 'Budi',
        'type' => 'pickup',
        'payment_method' => 'qris',
        'items' => [
            ['product_id' => $product->getKey(), 'quantity' => 2],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.channel', 'online')
        ->assertJsonPath('data.type', 'pickup');

    $order = Order::query()->latest('id')->firstOrFail();

    expect($order->grand_total)->toBeGreaterThan(0.0)
        ->and($order->items)->toHaveCount(1);
});

it('blocks ordering when the subscription is inactive', function () {
    $this->tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

    Product::factory()->create(['tenant_id' => $this->tenant->getKey()]);

    $this->withHeader('X-Api-Key', $this->key)
        ->getJson('/api/v1/menu')
        ->assertForbidden();
});
