<?php

namespace App\Filament\App\Widgets;

use App\Modules\Reporting\Services\SalesSummary;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Penjualan 7 hari terakhir';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    protected function getData(): array
    {
        $series = app(SalesSummary::class)->dailySeries(
            CarbonImmutable::now()->subDays(6)->startOfDay(),
            CarbonImmutable::now()->endOfDay(),
        );

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => array_values($series),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => array_map(
                fn (string $day): string => CarbonImmutable::parse($day)->translatedFormat('d M'),
                array_keys($series),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $summary = app(SalesSummary::class)->totals(
            CarbonImmutable::now()->subDays(6)->startOfDay(),
            CarbonImmutable::now()->endOfDay(),
        );

        return Format::rupiah($summary['revenue']).' · '.$summary['orders'].' transaksi';
    }
}
