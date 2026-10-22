<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'name', 'sku', 'description', 'price', 'cost',
    'image_path', 'is_active', 'is_sold_out', 'track_stock', 'sort_order',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'cost' => 'float',
            'is_active' => 'boolean',
            'is_sold_out' => 'boolean',
            'track_stock' => 'boolean',
            'sort_order' => 'integer',
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
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return BelongsToMany<ModifierGroup, $this>
     */
    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_group')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function isOrderable(): bool
    {
        return $this->is_active && ! $this->is_sold_out;
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        $name = strtolower($this->name);
        if (str_contains($name, 'matcha')) {
            return asset('images/matcha.jpg');
        }
        if (str_contains($name, 'croissant')) {
            return asset('images/croissant.jpg');
        }
        if (str_contains($name, 'roti') || str_contains($name, 'toast') || str_contains($name, 'srikaya')) {
            return asset('images/toast.jpg');
        }
        if (str_contains($name, 'kentang') || str_contains($name, 'fries')) {
            return asset('images/fries.jpg');
        }
        if (str_contains($name, 'tea') || str_contains($name, 'teh')) {
            return asset('images/tea.jpg');
        }
        if (str_contains($name, 'choco') || str_contains($name, 'cokelat')) {
            return asset('images/chocolate.jpg');
        }

        $categoryName = strtolower($this->category?->name ?? '');
        if (str_contains($categoryName, 'kopi') || str_contains($name, 'kopi') || str_contains($name, 'coffee') || str_contains($name, 'espresso') || str_contains($name, 'americano') || str_contains($name, 'cappuccino')) {
            return asset('images/coffee.jpg');
        }
        if (str_contains($categoryName, 'makan') || str_contains($categoryName, 'food')) {
            return asset('images/croissant.jpg');
        }
        if (str_contains($categoryName, 'camilan') || str_contains($categoryName, 'snack')) {
            return asset('images/fries.jpg');
        }

        return asset('images/coffee.jpg');
    }
}
