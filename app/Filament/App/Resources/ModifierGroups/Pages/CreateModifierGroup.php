<?php

namespace App\Filament\App\Resources\ModifierGroups\Pages;

use App\Filament\App\Resources\ModifierGroups\ModifierGroupResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateModifierGroup extends CreateRecord
{
    protected static string $resource = ModifierGroupResource::class;

    protected static ?string $title = 'Tambah Grup Opsi';

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Grup opsi berhasil disimpan.';
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
