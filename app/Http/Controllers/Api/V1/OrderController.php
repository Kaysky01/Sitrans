<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Outlet;
use App\Modules\Pos\Actions\CreateOnlineOrderAction;
use App\Modules\Pos\Enums\OrderType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrderController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'outlet_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:dine_in,takeaway,pickup'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:1'],
            'items.*.modifier_ids' => ['nullable', 'array'],
            'items.*.modifier_ids.*' => ['integer'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $outlet = $this->resolveOutlet($data['outlet_id'] ?? null);

        if ($outlet === null) {
            abort(422, 'Outlet belum dikonfigurasi untuk kafe ini.');
        }

        // Idempotency-Key: retry jaringan dengan kunci sama tidak membuat
        // order dobel — response pertama dikirim ulang.
        $idempotencyKey = trim((string) $request->header('Idempotency-Key', ''));

        if ($idempotencyKey !== '') {
            return $this->storeIdempotent($outlet, $data, $idempotencyKey);
        }

        $order = $this->createOrder($outlet, $data);

        return response()->json(['data' => $this->present($order)], 201);
    }

    private function storeIdempotent(Outlet $outlet, array $data, string $key): JsonResponse
    {
        $cacheKey = 'order-idem:'.$outlet->tenant_id.':'.sha1($key);

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return response()->json($cached, 200)
                ->header('Idempotency-Replayed', 'true');
        }

        // Kunci singkat mencegah dua request bersamaan dengan kunci sama
        // membuat order kembar.
        $lock = Cache::lock($cacheKey.':lock', 10);

        if (! $lock->get()) {
            abort(429, 'Pesanan dengan kunci yang sama sedang diproses. Tunggu beberapa saat lalu coba lagi.');
        }

        try {
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return response()->json($cached, 200)
                    ->header('Idempotency-Replayed', 'true');
            }

            $order = $this->createOrder($outlet, $data);

            $payload = ['data' => $this->present($order)];

            Cache::put($cacheKey, $payload, now()->addDay());

            return response()->json($payload, 201);
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOrder(Outlet $outlet, array $data): Order
    {
        return app(CreateOnlineOrderAction::class)->execute($outlet, [
            'type' => isset($data['type']) ? OrderType::from($data['type']) : null,
            'customer_name' => $data['customer_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'items' => $data['items'],
        ]);
    }

    private function resolveOutlet(?int $outletId): ?Outlet
    {
        $query = Outlet::query()->where('is_active', true);

        return $outletId !== null
            ? (clone $query)->whereKey($outletId)->first()
            : $query->orderBy('id')->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Order $order): array
    {
        return [
            'id' => $order->getKey(),
            'number' => $order->number,
            'status' => $order->status->value,
            'channel' => $order->channel->value,
            'type' => $order->type->value,
            'outlet' => $order->outlet?->name,
            'customer_name' => $order->customer_name,
            'subtotal' => (float) $order->subtotal,
            'discount_total' => (float) $order->discount_total,
            'tax_amount' => (float) $order->tax_amount,
            'service_amount' => (float) $order->service_amount,
            'grand_total' => (float) $order->grand_total,
            'paid_total' => (float) $order->paid_total,
            'items' => $order->items->map(fn ($item): array => [
                'name' => $item->name,
                'variant_name' => $item->variant_name,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])->values(),
            'payments' => $order->payments->map(fn ($payment): array => [
                'method' => $payment->method_name,
                'amount' => (float) $payment->amount,
            ])->values(),
        ];
    }
}
