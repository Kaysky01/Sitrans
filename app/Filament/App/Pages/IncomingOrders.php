<?php

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Menu sidebar "Pesanan Masuk". Isi papan kerja ada di komponen Livewire
 * tersendiri agar dapat dipakai ulang dan diuji secara terpisah.
 */
class IncomingOrders extends Page
{
    protected string $view = 'filament.app.pages.incoming-orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pesanan Masuk';

    protected static ?string $title = 'Pesanan Masuk';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('orders.view') ?? false;
    }
}
