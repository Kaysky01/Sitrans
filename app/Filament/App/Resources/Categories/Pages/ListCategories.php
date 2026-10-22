<?php

namespace App\Filament\App\Resources\Categories\Pages;

use App\Filament\App\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        // Sembunyikan tombol tambah bagi role yang tidak dapat mengelola data ini.
        return static::$resource::canCreate() ? [CreateAction::make()] : [];
    }
}
