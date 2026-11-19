<?php

namespace App\Exceptions;

/**
 * Aksi bertabrakan dengan state terkini, mis. order sudah dibayar dua kali
 * atau transisi status tidak valid. Umumnya aman dicoba ulang setelah
 * memuat data terbaru.
 */
class ConflictError extends AppError
{
    public string $errorCode = 'KONFLIK_STATUS';

    public int $httpStatus = 409;

    protected function defaultMessage(): string
    {
        return 'Perubahan tidak bisa diproses karena data sudah berubah. Muat ulang halaman lalu coba lagi.';
    }
}
