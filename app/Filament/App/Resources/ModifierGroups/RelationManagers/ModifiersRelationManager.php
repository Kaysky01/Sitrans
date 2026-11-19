<?php

namespace App\Filament\App\Resources\ModifierGroups\RelationManagers;

use App\Support\Format;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModifiersRelationManager extends RelationManager
{
    protected static string $relationship = 'modifiers';

    protected static ?string $title = 'Pilihan modifier';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama opsi')->required()->maxLength(80)
                ->placeholder('Less sugar / Extra shot'),
            TextInput::make('price')->label('Tambahan harga')->numeric()->prefix('Rp')->default(0)->placeholder('5000')->helperText('Biaya tambahan untuk opsi ini (0 jika gratis).'),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->helperText('Angka kecil tampil lebih dulu.'),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Opsi')->searchable(),
                TextColumn::make('price')
                    ->label('Tambahan')
                    ->formatStateUsing(fn ($state): string => Format::rupiah($state)),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
