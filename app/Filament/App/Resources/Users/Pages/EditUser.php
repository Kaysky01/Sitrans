<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Access\RoleDefinitions;
use App\Support\Access\UserDeletionGuard;
use App\Support\Tenant\TenantManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
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

        $tenant = app(TenantManager::class)->current();

        // Peran Owner tidak pernah bisa dipilih lewat API/payload manual.
        if ($this->role === RoleDefinitions::OWNER) {
            throw ValidationException::withMessages([
                'role' => 'Peran Owner tidak bisa dipilih. Owner adalah pemilik akun utama kafe.',
            ]);
        }

        // Owner tidak bisa menurunkan perannya sendiri lewat halaman ini.
        if ($tenant !== null
            && $this->record->getKey() === auth()->id()
            && $this->record->roleIn($tenant) === RoleDefinitions::OWNER) {
            throw ValidationException::withMessages([
                'role' => 'Kamu tidak bisa menurunkan peran akunmu sendiri. Minta Owner lain yang mengubahnya.',
            ]);
        }

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
            DeleteAction::make()
                ->before(function (User $record, Action $action): void {
                    $reason = UserDeletionGuard::rejectReason([$record]);

                    if ($reason !== null) {
                        Notification::make()
                            ->title('Akun tidak bisa dihapus')
                            ->body($reason)
                            ->warning()
                            ->send();

                        $action->halt();
                    }
                }),
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
