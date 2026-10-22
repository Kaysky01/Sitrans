<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        // Sembunyikan tombol tambah bagi role yang tidak dapat mengelola data ini.
        return static::$resource::canCreate() ? [CreateAction::make()] : [];
    }
}
