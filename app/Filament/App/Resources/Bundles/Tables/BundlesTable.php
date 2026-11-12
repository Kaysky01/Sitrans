<?php

namespace App\Filament\App\Resources\Bundles\Tables;

use App\Filament\App\Resources\Bundles\BundleResource;
use App\Models\Bundle;
use App\Support\Format;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BundlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto')->disk(config('uploads.disk'))->square()->height(40),
                TextColumn::make('name')->label('Paket')->searchable()->weight('semibold'),
                TextColumn::make('items_count')->counts('items')->label('Isi'),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state): string => Format::rupiah($state)),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Bundle $record): bool => BundleResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Bundle $record): bool => BundleResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => BundleResource::canDeleteAny()),
                ]),
            ]);
    }
}
