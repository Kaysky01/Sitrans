<?php

namespace App\Filament\App\Resources\Products\RelationManagers;

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

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variasi ukuran';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama variasi')->required()->maxLength(60)
                ->placeholder('Regular / Large'),
            TextInput::make('price')->label('Harga')->numeric()->prefix('Rp')->default(0)->placeholder('25000')->helperText('Harga varian ini.'),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->helperText('Angka kecil tampil lebih dulu.'),
            Toggle::make('is_default')->label('Default'),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Variasi')->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state): string => Format::rupiah($state)),
                IconColumn::make('is_default')->label('Default')->boolean(),
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
