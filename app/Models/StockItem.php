<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\StockItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id', 'product_variant_id', 'quantity', 'reorder_level',
])]
class StockItem extends Model
{
    /** @use HasFactory<StockItemFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'reorder_level' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function label(): string
    {
        return $this->variant?->name !== null
            ? "{$this->product?->name} — {$this->variant->name}"
            : (string) $this->product?->name;
    }

    public function isOut(): bool
    {
        return $this->quantity <= 0;
    }

    public function isLow(): bool
    {
        return ! $this->isOut() && $this->quantity <= $this->reorder_level;
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isOut() => 'Habis',
            $this->isLow() => 'Menipis',
            default => 'Aman',
        };
    }

    public function statusColor(): string
    {
        return match (true) {
            $this->isOut() => 'danger',
            $this->isLow() => 'warning',
            default => 'success',
        };
    }
}
