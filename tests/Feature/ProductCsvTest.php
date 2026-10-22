<?php

use App\Models\Category;
use App\Models\Product;
use App\Modules\Catalog\Services\ProductCsv;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Impor',
        'name' => 'Owner Impor',
        'email' => 'owner@impor.test',
        'password' => 'password123',
    ]);

    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

function writeCsv(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'sitrans-csv-');
    file_put_contents($path, $contents);

    return $path;
}

it('imports products and creates missing categories', function () {
    $path = writeCsv("name,sku,category,price,cost,is_active,track_stock\nEspresso,ESP-1,Kopi,18000,6000,1,1\n");

    $stats = app(ProductCsv::class)->import($path);
    unlink($path);

    $product = Product::query()->where('sku', 'ESP-1')->firstOrFail();

    expect($stats['created'])->toBe(1)
        ->and($product->name)->toBe('Espresso')
        ->and($product->price)->toBe(18000.0)
        ->and($product->track_stock)->toBeTrue()
        ->and($product->category?->name)->toBe('Kopi');
});

it('updates an existing product matched by SKU', function () {
    Category::factory()->create(['tenant_id' => $this->tenant->getKey(), 'name' => 'Kopi']);
    Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Espresso Lama',
        'sku' => 'ESP-1',
        'price' => 10000,
    ]);

    $path = writeCsv("name,sku,category,price,cost,is_active,track_stock\nEspresso Baru,ESP-1,Kopi,21000,7000,1,0\n");

    $stats = app(ProductCsv::class)->import($path);
    unlink($path);

    $product = Product::query()->where('sku', 'ESP-1')->firstOrFail();

    expect($stats['updated'])->toBe(1)
        ->and($stats['created'])->toBe(0)
        ->and($product->name)->toBe('Espresso Baru')
        ->and($product->price)->toBe(21000.0)
        ->and(Product::query()->count())->toBe(1);
});

it('skips rows without a name', function () {
    $path = writeCsv("name,sku,price\n,EMPTY,1000\nLatte,LAT-1,25000\n");

    $stats = app(ProductCsv::class)->import($path);
    unlink($path);

    expect($stats['skipped'])->toBe(1)
        ->and($stats['created'])->toBe(1);
});

it('exports the catalogue as rows', function () {
    Category::factory()->create(['tenant_id' => $this->tenant->getKey(), 'name' => 'Kopi']);
    $category = Category::query()->where('name', 'Kopi')->firstOrFail();

    Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'category_id' => $category->getKey(),
        'name' => 'Latte',
        'sku' => 'LAT-1',
        'price' => 25000,
        'cost' => 9000,
    ]);

    $rows = app(ProductCsv::class)->rows();

    expect($rows[0])->toBe(['name', 'sku', 'category', 'price', 'cost', 'is_active', 'track_stock'])
        ->and($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('Latte')
        ->and($rows[1][1])->toBe('LAT-1')
        ->and($rows[1][2])->toBe('Kopi');
});
