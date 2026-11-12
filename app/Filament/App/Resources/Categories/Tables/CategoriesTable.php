<?php

namespace App\Filament\App\Resources\Categories\Tables;

use App\Filament\App\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto')->disk(config('uploads.disk'))->circular(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('products_count')->counts('products')->label('Jumlah menu'),
                TextColumn::make('sort_order')->label('Urutan')->numeric()->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('name'))
            ->reorderable(
                'sort_order',
                condition: fn (): bool => CategoryResource::canCreate(),
            )
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->label($isReordering ? 'Selesai Mengurutkan' : 'Urutkan Kategori')
                    ->tooltip('Aktifkan lalu seret baris untuk mengatur urutan kategori yang tampil di POS.'),
            )
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
