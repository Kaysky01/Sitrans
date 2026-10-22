<?php

namespace App\Modules\Pos\Services;

use App\Models\TaxSetting;
use App\Modules\Pos\Enums\DiscountType;
use App\Modules\Pos\Support\Money;

/**
 * Pure calculation of order totals. PB1 and service charge are computed as
 * separate liabilities (titipan), never folded into revenue. Revenue is
 * `subtotal - discount_total`.
 */
final class OrderCalculator
{
    /**
     * @param  array<int, array{unit_price: float, quantity: float, discount_amount: float}>  $lines
     * @return array{subtotal: float, discount_total: float, service_amount: float, tax_amount: float, grand_total: float}
     */
    public function calculate(
        TaxSetting $tax,
        array $lines,
        ?DiscountType $discountType = null,
        float $discountValue = 0,
    ): array {
        $subtotal = Money::round(array_reduce(
            $lines,
            fn (float $carry, array $line): float => $carry
                + ($line['unit_price'] * $line['quantity'])
                - $line['discount_amount'],
            0.0,
        ));

        $discountTotal = Money::round(match ($discountType) {
            DiscountType::Percent => $subtotal * ($discountValue / 100),
            DiscountType::Fixed => $discountValue,
            null => 0.0,
        });
        $discountTotal = min($discountTotal, $subtotal);

        $net = Money::round($subtotal - $discountTotal);

        $serviceAmount = $tax->service_enabled
            ? Money::round($net * ($tax->service_rate / 100))
            : 0.0;

        $taxable = Money::round($net + $serviceAmount);

        if ($tax->prices_include_tax) {
            $taxAmount = $tax->pb1_enabled
                ? Money::round($taxable * ($tax->pb1_rate / (100 + $tax->pb1_rate)))
                : 0.0;
            $grandTotal = $net;
        } else {
            $taxAmount = $tax->pb1_enabled
                ? Money::round($taxable * ($tax->pb1_rate / 100))
                : 0.0;
            $grandTotal = Money::round($taxable + $taxAmount);
        }

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'service_amount' => $serviceAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ];
    }
}
