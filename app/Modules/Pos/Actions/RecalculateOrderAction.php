<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TaxSetting;
use App\Modules\Pos\Services\OrderCalculator;

/**
 * Recomputed the stored totals of an order from its items, its discount and
 * the outlet tax settings. Safe to call after any mutation.
 */
final class RecalculateOrderAction
{
    public function __construct(private readonly OrderCalculator $calculator) {}

    public function execute(Order $order, ?TaxSetting $tax = null): Order
    {
        $tax ??= $this->resolveTaxSetting((int) $order->outlet_id);

        $lines = $order->items()->get()
            ->map(fn (OrderItem $item): array => [
                'unit_price' => (float) $item->unit_price,
                'quantity' => (float) $item->quantity,
                'discount_amount' => (float) $item->discount_amount,
            ])
            ->all();

        $totals = $this->calculator->calculate(
            $tax,
            $lines,
            $order->discount_type,
            (float) $order->discount_value,
        );

        $order->fill($totals)->save();

        return $order;
    }

    private function resolveTaxSetting(int $outletId): TaxSetting
    {
        return TaxSetting::query()->where('outlet_id', $outletId)->first()
            ?? TaxSetting::query()->whereNull('outlet_id')->first()
            ?? new TaxSetting([
                'pb1_enabled' => false,
                'service_enabled' => false,
                'prices_include_tax' => false,
            ]);
    }
}
