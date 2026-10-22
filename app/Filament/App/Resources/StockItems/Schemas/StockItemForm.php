<?php

namespace App\Filament\App\Resources\StockItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('product_name')
                ->label('Menu')
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(fn ($record): string => $record?->label() ?? ''),
            TextInput::make('quantity')
                ->label('Stok saat ini')
                ->numeric()
                ->disabled()
                ->dehydrated(false)
                ->helperText('Ubah jumlah stok melalui aksi "Penyesuaian" pada daftar.'),
            TextInput::make('reorder_level')
                ->label('Batas minimum')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->helperText('Daftar akan menandai menu ini ketika stok berada di bawah nilai ini.'),
        ]);
    }
}
