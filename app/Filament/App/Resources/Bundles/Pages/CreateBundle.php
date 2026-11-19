<?php

namespace App\Filament\App\Resources\Bundles\Pages;

use App\Filament\App\Resources\Bundles\BundleResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateBundle extends CreateRecord
{
    protected static string $resource = BundleResource::class;

    protected static ?string $title = 'Tambah Paket Promo';

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Paket promo berhasil disimpan.';
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
