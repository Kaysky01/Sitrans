<?php

namespace App\Modules\Pos\Enums;

enum DiscountType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Persen',
            self::Fixed => 'Nominal',
        };
    }
}
