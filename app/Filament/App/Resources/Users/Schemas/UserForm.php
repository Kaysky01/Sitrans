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
            TextInput::make('name')->label('Nama')->required()->maxLength(100),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(150)
                ->unique(ignoreRecord: true),
            TextInput::make('phone')->label('No. HP')->tel()->maxLength(30),
            Select::make('role')
                ->label('Peran')
                ->options([
                    RoleDefinitions::OWNER => 'Pemilik',
                    RoleDefinitions::MANAGER => 'Manajer',
                    RoleDefinitions::CASHIER => 'Kasir',
                    RoleDefinitions::KITCHEN => 'Dapur',
                ])
                ->default(RoleDefinitions::CASHIER)
                ->required()
                ->helperText('Perubahan peran berlaku pada kafe ini.'),
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
                ->maxLength(6)
                ->helperText('4-6 digit, untuk void/refund. Kosongkan bila tidak diperlukan.')
                ->dehydrated(fn (?string $state): bool => filled($state)),
        ]);
    }
}
