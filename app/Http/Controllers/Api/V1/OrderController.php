<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Outlet;
use App\Modules\Pos\Actions\CreateOnlineOrderAction;
use App\Modules\Pos\Enums\OrderType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $order = app(CreateOnlineOrderAction::class)->execute($outlet, [
            'type' => isset($data['type']) ? OrderType::from($data['type']) : null,
            'customer_name' => $data['customer_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'items' => $data['items'],
        ]);

        return response()->json(['data' => $this->present($order)], 201);
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
