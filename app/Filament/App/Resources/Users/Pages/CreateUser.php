<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\UserResource;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected string $role = RoleDefinitions::CASHIER;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->role = $data['role'] ?? RoleDefinitions::CASHIER;
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $tenant = app(TenantManager::class)->current();

        if ($tenant === null) {
            return;
        }

        $this->record->tenants()->syncWithoutDetaching([
            $tenant->getKey() => ['role' => $this->role, 'is_active' => true, 'joined_at' => now()],
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
        $this->record->syncRoles([$this->role]);
    }
}
