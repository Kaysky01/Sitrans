<?php

namespace App\Filament\App\Resources\Outlets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OutletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama outlet')->required()->maxLength(100),
            TextInput::make('code')->label('Kode')->maxLength(20)->placeholder('MKM'),
            TextInput::make('phone')->label('Telepon')->tel()->maxLength(30),
            TextInput::make('address')->label('Alamat')->maxLength(255)->columnSpanFull(),
            Select::make('timezone')
                ->label('Zona waktu')
                ->options([
                    'Asia/Jakarta' => 'WIB (Asia/Jakarta)',
                    'Asia/Makassar' => 'WITA (Asia/Makassar)',
                    'Asia/Jayapura' => 'WIT (Asia/Jayapura)',
                ])
                ->default('Asia/Jakarta')
                ->required(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }
}
