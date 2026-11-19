<?php

namespace App\Filament\App\Resources\PaymentMethods\Schemas;

use App\Modules\Pos\Enums\PaymentType;
use App\Support\Tenant\TenantManager;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama metode')
                ->required()
                ->maxLength(60)
                ->placeholder('Contoh: QRIS Toko, EDC BCA, Tunai')
                ->helperText('Nama tombol yang tampil di kasir. Buat terpisah jika ada beberapa bank atau mesin EDC berbeda.'),
            Select::make('type')
                ->label('Tipe')
                ->options(collect(PaymentType::cases())->mapWithKeys(
                    fn (PaymentType $type) => [$type->value => $type->label()]
                )->all())
                ->required()
                ->default(PaymentType::Cash->value)
                ->live()
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                    'requires_reference',
                    PaymentType::tryFrom((string) $state)?->requiresReference() ?? false,
                ))
                ->helperText('Tipe teknis pembayaran. Satu toko bisa punya beberapa rekening bank/EDC berbeda untuk tipe yang sama.'),
            TextInput::make('code')
                ->label('Kode internal')
                ->maxLength(30)
                ->placeholder('Kosongkan untuk dibuatkan otomatis')
                ->helperText('Kode unik sistem. Boleh dikosongkan — otomatis dibuat dari nama metode, mis. "EDC BCA" menjadi edc_bca.')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule): Unique => $rule
                        ->where('tenant_id', app(TenantManager::class)->current()?->getKey()),
                ),
            Toggle::make('requires_reference')
                ->label('Butuh nomor referensi')
                ->helperText('Aktifkan bila kasir wajib mengisi nomor referensi atau kode approval.')
                ->default(false),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0)
                ->helperText('Angka kecil tampil lebih dulu. Bisa juga diatur lewat seret-lepas di daftar metode bayar.'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->helperText('Nonaktifkan untuk menyembunyikan metode dari kasir tanpa menghapusnya.')
                ->default(true),
        ]);
    }
}
