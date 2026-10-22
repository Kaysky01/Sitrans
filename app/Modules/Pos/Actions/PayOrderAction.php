<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Pos\Services\CashShiftService;
use App\Modules\Pos\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * Records one or more payments against an order, marks it paid and computes the
 * change returned to the customer.
 */
final class PayOrderAction
{
    public function __construct(private readonly StockLedger $stock) {}

    /**
     * @param  array<int, array{
     *     payment_method_id?: int|null,
     *     method_name: string,
     *     method_type: PaymentType,
     *     amount: float,
     *     reference?: string|null
     * }>  $payments
     */
    public function execute(Order $order, array $payments): Order
    {
        return DB::transaction(function () use ($order, $payments): Order {
            $paidTotal = 0.0;

            foreach ($payments as $payment) {
                $amount = Money::round($payment['amount']);
                $paidTotal += $amount;

                $order->payments()->create([
                    'payment_method_id' => $payment['payment_method_id'] ?? null,
                    'method_name' => $payment['method_name'],
                    'method_type' => $payment['method_type'],
                    'amount' => $amount,
                    'reference' => $payment['reference'] ?? null,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            $paidTotal = Money::round($paidTotal);
            $change = Money::round(max(0.0, $paidTotal - (float) $order->grand_total));

            $order->fill([
                'status' => OrderStatus::Paid,
                'cash_shift_id' => $order->cash_shift_id ?? $this->currentShiftId($order),
                'paid_total' => $paidTotal,
                'change_total' => $change,
                'paid_at' => now(),
            ])->save();

            $this->stock->deductForOrder($order);

            return $order;
        });
    }

    private function currentShiftId(Order $order): ?int
    {
        $outlet = $order->outlet;
        $cashier = $order->cashier;

        if ($outlet === null || $cashier === null) {
            return null;
        }

        return app(CashShiftService::class)->openFor($outlet, $cashier)?->getKey();
    }
}
