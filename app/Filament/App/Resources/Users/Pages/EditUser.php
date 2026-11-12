<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\UserResource;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\PermissionRegistrar;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected string $role = RoleDefinitions::CASHIER;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenant = app(TenantManager::class)->current();
        $data['role'] = $tenant !== null ? $this->record->roleIn($tenant) : null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->role = $data['role'] ?? RoleDefinitions::CASHIER;
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        $tenant = app(TenantManager::class)->current();

        if ($tenant === null) {
            return;
        }

        $this->record->tenants()->updateExistingPivot($tenant->getKey(), ['role' => $this->role]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
        $this->record->syncRoles([$this->role]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Simpan');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data staf berhasil disimpan.';
    }
}
