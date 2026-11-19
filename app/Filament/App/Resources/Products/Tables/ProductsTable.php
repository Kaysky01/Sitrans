<?php

namespace App\Filament\App\Resources\Products\Tables;

use App\Filament\App\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\Format;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->disk(config('uploads.disk'))
                    ->defaultImageUrl(fn (Product $record): string => $record->imageUrl())
                    ->circular()
                    ->size(46),
                TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state): string => Format::rupiah($state))
                    ->weight('semibold')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->formatStateUsing(fn (Product $record): string => $record->unitLabel())
                    ->toggleable(),
                ToggleColumn::make('is_sold_out')->label('Habis'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('name'))
            ->reorderable(
                'sort_order',
                condition: fn (): bool => ProductResource::canCreate(),
            )
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->label($isReordering ? 'Selesai Mengurutkan' : 'Urutkan (Seret-Lepas)')
                    ->button()
                    ->color($isReordering ? 'success' : 'gray')
                    ->tooltip('Klik tombol ini, lalu seret baris ke atas/bawah untuk menyusun urutan menu di kasir.'),
            )
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')->label('Kategori'),
                TernaryFilter::make('is_sold_out')->label('Habis'),
                TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->recordActions([
                Action::make('generate_photo')
                    ->label('Foto Otomatis')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->tooltip('Pilih contoh foto bawaan berdasarkan nama menu (matcha, kopi, croissant, dan seterusnya).')
                    ->requiresConfirmation()
                    ->modalHeading('Gunakan foto contoh?')
                    ->modalDescription('Foto saat ini akan diganti dengan contoh foto yang cocok dengan nama menu. Unggah foto sendiri lewat Edit jika ingin hasil yang lebih tepat.')
                    ->modalSubmitActionLabel('Gunakan foto contoh')
                    ->visible(fn (Product $record): bool => ProductResource::canEdit($record))
                    ->action(function (Product $record): void {
                        $record->update(['image_path' => static::determinePhotoPath($record)]);

                        Notification::make()
                            ->title('Foto menu '.$record->name.' berhasil diperbarui')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->visible(fn (Product $record): bool => ProductResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Product $record): bool => ProductResource::canDelete($record)),
            ])
            ->toolbarActions([
                Action::make('generate_all_photos')
                    ->label('Foto Contoh (Menu Tanpa Foto)')
                    ->icon('heroicon-o-sparkles')
                    ->color('gray')
                    ->tooltip('Isi foto contoh bawaan hanya untuk menu yang belum memiliki foto.')
                    ->requiresConfirmation()
                    ->modalHeading('Isi foto contoh untuk menu tanpa foto?')
                    ->modalDescription('Menu yang belum memiliki foto akan dipasangkan foto contoh bawaan sesuai namanya. Menu yang sudah memiliki foto sendiri tidak akan diubah.')
                    ->modalSubmitActionLabel('Ya, pasang foto contoh')
                    ->visible(fn (): bool => ProductResource::canCreate())
                    ->action(function (): void {
                        $products = Product::query()->whereNull('image_path')->orWhere('image_path', '')->get();

                        if ($products->isEmpty()) {
                            Notification::make()
                                ->title('Semua menu sudah memiliki foto')
                                ->body('Tidak ada menu tanpa foto yang perlu diisi.')
                                ->info()
                                ->send();

                            return;
                        }

                        foreach ($products as $product) {
                            $product->update(['image_path' => static::determinePhotoPath($product)]);
                        }

                        Notification::make()
                            ->title('Foto contoh berhasil dipasang untuk '.$products->count().' menu')
                            ->success()
                            ->send();
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ProductResource::canDeleteAny()),
                ]),
            ]);
    }

    public static function determinePhotoPath(Product $product): string
    {
        $name = strtolower($product->name);

        if (str_contains($name, 'matcha')) {
            return 'products/matcha.jpg';
        }
        if (str_contains($name, 'croissant')) {
            return 'products/croissant.jpg';
        }
        if (str_contains($name, 'roti') || str_contains($name, 'toast') || str_contains($name, 'srikaya') || str_contains($name, 'pisang')) {
            return 'products/toast.jpg';
        }
        if (str_contains($name, 'kentang') || str_contains($name, 'fries')) {
            return 'products/fries.jpg';
        }
        if (str_contains($name, 'tea') || str_contains($name, 'teh')) {
            return 'products/tea.jpg';
        }
        if (str_contains($name, 'choco') || str_contains($name, 'cokelat')) {
            return 'products/chocolate.jpg';
        }

        return 'products/coffee.jpg';
    }
}
