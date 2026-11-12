<?php

use App\Livewire\Pos\IncomingOrders;
use App\Models\Order;
use App\Modules\Pos\Actions\AdvanceOrderFulfillmentAction;
use App\Modules\Pos\Enums\OrderFulfillmentStatus;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Antrean',
        'name' => 'Owner Antrean',
        'email' => 'owner@antrean.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->outlet = $result['outlet'];

    app(TenantManager::class)->set($this->tenant);
});

it('shows queue position and advances an order through the incoming board', function () {
    $first = Order::factory()->forOutlet($this->outlet)->paid()->create([
        'ordered_at' => now()->subMinutes(10),
        'fulfillment_status' => OrderFulfillmentStatus::Pending,
    ]);
    $second = Order::factory()->forOutlet($this->outlet)->paid()->create([
        'ordered_at' => now()->subMinutes(2),
        'fulfillment_status' => OrderFulfillmentStatus::Pending,
    ]);

    $component = Livewire::actingAs($this->owner)
        ->test(IncomingOrders::class)
        ->assertSee('Antrian 1 dari 2')
        ->assertSee('Antrian 2 dari 2')
        ->assertSee('1 pesanan di depan');

    $component->call('accept', $first->getKey())
        ->call('process', $first->getKey())
        ->call('complete', $first->getKey())
        ->assertHasNoErrors();

    expect($first->fresh()->fulfillment_status)->toBe(OrderFulfillmentStatus::Completed)
        ->and($first->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($second->fresh()->fulfillment_status)->toBe(OrderFulfillmentStatus::Pending);
});

it('blocks completing an unpaid order', function () {
    $order = Order::factory()->forOutlet($this->outlet)->create([
        'status' => OrderStatus::Open,
        'fulfillment_status' => OrderFulfillmentStatus::Pending,
    ]);

    app(AdvanceOrderFulfillmentAction::class)->execute($order, OrderFulfillmentStatus::Accepted, $this->owner);
    app(AdvanceOrderFulfillmentAction::class)->execute($order, OrderFulfillmentStatus::Processing, $this->owner);

    expect(fn () => app(AdvanceOrderFulfillmentAction::class)->execute($order, OrderFulfillmentStatus::Completed, $this->owner))
        ->toThrow(ValidationException::class);

    expect($order->fresh()->fulfillment_status)->toBe(OrderFulfillmentStatus::Processing);
});

it('removes completed orders from the active queue', function () {
    $done = Order::factory()->forOutlet($this->outlet)->paid()->create([
        'fulfillment_status' => OrderFulfillmentStatus::Completed,
    ]);
    $active = Order::factory()->forOutlet($this->outlet)->paid()->create([
        'fulfillment_status' => OrderFulfillmentStatus::Pending,
    ]);

    Livewire::actingAs($this->owner)
        ->test(IncomingOrders::class)
        ->assertSee($active->number)
        ->assertDontSee($done->number);
});
