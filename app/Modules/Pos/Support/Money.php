<?php

namespace App\Modules\Pos\Support;

/**
 * Tiny money helper. Amounts are stored as decimal(15,2); all arithmetic is
 * rounded back to 2 places at every step to keep totals consistent.
 */
final class Money
{
    public static function round(float|int|string $value): float
    {
        return round((float) $value, 2);
    }
}
