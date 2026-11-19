<?php

namespace App\Modules\Pos\Enums;

enum OrderStatus: string
{
    case Open = 'open';
    case Paid = 'paid';
    case Completed = 'completed';
    case Voided = 'voided';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Belum dibayar',
            self::Paid => 'Dibayar',
            self::Completed => 'Selesai',
            self::Voided => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }

    /**
     * Statuses that count as realised revenue.
     *
     * @return array<int, self>
     */
    public static function revenueBearing(): array
    {
        return [self::Paid, self::Completed];
    }
}
