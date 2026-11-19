<?php

namespace App\Exceptions;

/**
 * Batas percobaan terlampaui, mis. PIN supervisor salah berkali-kali.
 * Pesan disarankan menyebut sisa waktu tunggu.
 */
class RateLimitedError extends AppError
{
    public string $errorCode = 'TERLALU_BANYAK_PERCOBAAN';

    public int $httpStatus = 429;

    protected function defaultMessage(): string
    {
        return 'Terlalu banyak percobaan. Tunggu beberapa menit lalu coba lagi.';
    }
}
