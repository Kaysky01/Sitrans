<?php

namespace App\Filament\App\Resources\Categories\Schemas;

use App\Models\Category;
use App\Support\Tenant\TenantManager;
use App\Support\Uploads\ImageUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama kategori')
                ->required()
                ->maxLength(100)
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule): Unique => $rule
                        ->where('tenant_id', app(TenantManager::class)->current()?->getKey()),
                )
                ->validationMessages([
                    'unique' => 'Nama kategori sudah dipakai. Pilih nama lain.',
                ])
                ->helperText('Contoh: Kopi, Makanan Berat, Camilan. Urutan kategori menentukan urutan tab di layar kasir.'),
            ImageUpload::make('image_path', 'categories', 'Foto kategori'),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(function (): int {
                    $tenantId = app(TenantManager::class)->current()?->getKey();

                    return (int) ((Category::query()->where('tenant_id', $tenantId)->max('sort_order') ?? 0) + 1);
                })
                ->helperText('Nomor urut tampil di kasir (angka kecil tampil di sebelah kiri). Bisa juga diatur lewat seret-lepas (drag & drop) di tabel kategori.'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
