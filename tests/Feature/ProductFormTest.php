<?php

use App\Filament\App\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Format;
use App\Support\Tenant\TenantManager;

beforeEach(function () {
    $result = app(RegisterTenantAction::class)->execute([
        'cafe_name' => 'Kopi Form',
        'name' => 'Owner Form',
        'email' => 'owner@form.test',
        'password' => 'password123',
    ]);

    $this->tenant = $result['tenant'];

    app(TenantManager::class)->set($this->tenant);
});

it('generates a SKU from the menu name when left empty', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Kopi Susu Gula Aren',
        'sku' => null,
    ]);

    expect($product->sku)->toBe('KOPI-SUSU-GULA-AREN');
});

it('keeps generated SKUs unique within the tenant', function () {
    Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Es Teh',
        'sku' => null,
    ]);

    $second = Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'name' => 'Es Teh',
        'sku' => null,
    ]);

    expect($second->sku)->toBe('ES-TEH-2');
});

it('defaults the stock unit to whole portions', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->getKey()]);

    expect($product->unit)->toBe('porsi')
        ->and($product->isFractionalUnit())->toBeFalse()
        ->and($product->quantityDecimals())->toBe(0)
        ->and($product->unitLabel())->toBe('Porsi');
});

it('treats weight and volume units as fractional', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'unit' => 'kg',
    ]);

    expect($product->isFractionalUnit())->toBeTrue()
        ->and($product->quantityDecimals())->toBe(2)
        ->and(Format::quantity(2.5, $product->quantityDecimals()))->toBe('2,5')
        ->and(Format::quantity(12, 0))->toBe('12');
});

it('formats and parses prices with thousand separators', function () {
    expect(ProductForm::formatRupiahInput(25000))->toBe('25.000')
        ->and(ProductForm::parseRupiahInput('1.250.000'))->toBe(1250000.0)
        ->and(ProductForm::parseRupiahInput(''))->toBe(0.0);
});
