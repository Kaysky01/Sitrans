<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use App\Support\Format;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = Tenant::query()->count();
        $active = Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count();
        $trial = Tenant::query()
            ->where('status', Tenant::STATUS_TRIAL)
            ->where('trial_ends_at', '>', now())
            ->count();
        $suspended = Tenant::query()->where('status', Tenant::STATUS_SUSPENDED)->count();
        $expiring = Tenant::query()
            ->where('status', Tenant::STATUS_TRIAL)
            ->whereBetween('trial_ends_at', [now(), now()->addDays(3)])
            ->count();

        $estimatedMrr = $active * 149000;

        return [
            Stat::make('Total Kafe', (string) $total)
                ->description('Penyewa terdaftar di platform')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),
            Stat::make('Aktif Berlangganan', (string) $active)
                ->description('Estimasi MRR: '.Format::rupiah($estimatedMrr))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Masa Uji Coba (Trial)', (string) $trial)
                ->description($expiring > 0 ? $expiring.' berakhir dalam 3 hari' : 'Masa percobaan 14 hari')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Ditangguhkan', (string) $suspended)
                ->description($suspended > 0 ? 'Perlu tindak lanjut tagihan' : 'Semua akun aktif normal')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($suspended > 0 ? 'danger' : 'gray'),
        ];
    }
}
