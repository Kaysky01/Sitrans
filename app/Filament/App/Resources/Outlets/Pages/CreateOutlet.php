<?php

namespace App\Filament\App\Resources\Outlets\Pages;

use App\Filament\App\Resources\Outlets\OutletResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateOutlet extends CreateRecord
{
    protected static string $resource = OutletResource::class;

    protected static ?string $title = 'Tambah Outlet';

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Outlet berhasil disimpan.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Simpan & tambah lagi');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
