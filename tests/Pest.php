<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Lengkapi detail pesanan kasir (tipe, nama pelanggan, nomor meja).
 * Item boleh masuk keranjang lebih dulu; detail divalidasi saat pembayaran.
 */
function startCashierOrder(Testable $component): Testable
{
    return $component
        ->set('orderType', 'dine_in')
        ->set('customerName', 'Pelanggan Uji')
        ->set('tableNumber', '01');
}
