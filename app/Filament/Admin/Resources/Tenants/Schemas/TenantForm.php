<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama kafe')
                ->required()
                ->maxLength(120),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(150),
            TextInput::make('phone')
                ->label('Telepon')
                ->tel()
                ->maxLength(30),
            Select::make('status')
                ->label('Status')
                ->options([
                    Tenant::STATUS_TRIAL => 'Uji coba',
                    Tenant::STATUS_ACTIVE => 'Aktif',
                    Tenant::STATUS_PAST_DUE => 'Menunggu pembayaran',
                    Tenant::STATUS_SUSPENDED => 'Ditangguhkan',
                ])
                ->default(Tenant::STATUS_TRIAL)
                ->required(),
            DateTimePicker::make('trial_ends_at')
                ->label('Akhir uji coba')
                ->seconds(false)
                ->default(now()->addDays(config('sitrans.trial_days'))),

            TextInput::make('owner_name')
                ->label('Nama pemilik')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->visibleOn('create')
                ->maxLength(120),
            TextInput::make('owner_email')
                ->label('Email pemilik')
                ->email()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->visibleOn('create')
                ->maxLength(150),
            TextInput::make('owner_password')
                ->label('Kata sandi pemilik')
                ->password()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->visibleOn('create')
                ->minLength(8)
                ->maxLength(60),
        ]);
    }
}
