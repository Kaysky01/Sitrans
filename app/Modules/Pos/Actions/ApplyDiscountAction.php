<?php

namespace App\Modules\Pos\Actions;

use App\Exceptions\ConflictError;
use App\Exceptions\ValidationError;
use App\Models\Order;
use App\Modules\Pos\Enums\DiscountType;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Support\Money;

/**
 * Sets an order-level discount and recalculates the totals.
 */
final class ApplyDiscountAction
{
    public function __construct(private readonly RecalculateOrderAction $recalculate) {}

    public function execute(Order $order, ?DiscountType $type, float $value): Order
    {
        if ($order->status !== OrderStatus::Open) {
            throw new ConflictError(
                'Diskon hanya bisa diubah saat pesanan masih terbuka, sebelum pembayaran.',
                'discount',
                'PESANAN_TIDAK_TERBUKA',
            );
        }

        if ($value < 0) {
            throw ValidationError::with('discountValue', 'Diskon tidak boleh negatif.', 'DISKON_TIDAK_VALID');
        }

        if ($type === DiscountType::Percent && $value > 100) {
            throw ValidationError::with('discountValue', 'Diskon persentase maksimal 100%.', 'DISKON_TIDAK_VALID');
        }

        $order->fill([
            'discount_type' => $type,
            'discount_value' => Money::round($value),
        ])->save();

        return $this->recalculate->execute($order);
    }
}
