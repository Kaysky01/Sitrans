<?php

namespace App\Exceptions;

/**
 * Pengguna tidak punya izin untuk aksi ini. Jangan membocorkan struktur data.
 */
class ForbiddenError extends AppError
{
    public string $errorCode = 'TANPA_IZIN';

    public int $httpStatus = 403;

    protected function defaultMessage(): string
    {
        return 'Kamu tidak punya izin untuk aksi ini.';
    }
}
