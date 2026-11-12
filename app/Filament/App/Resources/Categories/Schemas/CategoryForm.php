<?php

namespace App\Filament\App\Resources\Categories\Schemas;

use App\Support\Uploads\ImageUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama kategori')
                ->required()
                ->maxLength(100)
                ->helperText('Contoh: Kopi, Makanan Berat, Camilan. Urutan kategori menentukan urutan di kasir.'),
            ImageUpload::make('image_path', 'categories', 'Foto kategori'),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0)
                ->helperText('Angka kecil tampil lebih dulu. Bisa juga diatur lewat seret-lepas di daftar kategori.'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
