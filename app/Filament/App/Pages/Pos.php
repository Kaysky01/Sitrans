<?php

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Sidebar entry pointing at the POS screen, which lives outside the Filament
 * panel because it uses its own kiosk layout. Mounting this page simply
 * forwards the user to /pos, so cashiers always have a "start here" item.
 */
class Pos extends Page
{
    protected string $view = 'filament.app.pages.pos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Kasir (POS)';

    protected static ?string $title = 'Kasir';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('pos.use') ?? false;
    }

    public function mount(): void
    {
        $this->redirect(route('pos'));
    }
}
