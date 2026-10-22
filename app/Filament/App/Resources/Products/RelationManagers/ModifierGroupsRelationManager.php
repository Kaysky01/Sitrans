<?php

namespace App\Filament\App\Resources\Products\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModifierGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'modifierGroups';

    protected static ?string $title = 'Grup modifier';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Grup')->searchable(),
                TextColumn::make('min_select')->label('Min'),
                TextColumn::make('max_select')->label('Maks'),
                IconColumn::make('is_required')->label('Wajib')->boolean(),
            ])
            ->headerActions([
                AttachAction::make()->label('Tambah grup')->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
