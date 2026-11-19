<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use App\Support\Uploads\ImageUpload;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'category_id', 'name', 'sku', 'description', 'price', 'cost',
    'image_path', 'is_active', 'is_sold_out', 'track_stock', 'sort_order', 'unit',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * Satuan umum untuk menu F&B. Satuan pecahan memakai tampilan desimal.
     *
     * @var array<string, string>
     */
    public const UNITS = [
        'porsi' => 'Porsi',
        'gelas' => 'Gelas',
        'botol' => 'Botol',
        'pcs' => 'Pcs',
        'pack' => 'Pack',
        'kg' => 'Kilogram (kg)',
        'gram' => 'Gram (g)',
        'liter' => 'Liter (L)',
        'ml' => 'Mililiter (ml)',
    ];

    protected static function booted(): void
    {
        // SKU opsional: buat otomatis dari nama menu agar katalog tetap rapi.
        static::saving(function (Product $product): void {
            if (blank($product->sku)) {
                $product->sku = static::generateSku($product);
            }
        });

        // Bersihkan file foto lama saat diganti agar tidak jadi orphan, dan
        // hapus file saat produk dihapus permanen (soft delete tetap bisa
        // direstore bersama fotonya).
        static::updated(function (Product $product): void {
            $original = $product->getOriginal('image_path');

            if ($product->isDirty('image_path') && filled($original) && $original !== $product->image_path) {
                Storage::disk(config('uploads.disk'))->delete($original);
            }
        });

        static::forceDeleted(function (Product $product): void {
            if (filled($product->image_path)) {
                Storage::disk(config('uploads.disk'))->delete($product->image_path);
            }
        });
    }

    public static function generateSku(Product $product): string
    {
        $base = Str::upper(Str::slug((string) $product->name, '-')) ?: 'MENU';
        $sku = $base;
        $suffix = 1;

        while (static::withoutGlobalScopes()
            ->where('tenant_id', $product->tenant_id)
            ->where('sku', $sku)
            ->when($product->exists, fn ($query) => $query->whereKeyNot($product->getKey()))
            ->exists()
        ) {
            $sku = $base.'-'.(++$suffix);
        }

        return $sku;
    }

    /**
     * @return array<string, string>
     */
    public static function units(): array
    {
        return self::UNITS;
    }

    public function isFractionalUnit(): bool
    {
        return in_array($this->unit, ['kg', 'gram', 'liter', 'ml'], true);
    }

    /**
     * Jumlah desimal yang masuk akal untuk satuan menu ini.
     */
    public function quantityDecimals(): int
    {
        return $this->isFractionalUnit() ? 2 : 0;
    }

    public function unitLabel(): string
    {
        return self::UNITS[$this->unit] ?? (string) ($this->unit ?: 'Porsi');
    }

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
            return (string) ImageUpload::url($this->image_path);
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
