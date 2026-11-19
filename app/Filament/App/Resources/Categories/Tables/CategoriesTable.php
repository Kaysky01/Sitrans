<?php

namespace App\Filament\App\Resources\Categories\Tables;

use App\Filament\App\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Support\Catalog\CategoryDeletionGuard;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
                    ->label($isReordering ? 'Selesai Mengurutkan' : 'Urutkan (Seret-Lepas)')
                    ->button()
                    ->color($isReordering ? 'success' : 'gray')
                    ->tooltip('Klik tombol ini, lalu seret baris ke atas/bawah untuk menyusun urutan tampilan di kasir.'),
            )
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Category $record): bool => CategoryResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Category $record): bool => CategoryResource::canDelete($record))
                    ->before(function (Category $record, Action $action): void {
                        $reason = CategoryDeletionGuard::rejectReason([$record]);

                        if ($reason !== null) {
                            Notification::make()
                                ->title('Kategori tidak bisa dihapus')
                                ->body($reason)
                                ->warning()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => CategoryResource::canDeleteAny())
                        ->before(function (Collection $records, Action $action): void {
                            $reason = CategoryDeletionGuard::rejectReason($records);

                            if ($reason !== null) {
                                Notification::make()
                                    ->title('Beberapa kategori tidak bisa dihapus')
                                    ->body($reason)
                                    ->warning()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
