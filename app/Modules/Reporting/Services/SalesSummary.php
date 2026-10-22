<?php

namespace App\Modules\Reporting\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Read model for sales reporting. All queries run inside the tenant global
 * scope, so a report only ever spans the active cafe. An optional outlet id
 * narrows the report to a single outlet.
 *
 * Revenue follows the accounting rule used across Sitrans: `subtotal -
 * discount_total`. PB1 and service charge are liabilities (titipan) and are
 * reported separately, never as revenue.
 */
final class SalesSummary
{
    /**
     * @param  CarbonInterface  $from  inclusive lower bound
     * @param  CarbonInterface  $to  inclusive upper bound
     * @return array{
     *     orders: int,
     *     revenue: float,
     *     discounts: float,
     *     tax: float,
     *     service: float,
     *     gross: float,
     *     cogs: float,
     *     gross_margin: float,
     *     avg_ticket: float,
     *     items_sold: float
     * }
     */
    public function totals(CarbonInterface $from, CarbonInterface $to, ?int $outletId = null): array
    {
        $row = $this->orderQuery($from, $to, $outletId)
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(subtotal - discount_total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(discount_total), 0) as discounts')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax')
            ->selectRaw('COALESCE(SUM(service_amount), 0) as service')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as gross')
            ->first();

        $orders = (int) ($row->orders ?? 0);
        $revenue = (float) ($row->revenue ?? 0);
        $cogs = (float) $this->itemQuery($from, $to, $outletId)
            ->selectRaw('COALESCE(SUM(cogs * quantity), 0) as total')
            ->value('total');

        $itemsSold = (float) $this->itemQuery($from, $to, $outletId)->sum('quantity');

        return [
            'orders' => $orders,
            'revenue' => round($revenue, 2),
            'discounts' => (float) ($row->discounts ?? 0),
            'tax' => (float) ($row->tax ?? 0),
            'service' => (float) ($row->service ?? 0),
            'gross' => (float) ($row->gross ?? 0),
            'cogs' => round($cogs, 2),
            'gross_margin' => round($revenue - $cogs, 2),
            'avg_ticket' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
            'items_sold' => $itemsSold,
        ];
    }

    /**
     * Daily revenue series, with zero-filled days, keyed by Y-m-d.
     *
     * @return array<string, float>
     */
    public function dailySeries(CarbonInterface $from, CarbonInterface $to, ?int $outletId = null): array
    {
        $revenueByDay = $this->orderQuery($from, $to, $outletId)
            ->selectRaw('DATE(paid_at) as day')
            ->selectRaw('COALESCE(SUM(subtotal - discount_total), 0) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $series = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->endOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $series[$key] = round((float) ($revenueByDay[$key] ?? 0), 2);
            $cursor = $cursor->addDay();
        }

        return $series;
    }

    /**
     * Daily PB1 (tax) and service charge collected per day, zero-filled,
     * keyed by Y-m-d. Both are liabilities (titipan), reported separately
     * from revenue.
     *
     * @return array<string, array{orders: int, tax: float, service: float, gross: float}>
     */
    public function taxSeries(CarbonInterface $from, CarbonInterface $to, ?int $outletId = null): array
    {
        $rows = $this->orderQuery($from, $to, $outletId)
            ->selectRaw('DATE(paid_at) as day')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax')
            ->selectRaw('COALESCE(SUM(service_amount), 0) as service')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as gross')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $series = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->endOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $row = $rows->get($key);

            $series[$key] = [
                'orders' => (int) ($row->orders ?? 0),
                'tax' => round((float) ($row->tax ?? 0), 2),
                'service' => round((float) ($row->service ?? 0), 2),
                'gross' => round((float) ($row->gross ?? 0), 2),
            ];

            $cursor = $cursor->addDay();
        }

        return $series;
    }

    /**
     * @return Collection<int, object{name: string, qty: float, total: float}>
     */
    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 10, ?int $outletId = null): Collection
    {
        return $this->itemQuery($from, $to, $outletId)
            ->selectRaw('name')
            ->selectRaw('SUM(quantity) as qty')
            ->selectRaw('SUM(line_total) as total')
            ->groupBy('name')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, object{method_name: string, payments: int, total: float}>
     */
    public function paymentBreakdown(CarbonInterface $from, CarbonInterface $to, ?int $outletId = null): Collection
    {
        return Payment::query()
            ->whereHas('order', fn (Builder $query) => $this->constrainOrder($query, $from, $to, $outletId))
            ->selectRaw('method_name')
            ->selectRaw('COUNT(*) as payments')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('method_name')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * @return Builder<Order>
     */
    private function orderQuery(CarbonInterface $from, CarbonInterface $to, ?int $outletId): Builder
    {
        return $this->constrainOrder(Order::query(), $from, $to, $outletId);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    private function constrainOrder(Builder $query, CarbonInterface $from, CarbonInterface $to, ?int $outletId): Builder
    {
        $query->paidBetween($from, $to);

        if ($outletId !== null) {
            $query->where('outlet_id', $outletId);
        }

        return $query;
    }

    /**
     * @return Builder<OrderItem>
     */
    private function itemQuery(CarbonInterface $from, CarbonInterface $to, ?int $outletId)
    {
        return OrderItem::query()
            ->whereHas('order', fn (Builder $query) => $this->constrainOrder($query, $from, $to, $outletId));
    }
}
