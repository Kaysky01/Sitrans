<?php

namespace App\Filament\App\Resources\Products\Schemas;

use App\Models\Product;
use App\Support\Format;
use App\Support\Tenant\TenantManager;
use App\Support\Uploads\ImageUpload;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->native(false)
                ->helperText('Kelompokkan menu agar mudah dicari di kasir. Kelola daftar kategori di halaman Kategori.'),
            TextInput::make('name')
                ->label('Nama menu')
                ->required()
                ->minLength(2)
                ->maxLength(120)
                ->rule(function (Get $get, ?Product $record): Closure {
                    return static function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                        $categoryId = $get('category_id');

                        if (blank($value) || blank($categoryId)) {
                            return;
                        }

                        $duplicate = Product::query()
                            ->where('category_id', $categoryId)
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) $value)])
                            ->when($record?->exists, fn ($query) => $query->whereKeyNot($record->getKey()))
                            ->first();

                        if ($duplicate !== null) {
                            $fail("Peringatan: sudah ada menu \"{$duplicate->name}\" di kategori ini. Gunakan nama lain agar tidak membingungkan kasir.");
                        }
                    };
                })
                ->helperText('Nama yang tampil di kasir, struk, dan menu digital. Minimal 2 karakter.'),
            TextInput::make('sku')
                ->label('SKU (Kode Barcode / Menu)')
                ->maxLength(50)
                ->placeholder('Kosongkan untuk dibuatkan otomatis')
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule): Unique => $rule
                        ->where('tenant_id', app(TenantManager::class)->current()?->getKey()),
                )
                ->validationMessages([
                    'unique' => 'SKU sudah dipakai produk lain.',
                ])
                ->helperText('Kode unik barang untuk pencarian barcode atau gudang. Jika dikosongkan, SKU dibuat otomatis dari nama menu, mis. KOPI-SUSU.'),
            Textarea::make('description')
                ->label('Deskripsi')
                ->required()
                ->minLength(10)
                ->rows(3)
                ->maxLength(500)
                ->live(debounce: 400)
                ->helperText(fn (Get $get): string => mb_strlen((string) $get('description')).'/500 karakter. Minimal 10 karakter. Jelaskan bahan utama atau porsi singkat.')
                ->columnSpanFull(),
            TextInput::make('price')
                ->label('Harga jual')
                ->prefix('Rp')
                ->required()
                ->default(0)
                ->inputMode('numeric')
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, mixed $state): void {
                    $set('price', self::formatRupiahInput($state));
                })
                ->formatStateUsing(fn (mixed $state): string => self::formatRupiahInput($state))
                ->dehydrateStateUsing(fn (mixed $state): float => self::parseRupiahInput($state))
                ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                    if (self::parseRupiahInput($value) <= 0) {
                        $fail('Harga harus lebih dari 0.');
                    }
                })
                ->helperText('Harga sebelum pajak & diskon. Contoh tampilan: Rp 25.000 (pemisah ribuan diformat otomatis).'),
            TextInput::make('cost')
                ->label('HPP rata-rata')
                ->prefix('Rp')
                ->default(0)
                ->disabled()
                ->formatStateUsing(fn (mixed $state): string => self::formatRupiahInput($state))
                ->hintIcon('heroicon-o-question-mark-circle', tooltip: 'HPP (Harga Pokok Penjualan) adalah modal bahan per porsi. Nilainya dihitung otomatis dari resep dan pembelian bahan mentah.')
                ->helperText('Modal bahan per porsi. Dihitung otomatis dari resep & pembelian bahan mentah.'),
            ImageUpload::make('image_path', 'products', 'Foto menu', required: true),
            Select::make('unit')
                ->label('Satuan stok')
                ->options(Product::units())
                ->default('porsi')
                ->required()
                ->selectablePlaceholder(false)
                ->live()
                ->helperText(fn (Get $get): string => in_array($get('unit'), ['kg', 'gram', 'liter', 'ml'], true)
                    ? 'Satuan bahan mentah / timbangan (bisa pecahan/desimal, mis. 2,5 kg kopi atau 1,5 liter sirup). Tampil dengan 2 desimal.'
                    : 'Satuan menu siap saji / porsi (selalu bilangan bulat, mis. 12 porsi atau 5 gelas). Tampil tanpa desimal.'),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(function (): int {
                    $tenantId = app(TenantManager::class)->current()?->getKey();

                    return (int) ((Product::query()->where('tenant_id', $tenantId)->max('sort_order') ?? 0) + 1);
                })
                ->helperText('Nomor urut tampil di kasir (angka kecil tampil lebih dulu). Bisa juga diatur seret-lepas (drag & drop) di tabel menu.'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->helperText('Nonaktifkan untuk menyembunyikan menu dari kasir tanpa menghapusnya.'),
            Toggle::make('is_sold_out')
                ->label('Habis')
                ->helperText('Tandai menu sebagai habis tanpa menonaktifkannya.'),
            Toggle::make('track_stock')
                ->label('Lacak stok')
                ->helperText('Stok berkurang otomatis setiap penjualan dan menu ditandai habis saat stok nol.'),
        ]);
    }

    /**
     * Tampilkan nominal dengan pemisah ribuan tanpa mengubah nilai simpanan.
     */
    public static function formatRupiahInput(mixed $state): string
    {
        return Format::rupiah(self::parseRupiahInput($state), withSymbol: false);
    }

    public static function parseRupiahInput(mixed $state): float
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $state);

        return (float) ($digits === '' ? 0 : $digits);
    }
}
