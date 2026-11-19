<?php

namespace App\Filament\App\Resources\ModifierGroups\Pages;

use App\Filament\App\Resources\ModifierGroups\ModifierGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModifierGroups extends ListRecords
{
    protected static string $resource = ModifierGroupResource::class;

    protected function getHeaderActions(): array
    {
        // Sembunyikan tombol tambah bagi role yang tidak dapat mengelola data ini.
        return static::$resource::canCreate() ? [
            CreateAction::make()
                ->label('Tambah Grup Opsi')
                ->tooltip('Buat grup opsi tambahan untuk menu (mis. Level Gula, Topping, Ukuran Cup).'),
        ] : [];
    }
}
