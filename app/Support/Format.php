<?php

namespace App\Support;

final class Format
{
    /**
     * Rupiah without decimals, e.g. "Rp 25.000".
     */
    public static function rupiah(float|int|string|null $value, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $value, 0, ',', '.');

        return $withSymbol ? 'Rp '.$formatted : $formatted;
    }

    /**
     * Jumlah stok tanpa nol yang tidak perlu, mis. "12" atau "2,5".
     */
    public static function quantity(float|int|string|null $value, int $decimals = 0): string
    {
        $formatted = number_format((float) $value, $decimals, ',', '.');

        if ($decimals > 0) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        return $formatted === '' ? '0' : $formatted;
    }
}
