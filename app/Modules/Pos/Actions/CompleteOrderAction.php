<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Modules\Pos\Enums\OrderStatus;

/**
 * Closes a paid order once it has been served/handed over.
 */
final class CompleteOrderAction
{
    public function execute(Order $order): Order
    {
        if ($order->status === OrderStatus::Paid) {
            $order->fill(['status' => OrderStatus::Completed])->save();
        }

        return $order;
    }
}
