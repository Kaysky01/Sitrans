<?php

namespace App\Filament\App\Resources\Users\Tables;

use App\Filament\App\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Tenant\TenantManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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

                        return $tenant !== null ? (string) $record->roleIn($tenant) : '-';
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
                    ->visible(fn (User $record): bool => UserResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => UserResource::canDeleteAny()),
                ]),
            ]);
    }
}
