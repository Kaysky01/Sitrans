<?php

namespace App\Modules\Pos\Services;

use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generates human-readable, per-outlet, per-day order numbers, e.g.
 * INV-261018-0001. The outlet row is locked for update, so this must be
 * called inside an open DB transaction (see CreateOrderAction) to serialize
 * concurrent order creation at the same outlet.
 */
final class OrderNumberGenerator
{
    public function generate(Outlet $outlet, ?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $prefix = 'INV-'.$date->format('ymd');

        // Kunci baris outlet agar dua kasir yang membuat order secara
        // bersamaan di outlet yang sama tidak mendapatkan nomor kembar.
        DB::table($outlet->getTable())
            ->where($outlet->getKeyName(), $outlet->getKey())
            ->lockForUpdate()
            ->first();

        $sequence = Order::withoutTenantScope($outlet->tenant_id)
            ->where('outlet_id', $outlet->getKey())
            ->whereDate('created_at', $date->toDateString())
            ->count() + 1;

        do {
            $number = $prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = Order::withoutTenantScope($outlet->tenant_id)
                ->where('number', $number)
                ->exists();
            $sequence++;
        } while ($exists);

        return $number;
    }
}
