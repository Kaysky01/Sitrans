<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Modules\Pos\Enums\DiscountType;
use App\Modules\Pos\Support\Money;

/**
 * Sets an order-level discount and recalculates the totals.
 */
final class ApplyDiscountAction
{
    public function __construct(private readonly RecalculateOrderAction $recalculate) {}

    public function execute(Order $order, ?DiscountType $type, float $value): Order
    {
        $order->fill([
            'discount_type' => $type,
            'discount_value' => Money::round($value),
        ])->save();

        return $this->recalculate->execute($order);
    }
}
