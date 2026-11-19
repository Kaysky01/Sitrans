<?php

namespace App\Exceptions;

use Exception;

/**
 * Base class untuk semua error domain aplikasi. Pesan yang dibawa selalu
 * berbahasa Indonesia dan aman ditampilkan langsung ke pengguna — detail
 * teknis (stack trace, kode internal) tetap hanya masuk ke log server.
 */
class AppError extends Exception
{
    /**
     * Kode error yang stabil untuk dikonsumsi klien API, mis. STOK_TIDAK_CUKUP.
     */
    public string $errorCode = 'APP_ERROR';

    /**
     * Status HTTP yang sesuai untuk error ini.
     */
    public int $httpStatus = 500;

    /**
     * Wajib isi: field bermasalah, mis. 'items' atau 'amountPaid'.
     */
    public ?string $field = null;

    /**
     * @param  string|null  $message  Pesan ramah bahasa Indonesia. Bila null,
     *                                pesan generik aman pengguna dipakai.
     */
    public function __construct(?string $message = null, ?string $field = null, ?string $errorCode = null)
    {
        parent::__construct($message ?? $this->defaultMessage(), $this->httpStatus);

        $this->field = $field;

        if ($errorCode !== null) {
            $this->errorCode = $errorCode;
        }
    }

    /**
     * Pesan generik yang tetap ramah bila pesan spesifik tidak tersedia.
     */
    protected function defaultMessage(): string
    {
        return 'Terjadi kesalahan yang tidak terduga. Coba beberapa saat lagi.';
    }

    /**
     * Nama field default bila pesan ingin dirender sebagai error inline.
     */
    public function errors(): array
    {
        if ($this->field === null) {
            return [];
        }

        return [$this->field => $this->getMessage()];
    }
}
