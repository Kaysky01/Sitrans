<?php

namespace App\Support\Catalog;

use App\Models\Payment;
use App\Models\PaymentMethod;

/**
 * Aturan penghapusan metode pembayaran bersama: metode yang sudah dipakai di
 * transaksi tidak boleh dihapus, cukup dinonaktifkan.
 */
final class PaymentMethodDeletionGuard
{
    /**
     * @param  iterable<PaymentMethod>  $methods
     * @return string|null Pesan penolakan (bahasa Indonesia) atau null jika aman.
     */
    public static function rejectReason(iterable $methods): ?string
    {
        $usedIn = (int) Payment::query()
            ->whereIn('payment_method_id', collect($methods)->pluck('id'))
            ->count();

        if ($usedIn > 0) {
            return "Metode ini sudah dipakai di {$usedIn} transaksi. Nonaktifkan saja agar tidak muncul di kasir.";
        }

        return null;
    }
}
