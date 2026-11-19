<?php

namespace App\Filament\App\Resources\Bundles\Schemas;

use App\Support\Uploads\ImageUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama paket')->required()->maxLength(120),
            Select::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
            Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
            TextInput::make('price')->label('Harga paket')->numeric()->prefix('Rp')->required()->default(0)->placeholder('35000')->helperText('Harga paket promo, contoh: 35000.'),
            ImageUpload::make('image_path', 'bundles', 'Foto paket'),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }
}
