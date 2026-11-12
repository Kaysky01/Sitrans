<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Models\User;
use App\Modules\Pos\Enums\OrderFulfillmentStatus;
use App\Support\Audit\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Memindahkan pesanan melalui antrean operasional: baru -> diterima ->
 * diproses -> selesai. Status pembayaran tidak diubah kecuali saat selesai,
 * di mana transaksi yang sudah dibayar ikut ditutup.
 */
final class AdvanceOrderFulfillmentAction
{
    public function execute(Order $order, OrderFulfillmentStatus $target, ?User $actor = null): Order
    {
        return DB::transaction(function () use ($order, $target, $actor): Order {
            $current = $order->fulfillment_status ?? OrderFulfillmentStatus::Pending;

            if ($current === $target) {
                return $order;
            }

            if (! $current->canTransitionTo($target)) {
                throw ValidationException::withMessages([
                    'fulfillment' => "Status tidak dapat diubah dari \"{$current->label()}\" ke \"{$target->label()}\".",
                ]);
            }

            if ($target === OrderFulfillmentStatus::Completed && ! $order->isPaid()) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Pesanan harus dibayar terlebih dahulu sebelum diselesaikan.',
                ]);
            }

            $timestampColumn = match ($target) {
                OrderFulfillmentStatus::Accepted => 'accepted_at',
                OrderFulfillmentStatus::Processing => 'processing_started_at',
                OrderFulfillmentStatus::Completed => 'fulfilled_at',
                default => null,
            };

            $order->fulfillment_status = $target;

            if ($timestampColumn !== null) {
                $order->{$timestampColumn} = now();
            }

            $order->save();

            if ($target === OrderFulfillmentStatus::Completed) {
                app(CompleteOrderAction::class)->execute($order);
            }

            app(ActivityLogger::class)->log(
                'order.fulfillment',
                "Pesanan {$order->number} menjadi {$target->label()}",
                $order,
                ['status' => $target->value],
                user: $actor,
            );

            return $order;
        });
    }
}
