<?php

namespace App\Modules\Pos\Actions;

use App\Exceptions\ConflictError;
use App\Exceptions\ValidationError;
use App\Models\Order;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Enums\PaymentType;
use App\Modules\Pos\Services\CashShiftService;
use App\Modules\Pos\Support\Money;
use App\Support\Format;
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
            // Ambil ulang dengan kunci baris supaya dua kasir yang memproses
            // pesanan yang sama secara bersamaan tidak dobel membayar.
            /** @var Order $latest */
            $latest = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($latest->status === OrderStatus::Paid) {
                throw new ConflictError(
                    'Pesanan ini sudah dibayar. Struk terbaru bisa dibuka dari daftar pesanan.',
                    'status',
                    'PESANAN_SUDAH_DIBAYAR',
                );
            }

            if ($latest->status !== OrderStatus::Open) {
                throw new ConflictError(
                    'Pesanan tidak bisa dibayar karena statusnya sudah berubah. Muat ulang daftar pesanan.',
                    'status',
                    'STATUS_TIDAK_VALID',
                );
            }

            // Pastikan stok masih mencukupi sebelum uang dicatat, supaya
            // kasir langsung tahu produk mana yang kurang.
            $this->stock->ensureAvailabilityForOrder($latest);

            $paidTotal = 0.0;

            foreach ($payments as $payment) {
                $amount = Money::round($payment['amount']);
                $paidTotal += $amount;

                $latest->payments()->create([
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
            $billed = (float) $latest->grand_total;

            if ($paidTotal < $billed) {
                throw ValidationError::with(
                    'amountPaid',
                    'Uang diterima kurang '.Format::rupiah(Money::round($billed - $paidTotal)).'.',
                    'PEMBAYARAN_KURANG',
                );
            }

            $change = Money::round(max(0.0, $paidTotal - $billed));

            $latest->fill([
                'status' => OrderStatus::Paid,
                'cash_shift_id' => $latest->cash_shift_id ?? $this->currentShiftId($latest),
                'paid_total' => $paidTotal,
                'change_total' => $change,
                'paid_at' => now(),
            ])->save();

            $this->stock->deductForOrder($latest);

            return $latest;
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
