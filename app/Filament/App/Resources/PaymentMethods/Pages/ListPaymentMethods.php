<?php

namespace App\Filament\App\Resources\PaymentMethods\Pages;

use App\Filament\App\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        // Sembunyikan tombol tambah bagi role yang tidak dapat mengelola data ini.
        return static::$resource::canCreate() ? [
            CreateAction::make()
                ->label('Tambah Metode')
                ->tooltip('Tambahkan metode pembayaran baru. Kode boleh dikosongkan dan dibuat otomatis.'),
        ] : [];
    }
}
