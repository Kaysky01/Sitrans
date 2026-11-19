<?php

namespace App\Modules\Pos\Actions;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use App\Modules\Pos\Enums\OrderChannel;
use App\Modules\Pos\Enums\OrderStatus;
use App\Modules\Pos\Enums\OrderType;
use App\Modules\Pos\Services\OrderNumberGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Opens a new order at an outlet.
 */
final class CreateOrderAction
{
    public function __construct(private readonly OrderNumberGenerator $numbers) {}

    /**
     * @param  array{channel?: OrderChannel, type?: OrderType|null, customer_name?: string|null, table_number?: string|null, notes?: string|null}  $attributes
     */
    public function execute(Outlet $outlet, array $attributes = [], ?User $cashier = null): Order
    {
        // Generasi nomor dan insert harus dalam satu transaksi: kunci baris
        // outlet di generator hanya efektif jika insert ikut terkunci, jadi
        // dua order bersamaan tidak pernah mendapat nomor kembar.
        return DB::transaction(function () use ($outlet, $attributes, $cashier): Order {
            return Order::create([
                'outlet_id' => $outlet->getKey(),
                'user_id' => $cashier?->getKey(),
                'number' => $this->numbers->generate($outlet),
                'channel' => $attributes['channel'] ?? OrderChannel::Offline,
                // Tipe boleh kosong saat keranjang masih diisi; kasir memvalidasinya
                // sebelum pembayaran dimulai.
                'type' => $attributes['type'] ?? null,
                'status' => OrderStatus::Open,
                'customer_name' => $attributes['customer_name'] ?? null,
                'table_number' => $attributes['table_number'] ?? null,
                'notes' => $attributes['notes'] ?? null,
                'ordered_at' => now(),
            ]);
        });
    }
}
