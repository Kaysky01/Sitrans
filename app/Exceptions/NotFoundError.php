<?php

namespace App\Exceptions;

/**
 * Data yang diminta tidak ada. Pesan default ramah, tanpa detail teknis.
 */
class NotFoundError extends AppError
{
    public string $errorCode = 'TIDAK_DITEMUKAN';

    public int $httpStatus = 404;

    protected function defaultMessage(): string
    {
        return 'Data yang kamu cari tidak ditemukan. Mungkin sudah dihapus atau tidak pernah ada.';
    }
}
