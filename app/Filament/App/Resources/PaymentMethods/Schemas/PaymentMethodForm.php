<?php

namespace App\Filament\App\Resources\PaymentMethods\Schemas;

use App\Modules\Pos\Enums\PaymentType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->required()->maxLength(60),
            TextInput::make('code')->label('Kode')->required()->maxLength(30)
                ->helperText('Huruf kecil tanpa spasi, mis. cash, qris.'),
            Select::make('type')
                ->label('Tipe')
                ->options(collect(PaymentType::cases())->mapWithKeys(
                    fn (PaymentType $type) => [$type->value => $type->label()]
                )->all())
                ->required()
                ->default(PaymentType::Cash->value)
                ->live(),
            Toggle::make('requires_reference')
                ->label('Butuh nomor referensi')
                ->default(false),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }
}
