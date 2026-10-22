<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Reporting\Services\SalesSummary;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Laporan',
        'name' => 'Owner Laporan',
        'email' => 'owner@laporan.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->outlet = $result['outlet'];

    app(TenantManager::class)->set($this->tenant);
});

function paidOrder(array $totals, array $items = []): Order
{
    $order = Order::factory()->forOutlet(test()->outlet)->paid()->create([
        'subtotal' => $totals['subtotal'],
        'discount_total' => $totals['discount_total'] ?? 0,
        'tax_amount' => $totals['tax_amount'] ?? 0,
        'service_amount' => $totals['service_amount'] ?? 0,
        'grand_total' => $totals['grand_total'],
        'paid_at' => now(),
    ]);

    foreach ($items as $item) {
        OrderItem::factory()->forOrder($order)->create($item);
    }

    return $order;
}

it('separates revenue from tax and service, and computes margin', function () {
    paidOrder(
        ['subtotal' => 25000, 'tax_amount' => 2500, 'grand_total' => 27500],
        [['name' => 'Kopi Susu', 'unit_price' => 25000, 'quantity' => 1, 'cogs' => 8000, 'line_total' => 25000]],
    );

    paidOrder(
        ['subtotal' => 10000, 'tax_amount' => 1000, 'grand_total' => 11000],
        [['name' => 'Americano', 'unit_price' => 10000, 'quantity' => 1, 'cogs' => 4000, 'line_total' => 10000]],
    );

    $summary = app(SalesSummary::class)->totals(now()->startOfDay(), now()->endOfDay());

    expect($summary['orders'])->toBe(2)
        ->and($summary['revenue'])->toBe(35000.0)
        ->and($summary['tax'])->toBe(3500.0)
        ->and($summary['cogs'])->toBe(12000.0)
        ->and($summary['gross_margin'])->toBe(23000.0)
        ->and($summary['avg_ticket'])->toBe(17500.0)
        ->and($summary['items_sold'])->toBe(2.0);
});

it('ignores orders that are not revenue bearing', function () {
    Order::factory()->forOutlet($this->outlet)->create([
        'status' => OrderStatus::Open,
        'subtotal' => 50000,
        'grand_total' => 55000,
    ]);

    $summary = app(SalesSummary::class)->totals(now()->startOfDay(), now()->endOfDay());

    expect($summary['orders'])->toBe(0)
        ->and($summary['revenue'])->toBe(0.0);
});

it('builds a zero filled daily series', function () {
    paidOrder(['subtotal' => 20000, 'grand_total' => 22000]);

    $series = app(SalesSummary::class)->dailySeries(
        CarbonImmutable::now()->subDays(3)->startOfDay(),
        CarbonImmutable::now()->endOfDay(),
    );

    expect($series)->toHaveCount(4)
        ->and(array_sum($series))->toBe(20000.0)
        ->and(end($series))->toBe(20000.0);
});

it('narrows the report to a single outlet', function () {
    $other = $this->tenant->outlets()->create(['name' => 'Cabang Lain', 'is_active' => true]);

    paidOrder(['subtotal' => 20000, 'grand_total' => 22000]);

    Order::factory()->forOutlet($other)->paid()->create([
        'subtotal' => 30000,
        'grand_total' => 30000,
        'paid_at' => now(),
    ]);

    $all = app(SalesSummary::class)->totals(now()->startOfDay(), now()->endOfDay());
    $onlyOther = app(SalesSummary::class)->totals(now()->startOfDay(), now()->endOfDay(), $other->getKey());

    expect($all['orders'])->toBe(2)
        ->and($all['revenue'])->toBe(50000.0)
        ->and($onlyOther['orders'])->toBe(1)
        ->and($onlyOther['revenue'])->toBe(30000.0);
});

it('renders the sales report page', function () {
    $this->actingAs($this->owner)
        ->get('/app/sales-report')
        ->assertSuccessful();
});

it('renders the dashboard with sales widgets', function () {
    paidOrder(['subtotal' => 20000, 'grand_total' => 22000]);

    $this->actingAs($this->owner)
        ->get('/app')
        ->assertSuccessful();
});
