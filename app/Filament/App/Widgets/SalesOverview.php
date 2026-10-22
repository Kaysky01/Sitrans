<?php

namespace App\Filament\App\Widgets;

use App\Modules\Reporting\Services\SalesSummary;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    protected function getStats(): array
    {
        $summary = app(SalesSummary::class)->totals(
            CarbonImmutable::now()->startOfDay(),
            CarbonImmutable::now()->endOfDay(),
        );

        return [
            Stat::make('Penjualan hari ini', Format::rupiah($summary['revenue']))
                ->description($summary['orders'].' transaksi')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Rata-rata / transaksi', Format::rupiah($summary['avg_ticket']))
                ->description($summary['items_sold'].' item terjual')
                ->descriptionIcon('heroicon-m-calculator'),
            Stat::make('Laba kotor hari ini', Format::rupiah($summary['gross_margin']))
                ->description('HPP '.Format::rupiah($summary['cogs']))
                ->descriptionIcon('heroicon-m-banknotes'),
            Stat::make('Pajak & service', Format::rupiah($summary['tax'] + $summary['service']))
                ->description('Titipan, bukan pendapatan')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('warning'),
        ];
    }
}
