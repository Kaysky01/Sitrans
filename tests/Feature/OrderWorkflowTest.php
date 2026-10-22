<?php

use App\Filament\App\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Modules\Pos\Actions\CompleteOrderAction;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Alur',
        'name' => 'Owner Alur',
        'email' => 'owner@alur.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->outlet = $result['outlet'];

    app(TenantManager::class)->set($this->tenant);
});

it('completes a paid order', function () {
    $order = Order::factory()->forOutlet($this->outlet)->paid()->create();

    app(CompleteOrderAction::class)->execute($order);

    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
});

it('leaves an unpaid order untouched', function () {
    $order = Order::factory()->forOutlet($this->outlet)->create(['status' => OrderStatus::Open]);

    app(CompleteOrderAction::class)->execute($order);

    expect($order->fresh()->status)->toBe(OrderStatus::Open);
});

it('completes a paid order through the orders table', function () {
    $order = Order::factory()->forOutlet($this->outlet)->paid()->create();

    Livewire::actingAs($this->owner)
        ->test(ListOrders::class)
        ->callTableAction('complete', $order)
        ->assertHasNoTableActionErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
});
