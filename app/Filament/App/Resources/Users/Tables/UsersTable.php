<?php

namespace App\Filament\App\Resources\Users\Tables;

use App\Filament\App\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Access\RoleDefinitions;
use App\Support\Access\UserDeletionGuard;
use App\Support\Tenant\TenantManager;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('No. HP'),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->state(function (User $record): string {
                        $tenant = app(TenantManager::class)->current();
                        $role = $tenant !== null ? $record->roleIn($tenant) : null;

                        return $role !== null ? RoleDefinitions::label($role) : '-';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Pemilik' => 'warning',
                        'Supervisor' => 'info',
                        'Kasir' => 'success',
                        default => 'gray',
                    })
                    ->description(function (User $record): ?string {
                        $tenant = app(TenantManager::class)->current();

                        return $tenant !== null && $record->roleIn($tenant) === RoleDefinitions::OWNER
                            ? 'Akun Pemilik Utama'
                            : null;
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->state(function (User $record): bool {
                        $tenant = app(TenantManager::class)->current();

                        return $tenant !== null
                            && (bool) $record->tenants()->whereKey($tenant->getKey())->first()?->pivot->is_active;
                    })
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (User $record): bool => UserResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => UserResource::canDelete($record))
                    ->requiresConfirmation()
                    ->modalDescription(fn (User $record): string => 'Akun '.$record->name.' tidak akan bisa login lagi. Riwayat transaksinya tetap tersimpan.')
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => UserResource::canDeleteAny())
                        ->before(function (Collection $records, BulkAction $action): void {
                            $reason = UserDeletionGuard::rejectReason($records);

                            if ($reason !== null) {
                                Notification::make()
                                    ->title('Beberapa akun tidak bisa dihapus')
                                    ->body($reason)
                                    ->warning()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
