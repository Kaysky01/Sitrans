<?php

namespace App\Modules\Pos\Actions;

use App\Exceptions\ValidationError;
use App\Models\Order;
use App\Models\OrderItem;
use App\Modules\Pos\Support\Money;

/**
 * Appends a line item (with its chosen modifiers) to an open order.
 *
 * `unit_price` is the snapshot of base price + variant surcharge + selected
 * modifiers per unit, so the line total stays stable even if the menu price
 * changes later. `cogs` is the HPP snapshot taken at sale time.
 */
final class AddOrderItemAction
{
    /**
     * @param  array{
     *     product_id?: int|null,
     *     product_variant_id?: int|null,
     *     bundle_id?: int|null,
     *     name: string,
     *     variant_name?: string|null,
     *     unit_price: float,
     *     quantity?: float,
     *     discount_amount?: float,
     *     cogs?: float,
     *     notes?: string|null,
     *     modifiers?: array<int, array{modifier_id?: int|null, name: string, price: float, quantity?: float}>
     * }  $data
     */
    public function execute(Order $order, array $data): OrderItem
    {
        $modifiers = $data['modifiers'] ?? [];
        $modifierTotal = array_reduce(
            $modifiers,
            fn (float $carry, array $modifier): float => $carry
                + ($modifier['price'] * ($modifier['quantity'] ?? 1)),
            0.0,
        );

        $unitPrice = Money::round($data['unit_price'] + $modifierTotal);
        $quantity = (float) ($data['quantity'] ?? 1);
        $discount = Money::round($data['discount_amount'] ?? 0);

        if ($quantity <= 0) {
            throw ValidationError::with('quantity', 'Jumlah pesanan minimal 1.', 'JUMLAH_TIDAK_VALID');
        }

        if ($quantity > 999) {
            throw ValidationError::with('quantity', 'Jumlah pesanan terlalu besar (maksimal 999).', 'JUMLAH_TIDAK_VALID');
        }

        if ($unitPrice < 0) {
            throw ValidationError::with('unit_price', 'Harga item tidak boleh negatif.', 'HARGA_TIDAK_VALID');
        }

        if ($discount < 0) {
            throw ValidationError::with('discount_amount', 'Diskon item tidak boleh negatif.', 'DISKON_TIDAK_VALID');
        }

        $item = $order->items()->create([
            'product_id' => $data['product_id'] ?? null,
            'product_variant_id' => $data['product_variant_id'] ?? null,
            'bundle_id' => $data['bundle_id'] ?? null,
            'name' => $data['name'],
            'variant_name' => $data['variant_name'] ?? null,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'discount_amount' => $discount,
            'cogs' => Money::round($data['cogs'] ?? 0),
            'line_total' => Money::round(($unitPrice * $quantity) - $discount),
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($modifiers as $modifier) {
            $item->modifiers()->create([
                'modifier_id' => $modifier['modifier_id'] ?? null,
                'name' => $modifier['name'],
                'price' => Money::round($modifier['price']),
                'quantity' => (float) ($modifier['quantity'] ?? 1),
            ]);
        }

        return $item;
    }
}
