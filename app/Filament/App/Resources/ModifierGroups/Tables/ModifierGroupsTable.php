<?php

namespace App\Filament\App\Resources\ModifierGroups\Tables;

use App\Filament\App\Resources\ModifierGroups\ModifierGroupResource;
use App\Models\ModifierGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModifierGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Grup')->searchable()->weight('semibold'),
                TextColumn::make('modifiers_count')->counts('modifiers')->label('Jumlah opsi'),
                TextColumn::make('min_select')->label('Min'),
                TextColumn::make('max_select')->label('Maks'),
                IconColumn::make('is_required')->label('Wajib')->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (ModifierGroup $record): bool => ModifierGroupResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (ModifierGroup $record): bool => ModifierGroupResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ModifierGroupResource::canDeleteAny()),
                ]),
            ]);
    }
}
