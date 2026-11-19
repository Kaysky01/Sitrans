<?php

namespace App\Modules\Inventory\Services;

use App\Exceptions\ValidationError;
use App\Models\BundleItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Support\Audit\ActivityLogger;

/**
 * Single source of truth for stock levels. Every quantity change is written as an
 * immutable {@see StockMovement} so the running balance can always be audited.
 */
final class StockLedger
{
    public function forProduct(Product $product, ?int $variantId = null): StockItem
    {
        return StockItem::query()->firstOrCreate(
            [
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->getKey(),
                'product_variant_id' => $variantId,
            ],
            [
                'quantity' => 0,
                'reorder_level' => 0,
            ],
        );
    }

    /**
     * Apply a raw signed delta to a stock item and record the movement.
     */
    public function adjust(
        StockItem $item,
        float $delta,
        StockMovementType $type,
        ?string $reference = null,
        ?int $userId = null,
        ?string $note = null,
        bool $allowNegative = true,
    ): StockMovement {
        $delta = round($delta, 3);
        $balance = round((float) $item->quantity + $delta, 3);

        // Penjualan tidak boleh membuat stok minus. Penyesuaian manual oleh
        // admin (stock opname) tetap boleh untuk koreksi.
        if ($balance < 0 && ! $allowNegative) {
            $product = $item->product;

            throw ValidationError::with(
                'items',
                'Stok '.($product?->name ?? 'produk').' tersisa '.$this->formatQuantity((float) $item->quantity).'.',
                'STOK_TIDAK_CUKUP',
            );
        }

        $statusBefore = $this->statusOf($item);

        $item->quantity = $balance;
        $item->save();

        $movement = $item->movements()->create([
            'tenant_id' => $item->tenant_id,
            'type' => $type,
            'quantity' => $delta,
            'balance_after' => $balance,
            'reference' => $reference,
            'note' => $note,
            'user_id' => $userId,
        ]);

        $this->syncSoldOutFlag($item);
        $this->alertIfCrossedThreshold($item, $statusBefore);

        if ($type !== StockMovementType::Sale && $type !== StockMovementType::SaleReturn) {
            app(ActivityLogger::class)->log(
                'inventory.adjust',
                'Stok produk #'.$item->product_id.' disesuaikan '.($delta >= 0 ? '+' : '').$delta,
                $item,
                ['delta' => $delta, 'balance_after' => $balance, 'type' => $type->value],
            );
        }

        return $movement;
    }

    /**
     * Apply a user-entered movement. In/Out/Waste take a positive magnitude;
     * Adjustment is treated as a signed correction.
     */
    public function apply(
        StockItem $item,
        StockMovementType $type,
        float $quantity,
        ?string $note = null,
        ?int $userId = null,
    ): StockMovement {
        $delta = $type === StockMovementType::Adjustment
            ? $quantity
            : $type->direction() * abs($quantity);

        return $this->adjust($item, $delta, $type, null, $userId, $note);
    }

    public function deductForOrder(Order $order): void
    {
        if ($this->hasMovements($order, StockMovementType::Sale)) {
            return;
        }

        // Cek ketersediaan lebih dulu supaya pesan ke kasir jelas: produk
        // mana yang kurang dan sisa stoknya berapa, sebelum ada pencatatan.
        $this->ensureAvailabilityForOrder($order);

        $order->loadMissing('items.product', 'items.bundle.items.product');

        foreach ($order->items as $item) {
            foreach ($this->stockComponents($item) as [$product, $variantId, $quantity]) {
                if (! $product->track_stock) {
                    continue;
                }

                $stock = $this->lockedForProduct($product, $variantId);

                $this->adjust(
                    $stock,
                    -1 * $quantity,
                    StockMovementType::Sale,
                    $order->number,
                    $order->user_id,
                    allowNegative: false,
                );
            }
        }
    }

