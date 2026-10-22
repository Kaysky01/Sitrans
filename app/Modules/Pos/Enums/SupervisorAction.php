<?php

namespace App\Modules\Pos\Enums;

enum SupervisorAction: string
{
    case Void = 'void';
    case Refund = 'refund';
    case PriceOverride = 'price_override';

    public function label(): string
    {
        return match ($this) {
            self::Void => 'Void transaksi',
            self::Refund => 'Refund',
            self::PriceOverride => 'Ubah harga',
        };
    }
}
