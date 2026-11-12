<?php

use App\Models\Product;
use App\Support\Uploads\ImageUpload;

it('describes the accepted formats and maximum upload size', function () {
    config([
        'uploads.max_size_kb' => 2048,
        'uploads.resize.width' => 1200,
    ]);

    expect(ImageUpload::helperText())
        ->toContain('JPG, PNG, atau WEBP')
        ->toContain('Maksimal 2 MB')
        ->toContain('1200 px');
});

it('builds image urls from the central upload disk', function () {
    config(['uploads.disk' => 'public']);

    $product = Product::factory()->make(['image_path' => 'products/kopi-susu.jpg']);

    expect($product->imageUrl())->toContain('/storage/products/kopi-susu.jpg');
});

it('stores only a relative path in the database', function () {
    config(['uploads.disk' => 'public']);

    $product = Product::factory()->make(['image_path' => 'products/kopi-susu.jpg']);

    expect($product->getAttribute('image_path'))->toBe('products/kopi-susu.jpg')
        ->and($product->getAttribute('image_path'))->not->toContain('http');
});
