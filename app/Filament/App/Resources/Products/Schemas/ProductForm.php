<?php

namespace App\Filament\App\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('name')
                ->label('Nama menu')
                ->required()
                ->maxLength(120),
            TextInput::make('sku')
                ->label('SKU')
                ->maxLength(50),
            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->columnSpanFull(),
            TextInput::make('price')
                ->label('Harga jual')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->default(0),
            TextInput::make('cost')
                ->label('HPP rata-rata')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->disabled()
                ->dehydrated()
                ->helperText('Diperbarui otomatis dari resep & pembelian bahan.'),
            FileUpload::make('image_path')
                ->label('Foto menu')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('products')
                ->required()
                ->helperText('Wajib diisi agar menu tampil menarik di kasir dan menu digital.')
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
            Toggle::make('is_sold_out')
                ->label('Habis')
                ->helperText('Tandai menu sebagai habis tanpa menonaktifkannya.'),
            Toggle::make('track_stock')
                ->label('Lacak stok')
                ->helperText('Stok berkurang otomatis setiap penjualan dan menu ditandai habis saat stok nol.'),
        ]);
    }
}
