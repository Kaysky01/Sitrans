<?php

namespace App\Filament\App\Resources\ModifierGroups\Tables;

use App\Filament\App\Resources\ModifierGroups\ModifierGroupResource;
use App\Models\ModifierGroup;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextColumn::make('sort_order')->label('Urutan')->numeric()->toggleable(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('name'))
            ->reorderable(
                'sort_order',
                condition: fn (): bool => ModifierGroupResource::canCreate(),
            )
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->label($isReordering ? 'Selesai Mengurutkan' : 'Urutkan Grup')
                    ->tooltip('Aktifkan lalu seret baris untuk mengatur urutan grup opsi di POS.'),
            )
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
