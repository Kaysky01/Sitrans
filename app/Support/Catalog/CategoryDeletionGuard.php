<?php

namespace App\Support\Catalog;

use App\Models\Category;

/**
 * Aturan penghapusan kategori bersama untuk aksi tabel (tunggal & massal).
 */
final class CategoryDeletionGuard
{
    /**
     * @param  iterable<Category>  $categories
     * @return string|null Pesan penolakan (bahasa Indonesia) atau null jika aman.
     */
    public static function rejectReason(iterable $categories): ?string
    {
        $totalProducts = 0;

        foreach ($categories as $category) {
            $totalProducts += (int) $category->products()->count();
        }

        if ($totalProducts > 0) {
            return "Kategori masih berisi {$totalProducts} menu. Pindahkan atau hapus menunya dulu.";
        }

        return null;
    }
}
