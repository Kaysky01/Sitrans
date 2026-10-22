<?php

namespace App\Modules\Inventory\Enums;

enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';
    case Waste = 'waste';
    case Sale = 'sale';
    case SaleReturn = 'sale_return';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stok masuk',
            self::Out => 'Stok keluar',
            self::Adjustment => 'Penyesuaian',
            self::Waste => 'Rusak / terbuang',
            self::Sale => 'Penjualan',
            self::SaleReturn => 'Pengembalian penjualan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::In, self::SaleReturn => 'success',
            self::Out, self::Sale => 'danger',
            self::Adjustment => 'warning',
            self::Waste => 'gray',
        };
    }

    /**
     * How a movement of this type affects the running quantity.
     */
    public function direction(): int
    {
        return match ($this) {
            self::In, self::SaleReturn => 1,
            self::Out, self::Waste, self::Sale => -1,
            self::Adjustment => 1,
        };
    }
}
