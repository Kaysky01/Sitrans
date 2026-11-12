<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Modules\Reporting\Services\ReportCsv;
use App\Modules\Reporting\Services\SalesSummary;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Ekspor',
        'name' => 'Owner Ekspor',
        'email' => 'owner@ekspor.test',
        'password' => 'password123',
    ]);

    $this->owner = $result['owner'];
    $this->tenant = $result['tenant'];
    $this->outlet = $result['outlet'];

    app(TenantManager::class)->set($this->tenant);
});

function exportPaidOrder(array $totals, array $items = []): Order
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

it('collects tax and service per day', function () {
    exportPaidOrder(
        ['subtotal' => 25000, 'tax_amount' => 2500, 'service_amount' => 1250, 'grand_total' => 28750],
        [['name' => 'Kopi Susu', 'unit_price' => 25000, 'quantity' => 1, 'cogs' => 8000, 'line_total' => 25000]],
    );

    $series = app(SalesSummary::class)->taxSeries(now()->startOfDay(), now()->endOfDay());

    expect($series)->toHaveCount(1);
    $row = end($series);

    expect($row['orders'])->toBe(1)
        ->and($row['tax'])->toBe(2500.0)
        ->and($row['service'])->toBe(1250.0)
        ->and($row['gross'])->toBe(28750.0);
});

it('renders a sales CSV export', function () {
    exportPaidOrder(
        ['subtotal' => 25000, 'tax_amount' => 2500, 'grand_total' => 27500],
        [['name' => 'Kopi Susu', 'unit_price' => 25000, 'quantity' => 1, 'cogs' => 8000, 'line_total' => 25000]],
    );

    $csv = app(ReportCsv::class)->sales(now()->startOfDay(), now()->endOfDay());

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->toContain('Laporan penjualan')
        ->toContain('Subtotal menu')
        ->toContain('Penjualan bersih')
        ->toContain('Laba kotor')
        ->toContain('Penjualan per hari')
        ->toContain('Menu terlaris')
        ->toContain('Kopi Susu')
        ->toContain('25000');
});

it('renders a tax CSV export', function () {
    exportPaidOrder(['subtotal' => 25000, 'tax_amount' => 2500, 'service_amount' => 1250, 'grand_total' => 28750]);

    $csv = app(ReportCsv::class)->tax(CarbonImmutable::now()->startOfDay(), CarbonImmutable::now()->endOfDay());

    expect($csv)->toContain('Laporan PB1 & service')
        ->toContain('Total PB1')
        ->toContain('3750');
});

it('renders the tax report page', function () {
    $this->actingAs($this->owner)
        ->get('/app/tax-report')
        ->assertSuccessful();
});
