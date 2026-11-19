<?php

namespace App\Modules\Pos\Actions;

use App\Exceptions\NotFoundError;
use App\Exceptions\ValidationError;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Modules\Pos\Enums\OrderChannel;
use App\Modules\Pos\Enums\OrderType;
use Illuminate\Validation\ValidationException;

/**
 * Creates, prices and pays an order placed through the public ordering API.
 */
final class CreateOnlineOrderAction
{
    public function __construct(
        private readonly CreateOrderAction $createOrder,
        private readonly AddOrderItemAction $addItem,
        private readonly RecalculateOrderAction $recalculate,
        private readonly PayOrderAction $pay,
    ) {}

    /**
     * @param  array{
     *     type?: OrderType,
     *     customer_name?: string|null,
     *     table_number?: string|null,
     *     notes?: string|null,
     *     payment_method?: string|null,
     *     items: array<int, array{
     *         product_id: int,
     *         product_variant_id?: int|null,
     *         quantity?: float,
     *         modifier_ids?: array<int, int>,
     *         notes?: string|null
     *     }>
     * }  $data
     */
    public function execute(Outlet $outlet, array $data): Order
    {
        $order = $this->createOrder->execute($outlet, [
            'channel' => OrderChannel::Online,
            'type' => $data['type'] ?? OrderType::Takeaway,
            'customer_name' => $data['customer_name'] ?? null,
            'table_number' => $data['table_number'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $line) {
            $product = Product::query()->find($line['product_id']);

            if ($product === null) {
                throw new NotFoundError(
                    'Menu yang dipesan tidak ditemukan. Mungkin menu sudah dihapus. Perbarui daftar menu lalu coba lagi.',
                    'items',
                    'MENU_TIDAK_DITEMUKAN',
                );
            }

            $quantity = (float) ($line['quantity'] ?? 1);

            if ($quantity <= 0) {
                throw ValidationError::with('items', 'Jumlah pesanan untuk '.$product->name.' minimal 1.', 'JUMLAH_TIDAK_VALID');
            }

            if (! $product->isOrderable()) {
                throw ValidationError::with(
                    'items',
                    "{$product->name} sedang tidak tersedia.",
                    'MENU_TIDAK_TERSEDIA',
                );
            }

            $variant = $this->resolveVariant($product, $line['product_variant_id'] ?? null);

            $this->addItem->execute($order, [
                'product_id' => $product->getKey(),
                'product_variant_id' => $variant?->getKey(),
                'name' => $product->name,
                'variant_name' => $variant?->name,
                'unit_price' => $variant !== null ? (float) $variant->price : (float) $product->price,
                'quantity' => $quantity,
                'cogs' => (float) $product->cost,
                'notes' => $line['notes'] ?? null,
                'modifiers' => $this->resolveModifiers($product, $line['modifier_ids'] ?? []),
            ]);
        }

        $this->recalculate->execute($order);
        $order->refresh();

        $method = $this->resolvePaymentMethod($data['payment_method'] ?? null);

        $this->pay->execute($order, [[
            'payment_method_id' => $method->getKey(),
            'method_name' => $method->name,
            'method_type' => $method->type,
            'amount' => (float) $order->grand_total,
        ]]);

        return $order->refresh()->load(['items.modifiers', 'payments']);
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if ($variantId === null) {
            return null;
        }

        $variant = $product->variants()->whereKey($variantId)->first();

        if ($variant === null) {
            throw ValidationException::withMessages([
                'items' => "Varian tidak ditemukan untuk {$product->name}.",
            ]);
        }

        return $variant;
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, array{modifier_id: int, name: string, price: float}>
     */
    private function resolveModifiers(Product $product, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $product->loadMissing('modifierGroups.modifiers');

        $allowed = $product->modifierGroups->flatMap->modifiers->keyBy('id');
        $chosen = [];

        foreach ($ids as $id) {
            $modifier = $allowed->get((int) $id);

            if ($modifier === null) {
                throw ValidationException::withMessages([
                    'items' => "Modifier tidak valid untuk {$product->name}.",
                ]);
            }

            $chosen[] = [
                'modifier_id' => $modifier->getKey(),
                'name' => $modifier->name,
                'price' => (float) $modifier->price,
            ];
        }

        return $chosen;
    }

    private function resolvePaymentMethod(?string $code): PaymentMethod
    {
        $query = PaymentMethod::query()->where('is_active', true);

        $method = $code !== null
            ? (clone $query)->where('code', $code)->first()
            : $query->orderBy('sort_order')->first();

        if ($method === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran tidak tersedia.',
            ]);
        }

        return $method;
    }
}
