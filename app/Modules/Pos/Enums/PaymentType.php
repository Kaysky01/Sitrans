<?php

namespace App\Modules\Pos\Enums;

enum PaymentType: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case Transfer = 'transfer';
    case Card = 'card';
    case Ewallet = 'ewallet';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Qris => 'QRIS',
            self::Transfer => 'Transfer',
            self::Card => 'Kartu',
            self::Ewallet => 'E-Wallet',
        };
    }

    public function requiresReference(): bool
    {
        return $this !== self::Cash;
    }
}
