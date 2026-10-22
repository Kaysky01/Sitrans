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
            self::Takeaway => 'Bawa Pulang',
            self::Pickup => 'Ambil Sendiri',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::DineIn => 'Dine In',
            self::Takeaway => 'Takeaway',
            self::Pickup => 'Pickup',
        };
    }
}
