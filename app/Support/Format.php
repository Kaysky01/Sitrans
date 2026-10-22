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
}
