<?php

namespace App\Filament\App\Resources\Products\Schemas;

use App\Models\Product;
use App\Support\Format;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                ->preload()
                ->helperText('Kelompokkan menu agar mudah dicari di kasir. Kelola daftar kategori di halaman Kategori.'),
            TextInput::make('name')
                ->label('Nama menu')
                ->required()
                ->maxLength(120)
                ->helperText('Nama yang tampil di kasir, struk, dan menu digital.'),
            TextInput::make('sku')
                ->label('SKU')
                ->maxLength(50)
                ->placeholder('Kosongkan untuk otomatis')
                ->helperText('Opsional. Jika dikosongkan, SKU dibuat otomatis dari nama menu, mis. KOPI-SUSU.'),
            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->maxLength(500)
                ->live(debounce: 400)
                ->helperText(fn (Get $get): string => mb_strlen((string) $get('description')).'/500 karakter. Jelaskan bahan utama atau porsi singkat.')
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
                    if (self::parseRupiahInput($value) < 0) {
                        $fail('Harga jual tidak boleh negatif.');
                    }
                })
                ->helperText('Harga sebelum pajak dan diskon. Contoh tampilan: Rp 25.000.'),
            TextInput::make('cost')
                ->label('HPP rata-rata')
                ->prefix('Rp')
                ->default(0)
                ->disabled()
                ->formatStateUsing(fn (mixed $state): string => self::formatRupiahInput($state))
                ->hintIcon('heroicon-o-question-mark-circle', tooltip: 'HPP (Harga Pokok Penjualan) adalah modal bahan per porsi. Nilainya dihitung otomatis dari resep dan pembelian bahan, jadi tidak diisi manual di sini.')
                ->helperText('Dihitung otomatis dari resep & pembelian bahan.'),
            FileUpload::make('image_path')
                ->label('Foto menu')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('products')
                ->required()
                ->helperText('Unggah foto sendiri, atau gunakan tombol "Auto Foto" di daftar menu untuk memilih contoh foto berdasarkan nama menu.')
                ->columnSpanFull(),
            Select::make('unit')
                ->label('Satuan stok')
                ->options(Product::units())
                ->default('porsi')
                ->required()
                ->selectablePlaceholder(false)
                ->live()
                ->helperText(fn (Get $get): string => in_array($get('unit'), ['kg', 'gram', 'liter', 'ml'], true)
                    ? 'Satuan ini boleh pecahan, mis. 2,5 kg. Stok tampil dengan 2 angka desimal.'
                    : 'Satuan ini dihitung bulat, mis. 12 porsi. Stok tampil tanpa desimal.'),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0)
                ->helperText('Angka kecil tampil lebih dulu. Bisa juga diatur lewat seret-lepas di daftar menu.'),
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
