<?php

namespace App\Models;

use App\Support\Tenant\BelongsToTenant;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'image_path', 'sort_order', 'is_active'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (blank($category->slug)) {
                $category->slug = static::uniqueSlug($category->name, $category->getKey());
            }
        });

        // Bersihkan file foto lama saat diganti agar tidak jadi orphan, dan
        // hapus file saat kategori dihapus permanen.
        static::updated(function (Category $category): void {
            $original = $category->getOriginal('image_path');

            if ($category->isDirty('image_path') && filled($original) && $original !== $category->image_path) {
                Storage::disk(config('uploads.disk'))->delete($original);
            }
        });

        static::forceDeleted(function (Category $category): void {
            if (filled($category->image_path)) {
                Storage::disk(config('uploads.disk'))->delete($category->image_path);
            }
        });
    }

    protected static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
