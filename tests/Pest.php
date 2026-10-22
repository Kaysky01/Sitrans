<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Mulai pesanan di kasir: tipe pesanan + nama pelanggan wajib diisi
 * sebelum item pertama dapat ditambahkan ke keranjang.
 */
function startCashierOrder(Testable $component): Testable
{
    return $component
        ->set('orderType', 'dine_in')
        ->set('customerName', 'Pelanggan Uji')
        ->set('tableNumber', '01');
}
