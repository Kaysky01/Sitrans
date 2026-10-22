<?php

namespace App\Modules\Catalog\Services;

use App\Models\Category;
use App\Models\Product;

/**
 * Imports and exports the product catalogue as CSV.
 *
 * Import matches rows to existing products by SKU (when present) and creates
 * categories on the fly, so a spreadsheet can be round-tripped.
 */
final class ProductCsv
{
    /**
     * @return array<int, string>
     */
    public function columns(): array
    {
        return ['name', 'sku', 'category', 'price', 'cost', 'is_active', 'track_stock'];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function rows(): array
    {
        $rows = [$this->columns()];

        foreach (Product::query()->with('category')->orderBy('name')->get() as $product) {
            $rows[] = [
                (string) $product->name,
                (string) $product->sku,
                (string) ($product->category?->name ?? ''),
                (string) $product->price,
                (string) $product->cost,
                $product->is_active ? '1' : '0',
                $product->track_stock ? '1' : '0',
            ];
        }

        return $rows;
    }

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return $stats;
        }

        $header = $this->normaliseHeader($header);

        while (($row = fgetcsv($handle)) !== false) {
            $data = $this->combine($header, $row);

            $name = trim((string) ($data['name'] ?? ''));

            if ($name === '') {
                $stats['skipped']++;

                continue;
            }

            $sku = trim((string) ($data['sku'] ?? '')) ?: null;

            $product = $sku !== null
                ? Product::query()->where('sku', $sku)->first()
                : null;

            $attributes = [
                'name' => $name,
                'sku' => $sku,
                'price' => (float) ($data['price'] ?? 0),
                'cost' => (float) ($data['cost'] ?? 0),
                'is_active' => $this->toBool($data['is_active'] ?? '1'),
                'track_stock' => $this->toBool($data['track_stock'] ?? '0'),
            ];

            $categoryName = trim((string) ($data['category'] ?? ''));

            if ($categoryName !== '') {
                $attributes['category_id'] = Category::query()
                    ->firstOrCreate(['name' => $categoryName])
                    ->getKey();
            }

            if ($product !== null) {
                $product->update($attributes);
                $stats['updated']++;
            } else {
                Product::create($attributes);
                $stats['created']++;
            }
        }

        fclose($handle);

        return $stats;
    }

    /**
     * @param  array<int, string|null>  $header
     * @return array<int, string>
     */
    private function normaliseHeader(array $header): array
    {
        return array_map(
            fn (?string $column): string => strtolower(trim((string) $column, " \t\n\r\0\x0B\xEF\xBB\xBF")),
            $header,
        );
    }

    /**
     * @param  array<int, string>  $header
     * @param  array<int, string|null>  $row
     * @return array<string, string|null>
     */
    private function combine(array $header, array $row): array
    {
        $row = array_slice($row, 0, count($header));

        return array_combine($header, array_pad($row, count($header), null));
    }

    private function toBool(?string $value): bool
    {
        return filter_var($value ?? '', FILTER_VALIDATE_BOOLEAN);
    }
}
