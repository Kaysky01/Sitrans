<?php

namespace App\Modules\Pos\Services;

use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Support\Carbon;

/**
 * Generates human-readable, per-outlet, per-day order numbers, e.g.
 * INV-261018-0001. Uniqueness is guaranteed by a retry loop against the
 * (tenant_id, number) unique index.
 */
final class OrderNumberGenerator
{
    public function generate(Outlet $outlet, ?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $prefix = 'INV-'.$date->format('ymd');
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
