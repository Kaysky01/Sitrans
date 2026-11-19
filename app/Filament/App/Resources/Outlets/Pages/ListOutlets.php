<?php

namespace App\Filament\App\Resources\Outlets\Pages;

use App\Filament\App\Resources\Outlets\OutletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutlets extends ListRecords
{
    protected static string $resource = OutletResource::class;

    protected function getHeaderActions(): array
    {
        // Sembunyikan tombol tambah bagi role yang tidak dapat mengelola data ini.
        return static::$resource::canCreate() ? [
            CreateAction::make()
                ->label('Tambah Outlet')
                ->tooltip('Daftarkan cabang atau outlet baru.'),
        ] : [];
    }
}
