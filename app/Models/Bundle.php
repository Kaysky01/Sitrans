<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\BundleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'name', 'description', 'price', 'image_path', 'is_active'])]
class Bundle extends Model
{
    /** @use HasFactory<BundleFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<BundleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class);
    }

    /**
     * HPP snapshot of the whole bundle, summed from its component products.
     */
    public function totalCost(): float
    {
        $this->loadMissing('items.product');

        return round($this->items->sum(
            fn (BundleItem $item): float => (float) $item->product?->cost * (float) $item->quantity,
        ), 2);
    }
}