    /**
     * Validasi ketersediaan stok untuk seluruh item pesanan. Melempar
     * ValidationError (STOK_TIDAK_CUKUP) yang berisi daftar produk kurang.
     */
    public function ensureAvailabilityForOrder(Order $order): void
    {
        $order->loadMissing('items.product', 'items.bundle.items.product');

        /** @var array<string, array{0: Product, 1: ?int, 2: float}> $required */
        $required = [];

        foreach ($order->items as $item) {
            foreach ($this->stockComponents($item) as [$product, $variantId, $quantity]) {
                if (! $product->track_stock) {
                    continue;
                }

                $key = $product->getKey().':'.($variantId ?? 'base');
                $required[$key] = [$product, $variantId, ($required[$key][2] ?? 0) + $quantity];
            }
        }

        $shortages = [];

        foreach ($required as [$product, $variantId, $quantity]) {
            $stock = $this->forProduct($product, $variantId);
            $available = (float) $stock->quantity;

            if ($available < $quantity) {
                $shortages[] = 'Stok '.$product->name.' tersisa '.$this->formatQuantity($available);
            }
        }

        if ($shortages !== []) {
            throw ValidationError::with(
                'items',
                implode('. ', $shortages).'. Sesuaikan jumlah di keranjang.',
                'STOK_TIDAK_CUKUP',
            );
        }
    }

    /**
     * Tampilkan kuantitas dengan gaya Indonesia: "2" untuk satuan bulat,
     * "2,5" untuk satuan pecahan.
     */
    public function formatQuantity(float $quantity): string
    {
        $rounded = round($quantity, 2);

        if (abs($rounded - floor($rounded)) < 0.005) {
            return (string) (int) $rounded;
        }

        return str_replace('.', ',', (string) rtrim(rtrim(number_format($rounded, 2, '.', ''), '0'), '.'));
    }

    private function lockedForProduct(Product $product, ?int $variantId): StockItem
    {
        $item = StockItem::query()
            ->where('tenant_id', $product->tenant_id)
            ->where('product_id', $product->getKey())
            ->where('product_variant_id', $variantId)
            ->lockForUpdate()
            ->first();

        return $item ?? $this->forProduct($product, $variantId);
    }

    public function restoreForOrder(Order $order): void
    {
        if (! $this->hasMovements($order, StockMovementType::Sale)) {
            return;
        }

        if ($this->hasMovements($order, StockMovementType::SaleReturn)) {
            return;
        }

        $order->loadMissing('items.product', 'items.bundle.items.product');

        foreach ($order->items as $item) {
            foreach ($this->stockComponents($item) as [$product, $variantId, $quantity]) {
                if (! $product->track_stock) {
                    continue;
                }

                $stock = $this->forProduct($product, $variantId);

                $this->adjust(
                    $stock,
                    $quantity,
                    StockMovementType::SaleReturn,
                    $order->number,
                    $order->user_id,
                );
            }
        }
    }

    /**
     * Expands an order line into the stock-tracked products it consumes. A
     * bundle line fans out into its components; a regular line is itself.
     *
     * @return array<int, array{0: Product, 1: ?int, 2: float}>
     */
    private function stockComponents(OrderItem $item): array
    {
        if ($item->bundle_id !== null && $item->bundle !== null) {
            return $item->bundle->items
                ->filter(fn (BundleItem $component): bool => $component->product instanceof Product)
                ->map(fn (BundleItem $component): array => [
                    $component->product,
                    $component->product_variant_id,
                    (float) $component->quantity * (float) $item->quantity,
                ])
                ->values()
                ->all();
        }

        if ($item->product === null) {
            return [];
        }

        return [[$item->product, $item->product_variant_id, (float) $item->quantity]];
    }

    private function hasMovements(Order $order, StockMovementType $type): bool
    {
        return StockMovement::query()
            ->where('reference', $order->number)
            ->where('type', $type->value)
            ->exists();
    }

    private function syncSoldOutFlag(StockItem $item): void
    {
        $product = $item->product;

        if ($product === null || ! $product->track_stock) {
            return;
        }

        $product->is_sold_out = $item->isOut();
        $product->save();
    }

    /**
     * Fire the low-stock alert only when the item newly enters the low or out
     * band, so repeated deductions do not spam the bell.
     */
    private function alertIfCrossedThreshold(StockItem $item, string $statusBefore): void
    {
        $statusAfter = $this->statusOf($item);

        if ($statusAfter === 'ok' || $statusAfter === $statusBefore) {
            return;
        }

        app(LowStockNotifier::class)->notify($item);
    }

    private function statusOf(StockItem $item): string
    {
        return match (true) {
            $item->isOut() => 'out',
            $item->isLow() => 'low',
            default => 'ok',
        };
    }
}
