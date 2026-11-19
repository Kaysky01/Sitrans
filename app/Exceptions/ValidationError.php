<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException as BaseValidationException;

/**
 * ValidationException yang membawa kode error domain. Perilakunya sama
 * persis dengan ValidationException Laravel sehingga tetap tampil sebagai
 * error inline di Livewire/Blade, tetapi kode juga ikut ke response API.
 */
class ValidationError extends BaseValidationException
{
    public string $errorCode = 'VALIDASI_GAGAL';

    /**
     * Buat error validasi dengan kode domain, mis.
     * ValidationError::with('items', 'Stok Es Teh tersisa 2', 'STOK_TIDAK_CUKUP').
     */
    public static function with(string $key, string $message, string $code): static
    {
        /** @var static $exception */
        $exception = static::withMessages([$key => $message]);
        $exception->errorCode = $code;

        return $exception;
    }

    /**
     * Tandai ValidationException biasa dengan kode domain.
     */
    public static function from(BaseValidationException $exception, string $code): static
    {
        /** @var static $wrapped */
        $wrapped = static::withMessages($exception->errors() ?? []);
        $wrapped->errorCode = $code;

        return $wrapped;
    }
}
