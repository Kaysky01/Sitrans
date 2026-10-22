<?php

namespace App\Modules\Pos\Enums;

enum OrderChannel: string
{
    case Offline = 'offline';
    case Online = 'online';
    case QrTable = 'qr_meja';

    public function label(): string
    {
        return match ($this) {
            self::Offline => 'Kasir',
            self::Online => 'Online',
            self::QrTable => 'QR Meja',
        };
    }
}
