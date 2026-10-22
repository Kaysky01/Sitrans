<?php

namespace App\Filament\App\Resources\Categories\Tables;

use App\Filament\App\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto')->disk('public')->circular(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('products_count')->counts('products')->label('Jumlah menu'),
                TextColumn::make('sort_order')->label('Urutan')->numeric()->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Category $record): bool => CategoryResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Category $record): bool => CategoryResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => CategoryResource::canDeleteAny()),
                ]),
            ]);
    }
}
