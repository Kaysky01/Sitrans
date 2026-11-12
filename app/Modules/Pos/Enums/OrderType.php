<?php

namespace App\Modules\Pos\Enums;

enum OrderType: string
{
    case DineIn = 'dine_in';
    case Takeaway = 'takeaway';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'Makan di Tempat',
            self::Takeaway => 'Bawa Pulang (Take Away)',
            self::Pickup => 'Ambil Nanti (Pickup)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::DineIn => 'Dine In',
            self::Takeaway => 'Take Away',
            self::Pickup => 'Pickup',
        };
    }

    /**
     * Penjelasan singkat agar Take Away dan Pickup tidak tertukar.
     */
    public function description(): string
    {
        return match ($this) {
            self::DineIn => 'Disajikan dan dinikmati langsung di tempat.',
            self::Takeaway => 'Dibungkus sekarang dan langsung dibawa pulang.',
            self::Pickup => 'Dipesan sekarang, diambil sesuai waktu yang disepakati.',
        };
    }
}
