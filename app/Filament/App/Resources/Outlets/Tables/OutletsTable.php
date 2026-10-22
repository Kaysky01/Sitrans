<?php

namespace App\Filament\App\Resources\Outlets\Tables;

use App\Filament\App\Resources\Outlets\OutletResource;
use App\Models\Outlet;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OutletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Outlet')->searchable()->weight('semibold'),
                TextColumn::make('code')->label('Kode')->badge(),
                TextColumn::make('phone')->label('Telepon'),
                TextColumn::make('address')->label('Alamat')->limit(40)->toggleable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Outlet $record): bool => OutletResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Outlet $record): bool => OutletResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => OutletResource::canDeleteAny()),
                ]),
            ]);
    }
}
