<?php

namespace App\Filament\App\Resources\Users\Schemas;

use App\Support\Access\RoleDefinitions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(100)
                ->helperText('Akun staf untuk kasir, dapur, atau supervisor. Akun pemilik tidak dibuat dari halaman ini.'),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(150)
                ->unique(ignoreRecord: true),
            TextInput::make('phone')->label('No. HP')->tel()->maxLength(30),
            Select::make('role')
                ->label('Peran')
                ->options(collect(RoleDefinitions::labels())
                    ->except(RoleDefinitions::OWNER)
                    ->all())
                ->default(RoleDefinitions::CASHIER)
                ->required()
                ->helperText('Supervisor dapat membuka laporan dan mengelola menu; kasir fokus pada transaksi.'),
            TextInput::make('password')
                ->label('Kata sandi')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(8),
            TextInput::make('pin')
                ->label('PIN supervisor')
                ->numeric()
                ->minLength(4)
                ->maxLength(6)
                ->helperText('PIN 4-6 digit untuk menyetujui void, refund, atau diskon di kasir. Hanya supervisor dan pemilik yang perlu PIN.')
                ->dehydrated(fn (?string $state): bool => filled($state)),
        ]);
    }
}
