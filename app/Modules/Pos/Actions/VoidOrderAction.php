<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Models\User;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Pos\Enums\OrderStatus;
use App\Support\Audit\ActivityLogger;
use Illuminate\Support\Facades\DB;

/**
 * Voids an order. Must be authorised by a supervisor PIN before calling.
 */
final class VoidOrderAction
{
    public function __construct(private readonly StockLedger $stock) {}

    public function execute(Order $order, User $actor, string $reason): Order
    {
        return DB::transaction(function () use ($order, $actor, $reason): Order {
            $order->fill([
                'status' => OrderStatus::Voided,
                'voided_by' => $actor->getKey(),
                'voided_at' => now(),
                'void_reason' => $reason,
            ])->save();

            $this->stock->restoreForOrder($order);

            app(ActivityLogger::class)->log(
                'order.void',
                'Transaksi '.$order->number.' dibatalkan',
                $order,
                ['reason' => $reason, 'voided_by' => $actor->getKey()],
                user: $actor,
            );

            return $order;
        });
    }
}
