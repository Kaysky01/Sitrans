<?php

namespace App\Modules\Pos\Services;

use App\Models\CashShift;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\User;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Pos\Support\Money;
use Illuminate\Database\Eloquent\Builder;

/**
 * Opens and closes cash-drawer shifts and reconciles the expected cash against
 * the counted amount. Only paid/completed orders of the shift are considered.
 */
final class CashShiftService
{
    public function openFor(Outlet $outlet, User $user): ?CashShift
    {
        return CashShift::query()
            ->where('outlet_id', $outlet->getKey())
            ->where('user_id', $user->getKey())
            ->where('status', CashShift::STATUS_OPEN)
            ->latest('opened_at')
            ->first();
    }

    public function open(Outlet $outlet, User $user, float $openingFloat = 0): CashShift
    {
        $existing = $this->openFor($outlet, $user);

        if ($existing !== null) {
            return $existing;
        }

        return CashShift::create([
            'outlet_id' => $outlet->getKey(),
            'user_id' => $user->getKey(),
            'opened_at' => now(),
            'opening_float' => Money::round($openingFloat),
            'status' => CashShift::STATUS_OPEN,
        ]);
    }

    public function close(CashShift $shift, User $actor, float $countedCash, ?string $notes = null): CashShift
    {
        $summary = $this->summary($shift);

        $shift->fill([
            'status' => CashShift::STATUS_CLOSED,
            'closed_at' => now(),
            'closed_by' => $actor->getKey(),
            'counted_cash' => Money::round($countedCash),
            'expected_cash' => $summary['expected_cash'],
            'difference' => Money::round($countedCash - $summary['expected_cash']),
            'notes' => $notes,
        ])->save();

        return $shift;
    }

    /**
     * @return array{cash_sales: float, non_cash_sales: float, total_sales: float, change_total: float, expected_cash: float, orders: int}
     */
    public function summary(CashShift $shift): array
    {
        $cash = PaymentType::Cash->value;

        $payments = Payment::query()
            ->whereHas('order', fn (Builder $query) => $query->where('cash_shift_id', $shift->getKey()))
            ->selectRaw('COALESCE(SUM(CASE WHEN method_type = ? THEN amount ELSE 0 END), 0) as cash', [$cash])
            ->selectRaw('COALESCE(SUM(CASE WHEN method_type <> ? THEN amount ELSE 0 END), 0) as non_cash', [$cash])
            ->first();

        $cashSales = round((float) ($payments->cash ?? 0), 2);
        $nonCashSales = round((float) ($payments->non_cash ?? 0), 2);

        $orders = Order::query()->where('cash_shift_id', $shift->getKey())->revenue();
        $orderCount = (clone $orders)->count();
        $change = round((float) $orders->sum('change_total'), 2);

        return [
            'cash_sales' => $cashSales,
            'non_cash_sales' => $nonCashSales,
            'total_sales' => round($cashSales + $nonCashSales, 2),
            'change_total' => $change,
            'expected_cash' => round((float) $shift->opening_float + $cashSales - $change, 2),
            'orders' => $orderCount,
        ];
    }
}
